<?php

namespace App\Backend\Middleware;

use App\Backend\Component\CurrentPanel;
use App\Backend\Exception\Http\ForbiddenException;
use App\Backend\Service\UserService;
use App\Frontend\Handler\UnknownDealerHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\User\CurrentUser;

final class AccessPanelChecker implements MiddlewareInterface
{
    public function __construct(
        private CurrentUser $currentUser,
        private CurrentPanel $currentPanel,
        private UserService $userService,
        private UnknownDealerHandler $unknownDealerHandler
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $panelId = $this->currentPanel->getId();

        if ($panelId == "admin" && !$this->userService->isAccountManagerAdmin($this->currentUser)) {
            throw new ForbiddenException();
        }

        if ($panelId == "dealer" && !$this->userService->isDealer($this->currentUser)) {
            throw new ForbiddenException();
        }

        if ($panelId == "dealer" && $this->userService->isAccountManagerAdmin($this->currentUser) && !$this->currentUser->getIdentity()->currentDealerId) {
            return $this->unknownDealerHandler->handle($request);
        }

        return $handler->handle($request);
    }
}
