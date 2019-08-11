<?php declare(strict_types=1);
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Yiifabric\Authorization\Tests;

use yii\caching\FileCache;
use Yiifabric\Authorization\DbManager;

/**
 * MySQLManagerCacheTest.
 * @group rbac
 * @group db
 * @group mysql
 */
class MySQLManagerCacheTest extends MySQLManagerTest
{
    protected function createManager(): DbManager
    {
        return new DbManager([
            'cache' => new FileCache(['cachePath' => '@testroot/runtime']),
            'defaultRoles' => ['testDefaultRole'],
        ]);
    }
}
