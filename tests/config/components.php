<?php declare(strict_types=1);

use yii\db\Connection as DbConnection;
use yii\log\Dispatcher as LogDispatcher;
use yii\log\DbTarget;
use yii\log\FileTarget;
use yii\i18n\Formatter as I18NFormatter;
use yii\i18n\I18N;
use yii\i18n\MessageFormatter;
use yii\i18n\PhpMessageSource;
use yii\caching\FileCache;
use yii\rbac\DbManager;
use yii\web\ErrorHandler as WebErrorHandler;
use yii\console\ErrorHandler as ConsoleErrorHandler;

use yii\web\UrlManager as YiiWebUrlManager;
use yii\web\UrlRule as YiiWebUrlRule;
use yii\rest\UrlRule as YiiRestUrlRule;

$components = [];

/**
 * 数据库组件.
 *
 * 配置示例：
 * [
 *   'dsn' => 'mysql:dbname=example;host=IP;charset=utf8mb4;',
 *   'password' => 'example',
 *   'tablePrefix' => 'example_',
 *   'username' => 'root',
 * ]
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
$components[DbConnection::class] = static function (array $config): array {
    return array_merge([
        'class' => DbConnection::class,
        'attributes' => [
            PDO::ATTR_CASE => PDO::CASE_NATURAL, // 保留数据库驱动返回的列名
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // 错误报告模式：exception
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL, // 空字符串<=>NULL：保持原样
            PDO::ATTR_STRINGIFY_FETCHES => false, // 提取的时候将数值转换为字符串：No
            PDO::ATTR_TIMEOUT => 3, // 数据库连接超时时间：3s
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true, // 使用缓冲查询
        ],
        'charset' => 'utf8mb4',
        // 'commandClass' => , @deprecated
        // 'commandMap' => ,
        'driverName' => 'mysql',
        // 'emulatePrepare' => , // 对于SQL语句的预处理，是由PHP本地执行还是由MYSQL服务器执行
        'enableLogging' => YII_ENV !== 'prod', // 生产环境提升性能 https://github.com/yiisoft/yii2/issues/12528
        'enableProfiling' => YII_ENV !== 'prod', // 生产环境提升性能 https://github.com/yiisoft/yii2/issues/12528
        'enableQueryCache' => true,
        'enableSavepoint' => true,
        'enableSchemaCache' => true,
        'enableSlaves' => true,
        'masterConfig' => [],
        'masters' => [],
        // 'pdo' => ,
        'pdoClass' => PDO::class,
        // 'queryBuilder' => ,
        'queryCache' => 'cache',
        'queryCacheDuration' => YII_ENV === 'prod' ? 3600 : 600,
        'schemaCache' => 'cache',
        'schemaCacheDuration' => YII_ENV === 'prod' ? 3600 : 600,
        'schemaCacheExclude' => [], // 缓存排除
        // 'schemaMap' => ,
        'serverRetryInterval' => 600,
        'serverStatusCache' => 'cache',
        'shuffleMasters' => true,
        'slaveConfig' => [],
        'slaves' => [],
    ], $config);
};

/**
 * 日志组件.
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
$components[LogDispatcher::class] = static function (array $config = []): array {
    return array_merge([
        'class' => LogDispatcher::class,
        'traceLevel' => 0,
        'flushInterval' => YII_ENV === 'prod' ? 1000 : 0,
        'targets' => [
            [
                'class' => DbTarget::class,
                'categories' => [],
                'enabled' => YII_ENV === 'prod',
                'exportInterval' => 1000,
                'except' => [
                    'yii\web\HttpException:404',
                    'yii\web\HttpException:403',
                ],
                'levels' => ['error'],
                'logVars' => ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER'],
                'messages' => [],
                'microtime' => false,
                'logTable' => '{{%errlog}}',
            ],
            [
                'class' => FileTarget::class,
                'categories' => [],
                'enabled' => true,
                'exportInterval' => 1000,
                'except' => [
                    'yii\web\HttpException:404',
                    'yii\web\HttpException:403',
                ],
                'levels' => YII_ENV === 'prod' ? ['warning'] : ['warning', 'error'],
                'logVars' => ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER'],
                'messages' => [],
                'microtime' => false,
                'dirMode' => 0775,
                'enableRotation' => true,
                'fileMode' => null,
                'logFile' => '@runtime/logs/app.log',
                'maxFileSize' => 10240, // 10MB
                'maxLogFiles' => 5,
                'rotateByCopy' => true,
            ],
        ],
    ], $config);
};

/**
 * 格式化组件.
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
$components[I18NFormatter::class] = static function (array $config = []): array {
    return array_merge([
        'class' => I18NFormatter::class,
        'booleanFormat' => ['No', 'Yes'],
        'dateFormat' => 'medium',
        'datetimeFormat' => 'medium',
        'decimalSeparator' => '.',
        'defaultTimeZone' => 'Asia/Shanghai',
        'thousandSeparator' => ',',
        'timeFormat' => 'medium',
    ], $config);
};

/**
 * 国际化组件.
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
$components[I18N::class] = static function (array $config = []): array {
    return array_merge([
        'class' => I18N::class,
        'messageFormatter' => [
            'class' => MessageFormatter::class,
        ],
        'translations' => [
            'app*' => [
                'class' => PhpMessageSource::class,
                'basePath' => '@app/messages',
                'fileMap' => [
                    'app' => 'app.php',
                    'app/error' => 'error.php',
                ],
            ],
        ],
    ], $config);
};

/**
 * 缓存组件（file驱动）.
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
$components[FileCache::class] = static function (array $config = []): array {
    return array_merge([
        'class' => FileCache::class,
        'defaultDuration' => 0,
        'keyPrefix' => strtolower(CC_APPCODE),
    ], $config);
};

/**
 * 错误处理组件（web）.
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
$components[WebErrorHandler::class] = static function (array $config = []): array {
    return array_merge([
        'class' => WebErrorHandler::class,
        'displayVars' => ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION'],
    ], $config);
};

/**
 * 错误处理组件（console）.
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
$components[ConsoleErrorHandler::class] = static function (array $config = []): array {
    return array_merge([
        'class' => ConsoleErrorHandler::class,
    ], $config);
};

/**
 * RBAC权限组件（数据库驱动）.
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
$components[DbManager::class] = static function (array $config = []): array {
    return array_merge([
        'class' => DbManager::class,
        'cache' => 'cache',
    ], $config);
};

/**
 * 路由控制组件.
 *
 * 配置示例：
 * [
 *   'rules.controller' => [
 *
 *   ]
 * ]
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
$components[YiiWebUrlManager::class] = static function (array $config): array {
    $config = array_merge([
        'class' => YiiWebUrlManager::class,
        'cache' => 'cache',
        'enablePrettyUrl' => true,
        'enableStrictParsing' => false,
        'routeParam' => 'r',
        'suffix' => null,
        'showScriptName' => false,
        'ruleConfig' => [
            'class' => YiiWebUrlRule::class,
        ],
        'rules' => [
            [
                'class' => YiiRestUrlRule::class,
                'pluralize' => false,
                'controller' => $config['rules.controller'],
            ],
        ],
    ], $config);

    unset($config['rules.controller']);

    return $config;
};

return $components;
