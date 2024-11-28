<?php

namespace App\Backend\Middleware;

use App\Backend\Exception\Http\UnauthorizedException;
use App\Backend\Model\User\Status;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\User\CurrentUser;

final class AuthorizedOnly implements MiddlewareInterface
{
    public function __construct(
        private readonly CurrentUser $currentUser
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->currentUser->isGuest()) {
            throw new UnauthorizedException();
        }

        return $handler->handle($request);
    }
}
