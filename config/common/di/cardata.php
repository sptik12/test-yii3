<?php

declare(strict_types=1);

use App\Backend\Component\CarData\MarketCheck\MarketCheck;
use Yiisoft\Definitions\Reference;

/** @var array $params */

return [
    App\Backend\Component\CarData\CarDataInterface::class => Reference::to(MarketCheck::class)
];
