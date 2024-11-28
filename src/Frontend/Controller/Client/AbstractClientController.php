<?php

namespace App\Frontend\Controller\Client;

use App\Frontend\Controller\AbstractController;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

abstract class AbstractClientController extends AbstractController
{
    public function __construct(
        ViewRenderer $viewRenderer,
        ResponseFactoryInterface $responseFactory,
        UrlGeneratorInterface $urlGenerator,
        ?string $layout = "client",
    ) {
        parent::__construct(
            layout: $layout,
            viewRenderer: $viewRenderer,
            responseFactory: $responseFactory,
            urlGenerator: $urlGenerator,
        );
    }
}
