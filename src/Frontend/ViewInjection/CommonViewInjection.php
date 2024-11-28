<?php

declare(strict_types=1);

namespace App\Frontend\ViewInjection;

use App\Frontend\ApplicationParameters;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\User\CurrentUser;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\View\Renderer\CommonParametersInjectionInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Session\SessionInterface;

final class CommonViewInjection implements CommonParametersInjectionInterface
{
    public function __construct(
        private ApplicationParameters $applicationParameters,
        private UrlGeneratorInterface $urlGenerator,
        private SessionInterface $session,
        private CurrentRoute $currentRoute,
        private CurrentUser $currentUser,
        private Aliases $aliases
    ) {
    }

    public function getCommonParameters(): array
    {
        return [
            'applicationParameters' => $this->applicationParameters,
            'urlGenerator' => $this->urlGenerator,
            'session' => $this->session,
            'currentRoute' => $this->currentRoute,
            'currentUser' => $this->currentUser,
            'aliases' => $this->aliases
        ];
    }
}
