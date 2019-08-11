<?php

use Localheinz\PhpCsFixer\Config;

$config = Config\Factory::fromRuleSet(new Config\RuleSet\Php71());

$config->getFinder()->in(__DIR__);

$cacheDir = getenv('TRAVIS') ? getenv('HOME') : __DIR__;

$config->setCacheFile($cacheDir . '/.php_cs.cache');

return $config;
