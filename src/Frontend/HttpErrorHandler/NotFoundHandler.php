<?php

declare(strict_types=1);

namespace App\Frontend\HttpErrorHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Http\Status;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final class NotFoundHandler implements RequestHandlerInterface
{
    public function __construct(
        private ViewRenderer $viewRenderer,
    ) {
        $this->viewRenderer = $viewRenderer->withControllerName("site");
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->viewRenderer
            ->render("404")
            ->withStatus(Status::NOT_FOUND);
    }
}