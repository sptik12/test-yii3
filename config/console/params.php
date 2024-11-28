<?php

declare(strict_types=1);

return [
    'yiisoft/yii-console' => [
        'commands' => require __DIR__ . '/commands.php',
    ],

    'yiisoft/db-migration' => [
        'sourcePaths' => [
            dirname(__DIR__) . '/vendor/yiisoft/rbac-db/migrations/items',
            dirname(__DIR__) . '/vendor/yiisoft/rbac-db/migrations/assignments',
        ],
    ],
];
