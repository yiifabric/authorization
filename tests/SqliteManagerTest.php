<?php declare(strict_types=1);
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Yiifabric\Authorization\Tests;

/**
 * SqliteManagerTest.
 * @group db
 * @group rbac
 * @group sqlite
 */
class SqliteManagerTest extends DbManagerTestCase
{
    protected static $driverName = 'sqlite';

    protected static $sqliteDb;
}
