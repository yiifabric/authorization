<?php declare(strict_types=1);
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Yiifabric\Authorization\Tests;

use Yii;
use yii\caching\ArrayCache;
use yii\log\Logger;
use Yiifabric\Authorization\Assignment;
use Yiifabric\Authorization\DbManager;
use Yiifabric\Authorization\Permission;
use Yiifabric\Authorization\Role;
use Yiifabric\Authorization\Tests\Data\UserID;
use Yiifabric\Authorization\Tests\Support\ArrayTarget;

/**
 * DbManagerTestCase.
 * @group db
 * @group rbac
 */
abstract class DbManagerTestCase extends ManagerTestCase
{
    protected static $database;
    protected static $driverName;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auth = $this->createManager();
    }

    protected function createManager(): DbManager
    {
        return new DbManager([
            'defaultRoles' => ['testDefaultRole']
        ]);
    }

    private function prepareRoles($userId): void
    {
        //$this->auth->removeAll();

        $author = $this->auth->createRole('Author');
        $this->auth->add($author);
        $this->auth->assign($author, $userId);

        $createPost = $this->auth->createPermission('createPost');
        $this->auth->add($createPost);
        $this->auth->assign($createPost, $userId);

        $updatePost = $this->auth->createPermission('updatePost');
        $this->auth->add($updatePost);
        $this->auth->assign($updatePost, $userId);
    }

    public function emptyValuesProvider(): array
    {
        return [
            [0, 0, true],
            [0, new UserID(0), true],
            ['', '', false],
        ];
    }

    /**
     * @dataProvider emptyValuesProvider
     * @param mixed $userId
     * @param mixed $searchUserId
     * @param mixed $isValid
     */
    public function testGetPermissionsByUserWithEmptyValue($userId, $searchUserId, $isValid): void
    {
        $this->prepareRoles($userId);

        $permissions = $this->auth->getPermissionsByUser($searchUserId);

        if ($isValid) {
            $this->assertTrue(isset($permissions['createPost']));
            $this->assertInstanceOf(Permission::class, $permissions['createPost']);
        } else {
            $this->assertEmpty($permissions);
        }
    }

    /**
     * @dataProvider emptyValuesProvider
     * @param mixed $userId
     * @param mixed $searchUserId
     * @param mixed $isValid
     */
    public function testGetRolesByUserWithEmptyValue($userId, $searchUserId, $isValid)
    {
        $this->prepareRoles($userId);

        $roles = $this->auth->getRolesByUser($searchUserId);

        if ($isValid) {
            $this->assertTrue(isset($roles['Author']));
            $this->assertInstanceOf(Role::className(), $roles['Author']);
        } else {
            $this->assertEmpty($roles);
        }
    }

    /**
     * @dataProvider emptyValuesProvider
     * @param mixed $userId
     * @param mixed $searchUserId
     * @param mixed $isValid
     */
    public function testGetAssignmentWithEmptyValue($userId, $searchUserId, $isValid)
    {
        $this->prepareRoles($userId);

        $assignment = $this->auth->getAssignment('createPost', $searchUserId);

        if ($isValid) {
            $this->assertInstanceOf(Assignment::className(), $assignment);
            $this->assertEquals($userId, $assignment->userId);
        } else {
            $this->assertEmpty($assignment);
        }
    }

    /**
     * @dataProvider emptyValuesProvider
     * @param mixed $userId
     * @param mixed $searchUserId
     * @param mixed $isValid
     */
    public function testGetAssignmentsWithEmptyValue($userId, $searchUserId, $isValid)
    {
        $this->prepareRoles($userId);

        $assignments = $this->auth->getAssignments($searchUserId);

        if ($isValid) {
            $this->assertNotEmpty($assignments);
            $this->assertInstanceOf(Assignment::className(), $assignments['createPost']);
            $this->assertInstanceOf(Assignment::className(), $assignments['updatePost']);
        } else {
            $this->assertEmpty($assignments);
        }
    }

    /**
     * @dataProvider emptyValuesProvider
     * @param mixed $userId
     * @param mixed $searchUserId
     * @param mixed $isValid
     */
    public function testRevokeWithEmptyValue($userId, $searchUserId, $isValid)
    {
        $this->prepareRoles($userId);
        $role = $this->auth->getRole('Author');

        $result = $this->auth->revoke($role, $searchUserId);

        if ($isValid) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }

    /**
     * @dataProvider emptyValuesProvider
     * @param mixed $userId
     * @param mixed $searchUserId
     * @param mixed $isValid
     */
    public function testRevokeAllWithEmptyValue($userId, $searchUserId, $isValid)
    {
        $this->prepareRoles($userId);

        $result = $this->auth->revokeAll($searchUserId);

        if ($isValid) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }

    /**
     * Ensure assignments are read from DB only once on subsequent tests.
     */
    public function testCheckAccessCache()
    {
        $this->mockApplication();
        $this->prepareData();

        // warm up item cache, so only assignment queries are sent to DB
        $this->auth->cache = new ArrayCache();
        $this->auth->checkAccess('author B', 'readPost');
        $this->auth->checkAccess(new UserID('author B'), 'createPost');

        // track db queries
        Yii::$app->log->flushInterval = 1;
        Yii::$app->log->getLogger()->messages = [];
        Yii::$app->log->targets['rbacqueries'] = $logTarget = new ArrayTarget([
            'categories' => ['yii\\db\\Command::query'],
            'levels' => Logger::LEVEL_INFO,
        ]);
        $this->assertCount(0, $logTarget->messages);

        // testing access on two different permissons for the same user should only result in one DB query for user assignments
        foreach (['readPost' => true, 'createPost' => false] as $permission => $result) {
            $this->assertEquals($result, $this->auth->checkAccess('reader A', $permission), "Checking $permission");
        }
        $this->assertSingleQueryToAssignmentsTable($logTarget);

        // verify cache is flushed on assign (createPost is now true)
        $this->auth->assign($this->auth->getRole('admin'), 'reader A');
        foreach (['readPost' => true, 'createPost' => true] as $permission => $result) {
            $this->assertEquals($result, $this->auth->checkAccess('reader A', $permission), "Checking $permission");
        }
        $this->assertSingleQueryToAssignmentsTable($logTarget);

        // verify cache is flushed on unassign (createPost is now false again)
        $this->auth->revoke($this->auth->getRole('admin'), 'reader A');
        foreach (['readPost' => true, 'createPost' => false] as $permission => $result) {
            $this->assertEquals($result, $this->auth->checkAccess('reader A', $permission), "Checking $permission");
        }
        $this->assertSingleQueryToAssignmentsTable($logTarget);

        // verify cache is flushed on revokeall
        $this->auth->revokeAll('reader A');
        foreach (['readPost' => false, 'createPost' => false] as $permission => $result) {
            $this->assertEquals($result, $this->auth->checkAccess('reader A', $permission), "Checking $permission");
        }
        $this->assertSingleQueryToAssignmentsTable($logTarget);

        // verify cache is flushed on removeAllAssignments
        $this->auth->assign($this->auth->getRole('admin'), 'reader A');
        foreach (['readPost' => true, 'createPost' => true] as $permission => $result) {
            $this->assertEquals($result, $this->auth->checkAccess('reader A', $permission), "Checking $permission");
        }
        $this->assertSingleQueryToAssignmentsTable($logTarget);

        $this->auth->removeAllAssignments();

        foreach (['readPost' => false, 'createPost' => false] as $permission => $result) {
            $this->assertEquals($result, $this->auth->checkAccess('reader A', $permission), "Checking $permission");
        }
        $this->assertSingleQueryToAssignmentsTable($logTarget);
    }

    private function assertSingleQueryToAssignmentsTable($logTarget)
    {
        $this->assertCount(1, $logTarget->messages, 'Only one query should have been performed, but there are the following logs: ' . print_r($logTarget->messages, true));
        $this->assertStringContainsString('auth_assignment', $logTarget->messages[0][0], 'Log message should be a query to auth_assignment table');
        $logTarget->messages = [];
    }
}
