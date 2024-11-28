<?php

declare(strict_types=1);

return [
    'yiisoft/yii-debug' => [
        'enabled' => $_ENV['YII_DEBUG'],
    ],

    'yiisoft/yii-debug-api' => [
        'enabled' => $_ENV['YII_DEBUG_PANEL'] === "true",
    ],

    'yiisoft/yii-debug-viewer' => [
        'backendUrl' => "/",
    ],
];
