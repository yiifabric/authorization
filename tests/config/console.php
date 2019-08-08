<?php declare(strict_types=1);

use yii\console\Application as ConsoleApplication;
use yii\console\controllers\MigrateController;
//use yii\faker\FixtureController;
use yii\console\ErrorHandler;
use yii\console\Request as ConsoleRequest;
use yii\console\Response as ConsoleResponse;

$config = [
    'class' => ConsoleApplication::class,
    'id' => strtolower(CC_APPCODE) . '_console_application',
    'name' => ucfirst(strtolower(CC_APPCODE)) . ' Console Application',
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@tests' => '@app/tests',
    ],
    'defaultRoute' => 'help',
    'enableCoreCommands' => true,
    'controllerMap' => [
        'migrate' => [
            'class' => MigrateController::class,
            // 对于不含命名空间的迁移，需要使用migrationPath来指定路径
            'migrationPath' => [
                '@yii/rbac/migrations',
                '@yii/i18n/migrations',
            ],
            // 对于含有命名空间的迁移，需要使用migrationNamespaces指定对应的命名空间
            'migrationNamespaces' => [
                'app\migrations',
                'yii\queue\db\migrations',
            ],
        ],
        // Fixture generation command line.
//        'fixture' => [
//            'class' => FixtureController::class,
//        ],
    ],
    'components' => [
        'errorHandler' => $components[ErrorHandler::class](),
        'request' => [
            'class' => ConsoleRequest::class,
        ],
        'response' => [
            'class' => ConsoleResponse::class,
        ],
    ],
];

unset($config['class']);

return $config;
