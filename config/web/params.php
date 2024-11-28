<?php

declare(strict_types=1);

use App\Backend\Middleware\HttpExceptionCatcher;
use Yiisoft\ErrorHandler\Middleware\ErrorCatcher;
use Yiisoft\Router\Middleware\Router;
use Yiisoft\Session\SessionMiddleware;
use App\Backend\Middleware\CookieLoginMiddleware;
use Yiisoft\Yii\Middleware\Locale;

return [
    'middlewares' => [
        ErrorCatcher::class,
        HttpExceptionCatcher::class,
        SessionMiddleware::class,
        CookieLoginMiddleware::class,
        Locale::class,
        Router::class,
    ],

    'locale' => [
        'locales' => [
            'en' => 'en-US',
            'fr' => 'fr-FR',
        ],
        'ignoredRequests' => [
            '/debug**',
        ],
    ],

    'auth' => [
        'sessionTimeout' => 60 * 60 * 24 * 30, // 30 days
        'сodeKeepAlive' => 10 // 10 min
    ],

    'defaultPageParams' => [
        'perPage' => 10,
    ],

    'yiisoft/user' => [
        'cookieLogin' => [
            'duration' => "P30D"
        ]
    ],

    'yiisoft/cookies' => [
        'secretKey' => '53136271c432a1af377c3806c3112ddf',
    ],
];
