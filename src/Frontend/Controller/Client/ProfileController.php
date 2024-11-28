<?php

namespace App\Frontend\Controller\Client;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\User\CurrentUser;
use Yiisoft\Rbac\Manager;
use App\Backend\Service\UserService;

final class ProfileController extends AbstractClientController
{
    public function profile(
        CurrentUser $currentUser,
        UserService $userService,
        Manager $manager
    ): ResponseInterface {
        $roles = $userService->getCurrentUserRoles($currentUser, $manager);
        $currentUserData = "";

        if (!$currentUser->isGuest()) {
            $currentUserIdentity = $currentUser->getIdentity();
            $currentUserData = "{$currentUserIdentity->username} {$currentUserIdentity->email}";
        }


        return $this->viewRenderer->render("profile", [
            'currentUserData' => $currentUserData,
            'isAdmin' => $userService->isSuperAdmin($currentUser),
            'adminHomeUrl' => $this->urlGenerator->generateAbsolute("admin.home"),
            'isDealer' => $userService->isDealer($currentUser),
            'dealerHomeUrl' => $this->urlGenerator->generateAbsolute("dealer.searchCar"),
            'isClient' => $userService->isClient($currentUser),
            'clientHomeUrl' => $this->urlGenerator->generateAbsolute("client.home")
        ]);
    }
}
