<?php

namespace App\Backend\Middleware;

use App\Backend\Exception\Http\ForbiddenException;
use App\Backend\Exception\Http\NotFoundException;
use App\Backend\Exception\Http\UnauthorizedException;
use App\Frontend\HttpErrorHandler\ForbiddenHandler;
use App\Frontend\HttpErrorHandler\NotFoundHandler;
use App\Frontend\HttpErrorHandler\UnauthorizedHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class HttpExceptionCatcher implements MiddlewareInterface
{
    public function __construct(
        private readonly UnauthorizedHandler $unauthorizedHandler,
        private readonly ForbiddenHandler $forbiddenHandler,
        private readonly NotFoundHandler $notFoundHandler,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (UnauthorizedException) {
            return $this->unauthorizedHandler->handle($request);
        } catch (ForbiddenException) {
            return $this->forbiddenHandler->handle($request);
        } catch (NotFoundException) {
            return $this->notFoundHandler->handle($request);
        }
    }
}
