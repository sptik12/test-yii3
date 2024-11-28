<?php

declare(strict_types=1);

use App\Frontend\HttpErrorHandler\NotFoundHandler;
use Yiisoft\Definitions\DynamicReference;
use Yiisoft\Definitions\Reference;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Injector\Injector;
use Yiisoft\Middleware\Dispatcher\MiddlewareDispatcher;
use Yiisoft\Yii\Http\Application;

/** @var array $params */

return [
    Application::class => [
        '__construct()' => [
            'dispatcher' => DynamicReference::to(static function (Injector $injector) use ($params) {
                return $injector
                    ->make(MiddlewareDispatcher::class)
                    ->withMiddlewares($params['middlewares']);
            }),
            'fallbackHandler' => Reference::to(NotFoundHandler::class),
        ],
    ],
    \Yiisoft\Yii\Middleware\Locale::class => [
        '__construct()' => [
            'supportedLocales' => $params['locale']['locales'],
            'ignoredRequestUrlPatterns' => $params['locale']['ignoredRequests'],
        ],
    ],
    ContainerConfig::class => [
        'withValidate()' => [$_ENV['YII_ENV'] == "dev"],
    ],
];
