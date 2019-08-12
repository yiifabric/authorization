<?php

use Localheinz\PhpCsFixer\Config;

$config = Config\Factory::fromRuleSet(new Config\RuleSet\Php73(), [
  'yoda_style' => false,
  'native_constant_invocation' => false,
  'native_function_invocation' => false,
  'no_trailing_whitespace' => true,
  'no_trailing_whitespace_in_comment' => true,
  'final_class' => false,
]);

$config->getFinder()->in(__DIR__);

$cacheDir = getenv('TRAVIS') ? getenv('HOME') : __DIR__;

$config->setCacheFile($cacheDir . '/.php_cs.cache');

return $config;
