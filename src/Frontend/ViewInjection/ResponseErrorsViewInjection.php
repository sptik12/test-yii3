<?php

declare(strict_types=1);

namespace App\Frontend\ViewInjection;

use Yiisoft\Session\Flash\FlashInterface;
use Yiisoft\Yii\View\Renderer\CommonParametersInjectionInterface;

final class ResponseErrorsViewInjection implements CommonParametersInjectionInterface
{
    public function __construct(
        private readonly FlashInterface $flashSession
    ) {
    }

    public function getCommonParameters(): array
    {
        return [
            'responseErrors' => $this->flashSession->get("responseErrors"),
        ];
    }
}
