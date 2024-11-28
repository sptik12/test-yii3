<?php

namespace App\Frontend\Controller\Dealer;

use App\Backend\Component\CarData\CarData;
use App\Backend\Service\CarMakeService;
use App\Backend\Service\CarModelService;
use App\Backend\Service\CarService;
use App\Backend\Service\DealerService;
use App\Frontend\Helper\Ancillary;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Session\SessionInterface;
use Yiisoft\User\CurrentUser;
use Yiisoft\Router\HydratorAttribute\RouteArgument;

final class CarController extends AbstractDealerController
{
    /**
     * Pages
     */
    public function search(
        CarService $carService,
        DealerService $dealerService,
        CarModelService $carModelService,
        CurrentUser $currentUser,
        ServerRequestInterface $request,
        ConfigInterface $config,
        SessionInterface $session,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        $currentDealerId = $currentUser->getIdentity()->currentDealerId;

        list($queryFilters, $sort, $sortOrder, $page, $perPage) = $this->extractQuerySearchParameters(
            $request,
            $config,
            ['sort' => 'car.created', 'sortOrder' => 'desc']
        );
        $searchData = $carService->buildSearchFilters($queryFilters);

        $filtersItemsWithCounts = $carService->searchFiltersItemsWithCarsCountForDealerCatalog($searchData->filters);

        if ($searchData->filters) {
            $searchData->filters = $carService->validateFiltersAvailability($queryFilters, $searchData->filters, $filtersItemsWithCounts);
        }

        // get cars models for first models filter
        // $makeId = array_key_exists('make', $queryFilters) ? $queryFilters['make'] : null;
        // $models = $carModelService->searchModelsForView(makeId: $makeId, routeName: "dealer.searchCar");

        // build objects for next make/model pairs in filters
        // $makeModelPairsSelects = $carService->buildMakeModelPairsSelects($queryFilters, "dealer.searchCar");

        $data = $carService->searchCarsForDealer(
            dealerId: $currentDealerId,
            filters: $searchData->filters,
            sort: $sort,
            sortOrder: $sortOrder,
            page: $page,
            perPage: $perPage
        );

        $dealer = $dealerService->getDealer($currentDealerId);

        $session->set("lastSearchCarDealerUrl", $request->getRequestTarget());
        $session->set("lastSearchCarDealerRouteName", $currentRoute->getName());

        return $this->viewRenderer->render(
            "search",
            [
                "items" => $data->items,
                "totalCount" => $data->totalCount,
                "page" => $data->page,
                "filters" => $queryFilters,
                "sort" => $sort,
                "sortOrder" => $sortOrder,
                "perPage" => $perPage,
                "filtersItemsWithCounts" => $filtersItemsWithCounts,
                // "makes" => $filtersItemsWithCounts->makes,
                // "models" => $models,
                "dealer" => $dealer,
                // "makeModelPairsSelects" => $makeModelPairsSelects
            ]
        );
    }

    public function addCar(
        SessionInterface $session
    ): ResponseInterface {
        $lastSearchCarUrl = $session->get("lastSearchCarDealerUrl", $this->urlGenerator->generate("dealer.searchCar"));

        return $this->viewRenderer->render("add-car", compact("lastSearchCarUrl"));
    }

    public function editCar(
        #[RouteArgument('publicId')]
        string $publicId,
        CarService $carService,
        CarMakeService $carMakeService,
        CarModelService $carModelService,
        SessionInterface $session,
        ConfigInterface $config,
        ServerRequestInterface $request,
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $unsavedChangesExists = false;

        if (array_key_exists("checkSession", $requestData)) {
            $car = $this->getCarSessionForPreview($publicId, $carService, $session);
            $unsavedChangesExists = property_exists($car, 'unsavedChangesExists');
        } else {
            $car = $carService->getCarForDealerEdit($publicId);
        }

        $this->setCarSessionForPreview($publicId, $car, $session);
        $makes = $carMakeService->searchMakes(filters: ["active" => true]);
        $models = ($car->makeId) ? $carModelService->searchModelsForEdit(makeId: $car->makeId) : [];
        $lastSearchCarUrl = $session->get("lastSearchCarDealerUrl", $this->urlGenerator->generate("dealer.searchCar"));

        // upload validation data
        $params = $config->get('params');
        $allowedMimeTypes = $params['uploadedFiles']['car']['allowedMimeTypes'];
        $allowedMimeTypesImages = $params['uploadedFiles']['car']['allowedMimeTypesImages'];
        $allowedMimeTypesVideos = $params['uploadedFiles']['car']['allowedMimeTypesVideos'];
        $maxNumberOfUploadedFiles = $params['uploadedFiles']['car']['maxNumberOfUploadedFiles'];
        $maxNumberOfAssignedFiles = $params['uploadedFiles']['car']['maxNumberOfAssignedDealerFiles'];
        $maxUploadFileSize = $params['uploadedFiles']['car']['maxUploadFileSize'];

        return $this->viewRenderer->render("edit-car", compact(
            "car",
            "makes",
            "models",
            "lastSearchCarUrl",
            "allowedMimeTypes",
            "allowedMimeTypesImages",
            "allowedMimeTypesVideos",
            "maxNumberOfUploadedFiles",
            "maxUploadFileSize",
            "maxNumberOfAssignedFiles",
            "unsavedChangesExists"
        ));
    }

    public function view(
        #[RouteArgument('publicId')]
        string $publicId,
        CarService $carService,
        SessionInterface $session,
        ConfigInterface $config,
        CurrentUser $currentUser
    ): ResponseInterface {
        $car = $carService->getCarForDealerView($publicId);
        $lastSearchCarUrl = $session->get("lastSearchCarDealerUrl", $this->urlGenerator->generate("dealer.searchCar"));
        list($queryFilters, $sort, $sortOrder) = $this->extractQuerySearchParameters(
            $lastSearchCarUrl,
            $config,
            ['sort' => 'car.created', 'sortOrder' => 'desc']
        );
        $searchData = $carService->buildSearchFilters($queryFilters);

        $lastSearchCarRouteName = $session->get("lastSearchCarRouteName", "dealer.searchCar");
        $data = $carService->getNextPrevCars(
            carId: $car->id,
            filters: $searchData->filters,
            sort: $sort,
            sortOrder: $sortOrder,
            lastSearchCarRouteName: $lastSearchCarRouteName
        );

        $carNextPublicId = $data->carNextPublicId;
        $carPrevPublicId = $data->carPrevPublicId;

        return $this->viewRenderer->render(
            "view",
            compact(
                "car",
                "lastSearchCarUrl",
                "carNextPublicId",
                "carPrevPublicId"
            )
        );
    }

    public function preview(
        #[RouteArgument('publicId')]
        string $publicId,
        CarService $carService,
        SessionInterface $session,
        ServerRequestInterface $request,
    ): ResponseInterface {
        $requestData = (object)$this->getRequestData($request);
        $car = $this->getCarSessionForPreview($publicId, $carService, $session);

        $lastSearchCarUrl = $session->get("lastSearchCarDealerUrl", $this->urlGenerator->generate("dealer.searchCar"));
        $carNextPublicId = $carPrevPublicId = null;
        $allowPublish = $requestData->allowPublish ?? true;

        return $this->viewRenderer->render(
            "preview",
            compact(
                "car",
                "lastSearchCarUrl",
                "carNextPublicId",
                "carPrevPublicId",
                "allowPublish"
            )
        );
    }

    /**
     * Handlers
     */
    public function doAddCar(
        CarService $carService,
        ServerRequestInterface $request
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $carData = $carService->getCarDataByVinCode($requestData);
        $carModel = $carService->createCarFromArray(requestData: (array)$carData, isClient: false);

        return $this->redirectByName("dealer.editCar", ["publicId" => $carModel->publicId]);
    }

    public function doAddEmptyCar(
        CarService $carService
    ): ResponseInterface {
        $carData = new CarData();
        $carModel = $carService->createEmptyCar(requestData: (array)$carData, isClient: false);

        return $this->redirectByName("dealer.editCar", ["publicId" => $carModel->publicId]);
    }

    public function doSaveDraftCar(
        CarService $carService,
        ServerRequestInterface $request
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $carService->saveDraftCarFromArray(requestData: $requestData, isClient: false);

        return $this->redirectByName("dealer.searchCar");
    }


    /**
     * Ajax
     */
    public function searchAjax(
        CarService $carService,
        ServerRequestInterface $request,
        ConfigInterface $config,
        DataResponseFactoryInterface $dataResponseFactory,
        SessionInterface $session,
        CurrentUser $currentUser,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        $currentDealerId = $currentUser->getIdentity()->currentDealerId;

        list($queryFilters, $sort, $sortOrder, $page, $perPage) = $this->extractQuerySearchParameters(
            $request,
            $config,
            ['sort' => 'car.created', 'sortOrder' => 'desc']
        );
        $searchData = $carService->buildSearchFilters($queryFilters);
        $filtersItemsWithCounts =  $carService->searchFiltersItemsWithCarsCountForDealerCatalog($searchData->filters);

        $data = $carService->searchCarsForDealer(
            dealerId: $currentDealerId,
            filters: $searchData->filters,
            sort: $sort,
            sortOrder: $sortOrder,
            page: $page,
            perPage: $perPage,
        );
        $session->set("lastSearchCarDealerUrl", str_replace("-ajax", "", $request->getRequestTarget()));
        $session->set("lastSearchCarDealerRouteName", str_replace("Ajax", "", $request->getRequestTarget()));

        return $dataResponseFactory->createResponse([
            'items' => $data->items,
            'totalCount' => $data->totalCount,
            'page' => $data->page,
            'filters' => $queryFilters,
            'filtersItemsWithCounts' => $filtersItemsWithCounts,
        ]);
    }


    public function getVinCodeDataAjax(
        CarService $carService,
        SessionInterface $session,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $carData = $carService->getCarDataByVinCode($requestData);

        $requestData = (object)$requestData;

        if (property_exists($requestData, 'publicId')) {
            $carForPreview = $this->getCarSessionForPreview($requestData->publicId, $carService, $session);

            if ($carForPreview) {
                $carForPreview = Ancillary::mergeObjects($carForPreview, $carData);
                $this->setCarSessionForPreview($requestData->publicId, $carForPreview, $session);
            }
        }

        return $dataResponseFactory->createResponse(compact("carData"));
    }

    public function deleteMediaAjax(
        CarService $carService,
        SessionInterface $session,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $car = $carService->deleteCarMediaFromArray(requestData: $requestData, isClient: false);
        $carForPreview = $this->getCarSessionForPreview($car->publicId, $carService, $session);
        $carForPreview = Ancillary::mergeObjects($carForPreview, $car);
        $this->setCarSessionForPreview($car->publicId, $carForPreview, $session);

        return $dataResponseFactory->createResponse(compact("car"));
    }

    public function setMediaMainAjax(
        CarService $carService,
        SessionInterface $session,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $car = $carService->setMediaMainFromArray(requestData: $requestData, isClient: false);
        $carForPreview = $this->getCarSessionForPreview($car->publicId, $carService, $session);
        $carForPreview = Ancillary::mergeObjects($carForPreview, $car);
        $this->setCarSessionForPreview($car->publicId, $carForPreview, $session);

        return $dataResponseFactory->createResponse(compact("car"));
    }

    public function uploadMediaAjax(
        CarService $carService,
        SessionInterface $session,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $files = $request->getUploadedFiles();
        $car = $carService->assignFilesToCarFromArray(requestData: $requestData, isClient: false, files: $files["files"]);
        $carForPreview = $this->getCarSessionForPreview($car->publicId, $carService, $session);
        $carForPreview = Ancillary::mergeObjects($carForPreview, $car);
        $this->setCarSessionForPreview($car->publicId, $carForPreview, $session);

        return $dataResponseFactory->createResponse(compact("car"));
    }

    public function sortMediaAjax(
        CarService $carService,
        SessionInterface $session,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $car = $carService->sortCarMediaFromArray(requestData: $requestData, isClient: false);
        $carForPreview = $this->getCarSessionForPreview($car->publicId, $carService, $session);
        $carForPreview = Ancillary::mergeObjects($carForPreview, $car);
        $this->setCarSessionForPreview($car->publicId, $carForPreview, $session);

        return $dataResponseFactory->createResponse(compact("car"));
    }

    public function doPublishCarAjax(
        CarService $carService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $car = $carService->publishCarFromArray(requestData: $requestData, isClient: false);

        return $dataResponseFactory->createResponse(compact("car"));
    }

    public function doPublishCarFromPreviewAjax(
        CarService $carService,
        SessionInterface $session,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $requestData = (object)$requestData;

        if ($requestData->publicId) {
            $requestData = $this->getCarSessionForPreview($requestData->publicId, $carService, $session);
            $carService->publishCarFromArray(requestData: (array)$requestData, isClient: false);
        }

        return $dataResponseFactory->createResponse(true);
    }

    public function doSaveDraftCarFromPreviewAjax(
        CarService $carService,
        SessionInterface $session,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $requestData = (object)$requestData;

        if ($requestData->publicId) {
            $requestData = $this->getCarSessionForPreview($requestData->publicId, $carService, $session);
            $carService->saveDraftCarFromArray(requestData: (array)$requestData, isClient: false);
        }

        return $dataResponseFactory->createResponse(true);
    }

    public function getModelsForViewAjax(
        CarModelService $carModelService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $models = $carModelService->getModelsForViewFromArray($requestData);

        return $dataResponseFactory->createResponse(["models" => $models]);
    }

    public function getModelsForEditAjax(
        CarModelService $carModelService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $models = $carModelService->getModelsForEditFromArray($requestData);

        return $dataResponseFactory->createResponse(["models" => $models]);
    }

    public function updatePreviewCarSessionAjax(
        SessionInterface $session,
        CarService $carService,
        CarMakeService $carMakeService,
        CarModelService $carModelService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $publicId = $requestData["publicId"];
        $key = $requestData["key"];
        $value = $requestData["value"];
        $car = $this->getCarSessionForPreview($publicId, $carService, $session);
        $car->{$key} = ($key != 'features') ? $value : explode(',', $value);

        if ($key == 'makeId') {
            $car->makeName = empty($value) ? '' : $carMakeService->findById($value)?->name;
        } elseif ($key == 'modelId') {
            $car->modelName = empty($value) ? '' : $carModelService->findById($value)?->name;
        } else {
            $car = $carService->rebuildSessionData($car);
        }

        $car->unsavedChangesExists = true;
        $this->setCarSessionForPreview($publicId, $car, $session);

        return $dataResponseFactory->createResponse(true);
    }

    public function restorePreviewCarSessionAjax(
        SessionInterface $session,
        CarService $carService,
        CarMakeService $carMakeService,
        CarModelService $carModelService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $car = $carService->getPreviewCarDataFromArray($requestData);
        $carForPreview = $this->getCarSessionForPreview($car->publicId, $carService, $session);
        $carForPreview = Ancillary::mergeObjects($carForPreview, $car);
        $carForPreview = $carService->rebuildSessionData($carForPreview);
        $carForPreview->makeName = empty($carForPreview->makeId) ? '' : $carMakeService->findById($carForPreview->makeId)?->name;
        $carForPreview->modelName = empty($carForPreview->modelId) ? '' : $carModelService->findById($carForPreview->modelId)?->name;
        $car->unsavedChangesExists = true;
        $this->setCarSessionForPreview($car->publicId, $carForPreview, $session);

        return $dataResponseFactory->createResponse(true);
    }





    private function getCarSessionForPreview(
        string $publicId,
        CarService $carService,
        SessionInterface $session
    ): object {
        $car = $session->get("updatedCarId_{$publicId}");

        if (!$car) {
            $car = $carService->getCarForDealerEdit($publicId);
        }

        return $car;
    }

    private function setCarSessionForPreview(
        string $publicId,
        object $car,
        SessionInterface $session
    ): void {
        $session->set("updatedCarId_{$publicId}", $car);
    }
}
