<?php

namespace App\Frontend\Controller\Admin;

use App\Backend\Model\Dealer\DealerStatus;
use App\Backend\Service\AuthorizationService;
use App\Backend\Service\DealerDataTableService;
use App\Backend\Service\DealerService;
use App\Backend\Service\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Router\HydratorAttribute\RouteArgument;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\User\CurrentUser;

final class DealerController extends AbstractAdminController
{
    /**
     * Pages
     */
    public function index(
        DealerService $dealerService,
        UserService $userService,
        CurrentUser $currentUser
    ): ResponseInterface {
        $possibleStatuses = $dealerService->getPossibleStatuses();
        $isSuperAdmin = $userService->isSuperAdmin($currentUser);
        $canAddDealer = $currentUser->can("createDealer");
        $accountManagers = $isSuperAdmin ? $userService->searchUsersByIds(userIds: $userService->getAccountManagersIds(), filters: ['active' => true]) : '';

        return $this->viewRenderer->render("index", compact("possibleStatuses", "canAddDealer", "isSuperAdmin", "accountManagers"));
    }

    public function addDealer(
        UserService $userService,
        CurrentUser $currentUser,
    ): ResponseInterface {
        $isSuperAdmin = $userService->isSuperAdmin($currentUser);
        $accountManagers = $isSuperAdmin ? $userService->searchUsersByIds(userIds: $userService->getAccountManagersIds(), filters: ['active' => true]) : '';

        return $this->viewRenderer->render("add-dealer", compact("isSuperAdmin", "accountManagers"));
    }

    public function editDealer(
        #[RouteArgument('id')]
        int $id,
        DealerService $dealerService,
        UserService $userService,
        CurrentUser $currentUser,
        ConfigInterface $config,
    ): ResponseInterface {
        $dealer = $dealerService->getDealer($id);
        $isSuperAdmin = $userService->isSuperAdmin($currentUser);
        $accountManagers = $isSuperAdmin ? $userService->searchUsersByIds(userIds: $userService->getAccountManagersIds(), filters: ['active' => true]) : '';

        // upload validation data
        $params = $config->get('params');
        $allowedMimeTypes = $params['uploadedFiles']['logo']['allowedMimeTypes'];
        $maxUploadFileSize = $params['uploadedFiles']['logo']['maxUploadFileSize'];

        return $this->viewRenderer->render("edit-dealer", compact("dealer", "isSuperAdmin", "accountManagers", "allowedMimeTypes", "maxUploadFileSize"));
    }

    public function approveDealer(
        #[RouteArgument('id')]
        int $id,
        DealerService $dealerService
    ): ResponseInterface {
        $dealer = $dealerService->getDealer($id);

        return $this->viewRenderer->render("approve-dealer", compact("dealer"));
    }

    /**
     * Handlers
     */
    public function doAddDealer(
        DealerService $dealerService,
        ServerRequestInterface $request
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $dealerService->createDealerShipFromArray($requestData);

        return $this->redirectByNameAbsolute("admin.dealers");
    }


    /**
     * Ajax
     */
    public function searchAjax(
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
        UserService $userService,
        DealerDataTableService $dealerService,
        CurrentUser $currentUser
    ): ResponseInterface {
        $tableRequestData = $this->getRequestData($request);
        $baseFilters = $userService->isSuperAdmin($currentUser) ? [] : ['accountManager' => $currentUser->getId()];
        $tableResponseData = $dealerService->prepareAdminTableData($tableRequestData, $baseFilters);

        return $dataResponseFactory->createResponse($tableResponseData);
    }

    public function doApproveDealerAjax(
        DealerService $dealerService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $dealerService->approveDealerFromArray($requestData);

        return $dataResponseFactory->createResponse(true);
    }

    public function suspendDealerAjax(
        DealerService $dealerService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $dealerService->suspendDealerFromArray($requestData);

        return $dataResponseFactory->createResponse(true);
    }

    public function unsuspendDealerAjax(
        DealerService $dealerService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $dealerService->unsuspendDealerFromArray($requestData);

        return $dataResponseFactory->createResponse(true);
    }

    public function doEditDealerAjax(
        DealerService $dealerService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $result = $dealerService->updateDealerFromArray($requestData);

        return $dataResponseFactory->createResponse(true);
    }

    public function uploadDealerLogoAjax(
        DealerService $dealerService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $files = $request->getUploadedFiles();
        $dealer = $dealerService->uploadDealerLogoFromArray($requestData, $files["files"]);

        return $dataResponseFactory->createResponse(compact("dealer"));
    }

    public function deleteDealerLogoAjax(
        #[RouteArgument('id')]
        int $id,
        DealerService $dealerService,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $dealer = $dealerService->deleteDealerLogo($id);

        return $dataResponseFactory->createResponse(compact("dealer"));
    }

    public function loginAsDealerAjax(
        #[RouteArgument('id')]
        int $id,
        AuthorizationService $authorizationService,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $redirectUrl = $authorizationService->loginAsDealer($id);

        return $dataResponseFactory->createResponse(compact("redirectUrl"));
    }

    public function assignAccountManagersAjax(
        DealerService $dealerService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $dealerService->assignAccountManagersFromArray($requestData);

        return $dataResponseFactory->createResponse(true);
    }
}
