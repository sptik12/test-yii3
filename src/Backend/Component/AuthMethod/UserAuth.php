<?php

namespace App\Backend\Component\AuthMethod;

use App\Backend\Model\User\Status;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Auth\AuthenticationMethodInterface;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;
use Yiisoft\User\CurrentUser;

final class UserAuth implements AuthenticationMethodInterface
{
    public function __construct(
        private CurrentUser $currentUser
    ) {
    }

    public function authenticate(ServerRequestInterface $request): ?IdentityInterface
    {
        $identity = $this->currentUser->getIdentity();

        if (!$this->currentUser->isGuest() && $identity->status !== Status::Active->value) {
            $this->currentUser->logout();

            return null;
        }

        return $identity;
    }

    public function challenge(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }
}
