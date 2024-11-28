<?php

namespace App\Frontend\Controller\Dealer;

use App\Frontend\Controller\AbstractController;
use Psr\Http\Message\ResponseFactoryInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

abstract class AbstractDealerController extends AbstractController
{
    public function __construct(
        ViewRenderer $viewRenderer,
        ResponseFactoryInterface $responseFactory,
        UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct(
            layout: "dealer",
            viewRenderer: $viewRenderer,
            responseFactory: $responseFactory,
            urlGenerator: $urlGenerator,
        );
    }
}
