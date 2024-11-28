<?php

use Psr\Container\ContainerInterface;
use Yiisoft\ActiveRecord\ConnectionProvider;
use Yiisoft\Db\Connection\ConnectionInterface;

return [
    static function (ContainerInterface $container): void {
        ConnectionProvider::set($container->get(ConnectionInterface::class));
    },
];
