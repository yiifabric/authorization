<?php

declare(strict_types=1);

use yii\helpers\ArrayHelper;

$config = ArrayHelper::merge(
    require __DIR__ . '/base.php',
    require __DIR__ . '/base-test.php',
    require __DIR__ . '/console.php',
    [
        'id' => strtolower(CC_APPCODE) . '_testing_application',
        'name' => ucfirst(strtolower(CC_APPCODE)) . ' Testing Application',
    ]
);

return $config;
