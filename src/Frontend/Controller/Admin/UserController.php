<?php

namespace App\Frontend\Controller\Admin;

use App\Backend\Service\AuthorizationService;
use App\Backend\Service\UserDataTableService;
use App\Backend\Service\UserService;
use App\Backend\Service\DealerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\HydratorAttribute\RouteArgument;
use Yiisoft\Session\SessionInterface;

final class UserController extends AbstractAdminController
{
    /**
     * Pages
     */
    public function index(
        UserService $userService,
        ServerRequestInterface $request,
        SessionInterface $session,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        $possibleStatuses = $userService->getPossibleStatuses();
        $possibleRoles = $userService->getPossibleRoles();
        $session->set("lastSearchUserUrl", $request->getRequestTarget());
        $session->set("lastSearchUserRouteName", $currentRoute->getName());

        return $this->viewRenderer->render("index", compact("possibleStatuses", "possibleRoles"));
    }

    public function accountManagers(
        ServerRequestInterface $request,
        SessionInterface $session,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        $session->set("lastSearchUserUrl", $request->getRequestTarget());
        $session->set("lastSearchUserRouteName", $currentRoute->getName());

        return $this->viewRenderer->render("account-managers");
    }

    public function addUser(
        UserService $userService,
        DealerService $dealerService
    ): ResponseInterface {
        $possibleRoles = $userService->getPossibleRoles();
        $dealers = $dealerService->searchDealersForList(filters: []);

        return $this->viewRenderer->render("add-user", compact("possibleRoles", "dealers"));
    }

    public function editUser(
        #[RouteArgument('id')]
        int $id,
        UserService $userService,
        DealerService $dealerService,
        SessionInterface $session
    ): ResponseInterface {
        $user = $userService->getUser($id);
        $possibleRoles = $userService->getPossibleRoles();
        $dealers = $dealerService->searchDealersForList(filters: []);

        $canDelete = true;

        /*
        if ($userService->isAccountManagerAdminOnly($id)) {
            $canDelete = count($userService->getAccountManagersIds()) > 1;

            if ($canDelete) {
                $canDelete = $dealerService->getAccountManagerDealersCount($id) == 0;
            }
        }

        if ($userService->isDealerOnly($id)) {
            $dealerPositions = $userDealerPositionService->search(filters: ['userId' => $id]);

            foreach ($dealerPositions as $dealerPosition) {
                if ($userDealerPositionService->searchTotal(filters:["dealer" => $dealerPosition->dealerId]) == 1) {
                    $canDelete = false;
                    break;
                }
            }
        }*/

        $lastSearchUserUrl = $session->get("lastSearchUserUrl", $this->urlGenerator->generate("admin.users"));
        $lastSearchUserRouteName = $session->get("lastSearchUserRouteName", "admin.users");

        return $this->viewRenderer->render("edit-user", compact("user", "possibleRoles", "dealers", "canDelete", "lastSearchUserUrl", "lastSearchUserRouteName"));
    }

    /**
     * Handlers
     */
    public function doAddUser(
        UserService $userService,
        ServerRequestInterface $request
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $userService->addUserFromArray($requestData);

        return $this->redirectByNameAbsolute("admin.users");
    }


    /**
     * Ajax
     */
    public function searchAjax(
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
        UserDataTableService $userDataTableService,
    ): ResponseInterface {
        $tableRequestData = $this->getRequestData($request);
        $tableResponseData = $userDataTableService->prepareAdminTableData($tableRequestData);

        return $dataResponseFactory->createResponse($tableResponseData);
    }

    public function searchAccountManagersAjax(
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
        UserDataTableService $userDataTableService,
    ): ResponseInterface {
        $tableRequestData = $this->getRequestData($request);
        $tableResponseData = $userDataTableService->prepareAccountManagersTableData($tableRequestData);

        return $dataResponseFactory->createResponse($tableResponseData);
    }

    public function getUserAjax(
        #[RouteArgument('id')]
        int $id,
        UserService $userService,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $user = $userService->getUser($id);

        return $dataResponseFactory->createResponse(compact("user"));
    }

    public function doEditUserAjax(
        UserService $userService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);

        $result = $userService->updateUserFromArray($requestData);

        return $dataResponseFactory->createResponse(compact("result"));
    }

    public function searchUserRolesAjax(
        #[RouteArgument('id')]
        int $id,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
        UserDataTableService $userDataTableService,
    ): ResponseInterface {
        $tableRequestData = $this->getRequestData($request);
        $tableResponseData = $userDataTableService->prepareUserRolesTableData($id, $tableRequestData);

        return $dataResponseFactory->createResponse($tableResponseData);
    }

    // get Dealer users with their roles in it
    public function searchForDealerAjax(
        #[RouteArgument('id')]
        int $id,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
        UserDataTableService $userDataTableService,
    ): ResponseInterface {
        $tableRequestData = $this->getRequestData($request);
        $tableResponseData = $userDataTableService->prepareDealerTableData($id, $tableRequestData);

        return $dataResponseFactory->createResponse($tableResponseData);
    }



    // add User to Dealer from popup on Edit Dealer page
    public function addUserToDealerAjax(
        UserService $userService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $userService->addUserToDealerFromArray($requestData);

        return $dataResponseFactory->createResponse(true);
    }

    // update Users data and update Dealer Role from popup on Edit Dealer page
    public function updateUserToDealerAjax(
        UserService $userService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $userService->updateUserToDealerFromArray($requestData);

        return $dataResponseFactory->createResponse(true);
    }

    // update unassign user from dealer from grid on Edit Dealer page
    public function unassignUserFromDealerAjax(
        UserService $userService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $userService->unassignUserFromDealerFromArray($requestData);

        return $dataResponseFactory->createResponse(true);
    }

    //  from grid on Edit Dealer page
    public function setUserAsPrimaryDealerAjax(
        UserService $userService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $userService->setUserAsPrimaryDealerFromArray($requestData);

        return $dataResponseFactory->createResponse(true);
    }




    // assign User to Role on Edit User page
    public function addRoleToUserAjax(
        UserService $userService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $user = $userService->addRoleToUserFromArray($requestData);

        return $dataResponseFactory->createResponse(compact("user"));
    }

    // unassign User from Role on Edit User page
    public function unassignRoleFromUserAjax(
        UserService $userService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $user = $userService->unassignRoleFromUserFromArray($requestData);

        return $dataResponseFactory->createResponse(compact("user"));
    }



    // get User details and Dealer role for dealer
    public function getUserWithDealerPositionAjax(
        #[RouteArgument('id')]
        int $id,
        #[RouteArgument('dealerId')]
        int $dealerId,
        UserService $userService,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $user = $userService->searchUserWithDealerRole($id, $dealerId);

        return $dataResponseFactory->createResponse(compact("user"));
    }

    public function validateDeleteUserAjax(
        UserService $userService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $result = $userService->validateDeleteUserFromArray($requestData);

        return $dataResponseFactory->createResponse($result);
    }

    public function deleteUserAjax(
        UserService $userService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $userService->deleteUserFromArray($requestData);

        return $dataResponseFactory->createResponse(true);
    }

    public function setUserDeletionDateAjax(
        UserService $userService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $userService->setUserDeletionDateFromArray($requestData);

        return $dataResponseFactory->createResponse(true);
    }

    public function clearUserDeletionDateAjax(
        UserService $userService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $userService->clearUserDeletionDateFromArray($requestData);

        return $dataResponseFactory->createResponse(true);
    }

    public function sendCodeAjax(
        AuthorizationService $authorizationService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $email = $requestData["email"];
        $isCodeSent = $authorizationService->sendCode($email);

        return $dataResponseFactory->createResponse(compact("isCodeSent"));
    }

    public function unsuspendUserAjax(
        UserService $userService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $userService->unsuspendUserFromArray($requestData);

        return $dataResponseFactory->createResponse(true);
    }

    public function suspendUserAjax(
        UserService $userService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $userService->suspendUserFromArray($requestData);

        return $dataResponseFactory->createResponse(true);
    }
}
