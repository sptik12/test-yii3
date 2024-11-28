<?php

declare(strict_types=1);

namespace App\Frontend\ViewInjection;

use Yiisoft\Session\Flash\FlashInterface;
use Yiisoft\Yii\View\Renderer\CommonParametersInjectionInterface;

final class SavedRequestBodyViewInjection implements CommonParametersInjectionInterface
{
    public function __construct(
        private readonly FlashInterface $flashSession
    ) {
    }

    public function getCommonParameters(): array
    {
        return [
            'filled' => $this->flashSession->get("savedRequestBody"),
        ];
    }
}
