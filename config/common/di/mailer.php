<?php

declare(strict_types=1);

use Yiisoft\Assets\AssetLoader;
use Yiisoft\Definitions\Reference;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Mailer\FileMailer;
use App\Backend\Component\Gmailer\Mailer;

/** @var array $params */

return [
    Yiisoft\Assets\AssetLoaderInterface::class => Reference::to(AssetLoader::class),

    // just comment this string to switch from gmail to simple smtp
    MailerInterface::class => $params['yiisoft/mailer']['writeToFiles'] ? FileMailer::class : Mailer::class,
];
