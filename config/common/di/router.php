<?php

declare(strict_types=1);

use App\Backend\Component\UrlMatcher;
use Yiisoft\Config\Config;
use Yiisoft\Csrf\CsrfMiddleware;
use Yiisoft\DataResponse\Middleware\FormatDataResponse;
use Yiisoft\Router\Group;
use Yiisoft\Router\RouteCollection;
use Yiisoft\Router\RouteCollectionInterface;
use Yiisoft\Router\RouteCollectorInterface;
use Yiisoft\Router\UrlMatcherInterface;

/** @var Config $config */

return [
    RouteCollectionInterface::class => static function (RouteCollectorInterface $collector) use ($config) {
        $collector
            ->middleware(CsrfMiddleware::class)
            ->middleware(FormatDataResponse::class)
            ->addGroup(
                Group::create()
                    ->routes(...$config->get('routes')),
            );

        return new RouteCollection($collector);
    },

    UrlMatcherInterface::class => UrlMatcher::class, // TODO: Remove this line after fix getHost() problem in yiisoft/router-fastroute
];
