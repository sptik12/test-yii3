<?php

declare(strict_types=1);

use Yiisoft\Yii\Middleware\Event\SetLocaleEvent;
use Yiisoft\User\Event\AfterLogin;
use Yiisoft\User\Event\BeforeLogin;
use App\Frontend\EventHandler\SetLocaleEventHandler;
use App\Frontend\EventHandler\BeforeLoginEventHandler;
use App\Frontend\EventHandler\AfterLoginEventHandler;

return [
    SetLocaleEvent::class => [[SetLocaleEventHandler::class, 'handle']],
    // BeforeLogin::class => [[BeforeLoginEventHandler::class, 'handle']],
    // AfterLogin::class => [[AfterLoginEventHandler::class, 'handle']]
];
