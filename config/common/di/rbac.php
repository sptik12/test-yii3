<?php

declare(strict_types=1);

use Yiisoft\Access\AccessCheckerInterface;
use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\ItemsStorageInterface;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\Db\AssignmentsStorage;
use Yiisoft\Rbac\Db\ItemsStorage;

/** @var array $params */

return [
    ItemsStorageInterface::class => [
        'class' => ItemsStorage::class,
        '__construct()' => [
            'tableName' => 'rbacItem',
            'childrenTableName' => 'rbacItemChild'
        ]
    ],
    AssignmentsStorageInterface::class => [
        'class' => AssignmentsStorage::class,
        '__construct()' => [
            'tableName' => 'rbacAssignment'
        ]
    ],
    Manager::class => [
        'class' => Manager::class,
        '__construct()' => [
            'includeRolesInAccessChecks'  => true,
        ],
        'setGuestRoleName()' => ["guest"],
    ],
    AccessCheckerInterface::class => [
        'class' => Manager::class,
        '__construct()' => [
            'includeRolesInAccessChecks'  => true,
        ],
        'setGuestRoleName()' => ["guest"],
    ],
];
