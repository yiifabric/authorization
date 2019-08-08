<?php declare(strict_types=1);

use yii\base\Application as BaseApplication;
use yii\log\Dispatcher as LogDispatcher;
use yii\i18n\Formatter as I18NFormatter;
use yii\i18n\I18N;
use yii\caching\FileCache;
use yii\rbac\DbManager;

$params = require __DIR__ . '/params-test.php';

$components = require __DIR__ . '/components.php';

$config = [
    'class' => BaseApplication::class,
    'basePath' => dirname(__DIR__, 2),
    'layout' => 'main',
    'language' => 'zh-CN', // 当前语言
    'sourceLanguage' => 'en-US', // 源语言 => 其它语言
    'bootstrap' => [
        'log',
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'timeZone' => 'Asia/Shanghai',
    'params' => $params,
    'version' => '1.0.0',
    'components' => [
        'log' => $components[LogDispatcher::class](),
        'formatter' => $components[I18NFormatter::class](),
        'i18n' => $components[I18N::class](),
        'cache' => $components[FileCache::class](),
        'authManager' => $components[DbManager::class](),
    ],
];

$config['vendorPath'] = $config['basePath'] . '/vendor';
$config['runtimePath'] = $config['basePath'] . '/runtime';
$config['viewPath'] = $config['basePath'] . '/views';
$config['layoutPath'] = $config['viewPath'] . '/layouts';

unset($config['class']);

return $config;
