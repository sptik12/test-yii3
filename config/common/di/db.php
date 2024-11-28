<?php

declare(strict_types=1);

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Debug\ConnectionInterfaceProxy;
use Yiisoft\Db\Mysql\Connection;
use Yiisoft\Db\Mysql\Driver;
use Yiisoft\Definitions\Reference;
use Yiisoft\Log\Logger;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Log\Target\File\FileTarget;

/** @var array $params */

return [
    'dbLogger' => static function (Aliases $aliases) use ($params) {
        $fileTarget = $_ENV['YII_DEBUG'] ? new FileTarget($aliases->get("@runtime/logs/db.log")) : null;

        return new Logger($fileTarget ? [$fileTarget] : []);
    },

    Connection::class => [
        '__construct()' => [
            'driver' => new Driver(
                $params['yiisoft/db-mysql']['dsn'],
                $params['yiisoft/db-mysql']['username'],
                $params['yiisoft/db-mysql']['password'],
            ),
        ],
        'setLogger()' => [Reference::to('dbLogger')],
    ],

    ConnectionInterfaceProxy::class => [
        '__construct()' => [
            'connection' => Reference::to(Connection::class),
        ]
    ],
    ConnectionInterface::class => ConnectionInterfaceProxy::class,
];
