<?php

namespace App\Frontend\Controller\Admin;

use App\Backend\Service\UserService;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\User\CurrentUser;
use Yiisoft\Rbac\Manager;
use Yiisoft\Arrays\ArrayHelper;

final class MainController extends AbstractAdminController
{
    public function index(
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

        return $this->viewRenderer->render("index", [
            "currentUserData" => $currentUserData
        ]);
    }
}
