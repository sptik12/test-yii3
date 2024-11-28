<?php

declare(strict_types=1);

use Yiisoft\Injector\Injector;

return [
    Injector::class => [
        'withCacheReflections()' => [true],
    ],
];
