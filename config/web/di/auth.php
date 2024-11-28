<?php

declare(strict_types=1);

use App\Backend\Component\AuthMethod\UserAuth;
use App\Backend\Component\CookieLogin;
use Yiisoft\Definitions\Reference;

/** @var array $params */

return [
    \Yiisoft\Auth\AuthenticationMethodInterface::class => UserAuth::class,
    \Yiisoft\Auth\IdentityRepositoryInterface::class => \App\Backend\Service\UserService::class,
    \Yiisoft\Auth\Middleware\Authentication::class => [
        '__construct()' => [
            'authenticationFailureHandler' => Reference::to(\App\Frontend\HttpErrorHandler\UnauthorizedHandler::class),
        ],
    ],


    \Yiisoft\Cookies\CookieMiddleware::class => static fn(\Yiisoft\User\Login\Cookie\CookieLogin $cookieLogin, \Psr\Log\LoggerInterface $logger) =>
        new \Yiisoft\Cookies\CookieMiddleware(
            $logger,
            new \Yiisoft\Cookies\CookieEncryptor($params['yiisoft/cookies']['secretKey']),
            new \Yiisoft\Cookies\CookieSigner($params['yiisoft/cookies']['secretKey']),
            [$cookieLogin->getCookieName() => \Yiisoft\Cookies\CookieMiddleware::SIGN],
        ),

    \Yiisoft\User\Login\Cookie\CookieLoginMiddleware::class => [
        '__construct()' => [
            'cookieLogin' => new CookieLogin(
                $params['yiisoft/user']['cookieLogin']['duration'] !== null
                ? new DateInterval($params['yiisoft/user']['cookieLogin']['duration'])
                : null
            ),
            'forceAddCookie' => $params['yiisoft/user']['cookieLogin']['forceAddCookie'],
        ],
    ],

    \Yiisoft\User\CurrentUser::class => [
        'withSession()' => [Reference::to(\Yiisoft\Session\SessionInterface::class)],
        'withAuthTimeout()' => [$params['auth']['sessionTimeout']],
        'withAccessChecker()' => [Reference::to(\Yiisoft\Access\AccessCheckerInterface::class)],
    ],

    \Yiisoft\Csrf\CsrfMiddleware::class => [
        '__construct()' => [
            'failureHandler' => Reference::to(\App\Frontend\Handler\CsrfHandler::class),
        ],
    ],
];
