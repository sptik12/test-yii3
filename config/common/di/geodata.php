<?php

declare(strict_types=1);

use App\Backend\Component\GeoData\PositionStack\PositionStack;
use App\Backend\Component\GeoData\Osm\Osm;
use App\Backend\Component\GeoData\Geoapify\Geoapify;
use Yiisoft\Definitions\Reference;

/** @var array $params */

return [
    App\Backend\Component\GeoData\GeoDataInterface::class => Reference::to(Geoapify::class)
];
