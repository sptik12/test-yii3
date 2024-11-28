<?php

declare(strict_types=1);

namespace App\Frontend\HttpErrorHandler;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Http\Header;
use Yiisoft\Http\Status;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Router\CurrentRoute;

final class UnauthorizedHandler implements RequestHandlerInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private ResponseFactoryInterface $responseFactory,
        private CurrentRoute $currentRoute
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responseFactory
            ->createResponse(Status::FOUND)
            ->withHeader(
                Header::LOCATION,
                $this->urlGenerator->generateAbsolute(
                    name: "client.signIn",
                    queryParameters: ['returnUrl' => (string)$this->currentRoute->getUri()],
                ),
            );
    }
}
