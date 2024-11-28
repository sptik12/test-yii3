<?php

namespace App\Frontend\Controller\Client;

use App\Backend\Component\CarData\CarData;
use App\Backend\Service\CarMakeService;
use App\Backend\Service\CarService;
use App\Backend\Service\CarSearchUrlService;
use App\Backend\Service\CarModelService;
use App\Backend\Service\GeoService;
use App\Frontend\Helper\Ancillary;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\User\CurrentUser;
use Yiisoft\Router\HydratorAttribute\RouteArgument;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Session\SessionInterface;

final class CarController extends AbstractClientController
{
    /**
     * Pages
     */
    public function search(
        CarService $carService,
        CarSearchUrlService $carSearchUrlService,
        CarModelService $carModelService,
        ServerRequestInterface $request,
        ConfigInterface $config,
        CurrentUser $currentUser,
        SessionInterface $session,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        list($queryFilters, $sort, $sortOrder, $page, $perPage) = $this->extractQuerySearchParameters(
            $request,
            $config,
            ['sort' => 'car.published', 'sortOrder' => 'desc']
        );
        $searchData = $carService->buildSearchFilters($queryFilters);
        $filtersItemsWithCounts = $carService->searchFiltersItemsWithCarsCountForClientCatalog($searchData->filters);

        if ($searchData->filters) {
            $searchData->filters = $carService->validateFiltersAvailability($queryFilters, $searchData->filters, $filtersItemsWithCounts);
        }

        $data = $carService->searchCarsForClient(
            filters: $searchData->filters,
            sort: $sort,
            sortOrder: $sortOrder,
            page: $page,
            perPage: $perPage
        );


        // get cars models for first models filter
        $makeId = array_key_exists('make', $queryFilters) ? $queryFilters['make'] : null;
        $models = $carModelService->searchModelsForView(makeId: $makeId, routeName: "client.searchCar");

        // build objects for next make/model pairs in filters
        $makeModelPairsSelects = $carService->buildMakeModelPairsSelects($queryFilters, "client.searchCar");

        $session->set("lastSearchCarUrl", $request->getRequestTarget());
        $session->set("lastSearchCarRouteName", $currentRoute->getName());

        $carSearchUrl = $currentUser->isGuest()
            ? null
            : $carSearchUrlService->getCurrentUrlInCarSearchUrls($_SERVER['REQUEST_URI'], $currentUser->getId());

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
                "makes" => $filtersItemsWithCounts->makes,
                "models" => $models,
                "title" => $searchData->title,
                "dealer" => $searchData->dealer,
                "makeModelPairsSelects" => $makeModelPairsSelects,
                "carSearchUrl" => $carSearchUrl
            ]
        );
    }

    public function myCars(
        CarService $carService,
        CarModelService $carModelService,
        ServerRequestInterface $request,
        ConfigInterface $config,
        SessionInterface $session,
        CurrentUser $currentUser,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        list($queryFilters, $sort, $sortOrder, $page, $perPage) = $this->extractQuerySearchParameters(
            $request,
            $config,
            ['sort' => 'car.created', 'sortOrder' => 'desc']
        );
        $searchData = $carService->buildSearchFilters($queryFilters);

        $filtersItemsWithCounts = $carService->searchFiltersItemsWithCarsCountForMyCarsCatalog($searchData->filters);

        if ($searchData->filters) {
            $searchData->filters = $carService->validateFiltersAvailability($queryFilters, $searchData->filters, $filtersItemsWithCounts);
        }

        // get cars models for first models filter
        $makeId = array_key_exists('make', $queryFilters) ? $queryFilters['make'] : null;
        $models = $carModelService->searchModelsForView(makeId: $makeId, routeName: "client.myCars");

        // build objects for next make/model pairs in filters
        $makeModelPairsSelects = $carService->buildMakeModelPairsSelects($queryFilters, "client.myCars");

        $data = $carService->searchCarsForMyCars(
            clientId: $currentUser->getId(),
            filters: $searchData->filters,
            sort: $sort,
            sortOrder: $sortOrder,
            page: $page,
            perPage: $perPage
        );

        $session->set("lastSearchCarUrl", $request->getRequestTarget());
        $session->set("lastSearchCarRouteName", $currentRoute->getName());

        return $this->viewRenderer->render(
            "mycars",
            [
                "items" => $data->items,
                "totalCount" => $data->totalCount,
                "page" => $data->page,
                "filters" => $queryFilters,
                "sort" => $sort,
                "sortOrder" => $sortOrder,
                "perPage" => $perPage,
                "filtersItemsWithCounts" => $filtersItemsWithCounts,
                "makes" => $filtersItemsWithCounts->makes,
                "models" => $models,
                "makeModelPairsSelects" => $makeModelPairsSelects
            ]
        );
    }

    public function wishlist(
        CarService $carService,
        CarModelService $carModelService,
        ServerRequestInterface $request,
        ConfigInterface $config,
        SessionInterface $session,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        list($queryFilters, $sort, $sortOrder, $page, $perPage) = $this->extractQuerySearchParameters(
            $request,
            $config,
            ['sort' => 'car.published', 'sortOrder' => 'desc']
        );
        $searchData = $carService->buildSearchFilters($queryFilters);
        $filtersItemsWithCounts = $carService->searchFiltersItemsWithCarsCountForWishlistCatalog($searchData->filters);

        $data = $carService->searchCarsForWishlist(
            filters: $searchData->filters,
            sort: $sort,
            sortOrder: $sortOrder,
            page: $page,
            perPage: $perPage
        );

        // get cars models for first models filter
        $makeId = array_key_exists('make', $queryFilters) ? $queryFilters['make'] : null;
        $models = $carModelService->searchModelsForView(makeId: $makeId, routeName: "client.wishlist");

        // build objects for next make/model pairs in filters
        $makeModelPairsSelects = $carService->buildMakeModelPairsSelects($queryFilters, "client.wishlist");

        $session->set("lastSearchCarUrl", $request->getRequestTarget());
        $session->set("lastSearchCarRouteName", $currentRoute->getName());

        return $this->viewRenderer->render(
            "wishlist",
            [
                "items" => $data->items,
                "totalCount" => $data->totalCount,
                "page" => $data->page,
                "filters" => $queryFilters,
                "sort" => $sort,
                "sortOrder" => $sortOrder,
                "perPage" => $perPage,
                "filtersItemsWithCounts" => $filtersItemsWithCounts,
                "makes" => $filtersItemsWithCounts->makes,
                "models" => $models,
                "makeModelPairsSelects" => $makeModelPairsSelects
            ]
        );
    }

    public function view(
        #[RouteArgument('publicId')]
        string $publicId,
        CarService $carService,
        SessionInterface $session,
        ConfigInterface $config
    ): ResponseInterface {
        $car = $carService->getCarForClientView($publicId);
        $lastSearchCarUrl = $session->get("lastSearchCarUrl", $this->urlGenerator->generate("client.searchCar"));
        list($queryFilters, $sort, $sortOrder) = $this->extractQuerySearchParameters(
            $lastSearchCarUrl,
            $config,
            ['sort' => 'car.published', 'sortOrder' => 'desc']
        );
        $searchData = $carService->buildSearchFilters($queryFilters);

        $lastSearchCarRouteName = $session->get("lastSearchCarRouteName", "client.searchCar");
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
        $lastSearchCarUrl = $session->get("lastSearchCarUrl", $this->urlGenerator->generate("client.myCars"));
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
            $car = $carService->getCarForClientEdit($publicId);
        }

        $this->setCarSessionForPreview($publicId, $car, $session);
        $makes = $carMakeService->searchMakes(filters: ["active" => true]);
        $models = ($car->makeId) ? $carModelService->searchModelsForEdit(makeId: $car->makeId) : [];
        $lastSearchCarUrl = $session->get("lastSearchCarUrl", $this->urlGenerator->generate("client.myCars"));

        // upload validation data
        $params = $config->get('params');
        $allowedMimeTypes = $params['uploadedFiles']['car']['allowedMimeTypes'];
        $allowedMimeTypesImages = $params['uploadedFiles']['car']['allowedMimeTypesImages'];
        $allowedMimeTypesVideos = $params['uploadedFiles']['car']['allowedMimeTypesVideos'];
        $maxNumberOfUploadedFiles = $params['uploadedFiles']['car']['maxNumberOfUploadedFiles'];
        $maxNumberOfAssignedFiles = $params['uploadedFiles']['car']['maxNumberOfAssignedClientFiles'];
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

    public function carSearchUrls(
        CarSearchUrlService $carSearchUrlService,
        ServerRequestInterface $request,
        ConfigInterface $config
    ): ResponseInterface {
        list($queryFilters, $sort, $sortOrder, $page, $perPage) = $this->extractQuerySearchParameters(
            $request,
            $config,
            ['sort' => 'carSearchUrl.created', 'sortOrder' => 'desc']
        );

        $data = $carSearchUrlService->searchCarSearchUrls(
            filters: [],
            sort: $sort,
            sortOrder: $sortOrder,
            page: $page,
            perPage: $perPage
        );

        return $this->viewRenderer->render(
            "search-urls",
            [
                "items" => $data->items,
                "totalCount" => $data->totalCount,
                "page" => $data->page,
                "filters" => $queryFilters,
                "sort" => $sort,
                "sortOrder" => $sortOrder,
                "perPage" => $perPage,
            ]
        );
    }

    public function addCar(
        SessionInterface $session
    ): ResponseInterface {
        $lastSearchCarUrl = $session->get("lastSearchCarUrl", $this->urlGenerator->generate("client.searchCar"));

        return $this->viewRenderer->render("add-car", compact("lastSearchCarUrl"));
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
        $carModel = $carService->createCarFromArray(requestData: (array)$carData, isClient: true);

        return $this->redirectByName("client.editCar", ["publicId" => $carModel->publicId]);
    }

    public function doAddEmptyCar(
        CarService $carService
    ): ResponseInterface {
        $carData = new CarData();
        $carModel = $carService->createEmptyCar(requestData: (array)$carData, isClient: true);

        return $this->redirectByName("client.editCar", ["publicId" => $carModel->publicId]);
    }

    public function doSaveDraftCar(
        CarService $carService,
        ServerRequestInterface $request
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $carService->saveDraftCarFromArray(requestData: $requestData, isClient: true);

        return $this->redirectByName("client.myCars");
    }


    /**
     * Ajax
     */
    public function searchAjax(
        CarService $carService,
        CarSearchUrlService $carSearchUrlService,
        ServerRequestInterface $request,
        ConfigInterface $config,
        DataResponseFactoryInterface $dataResponseFactory,
        CurrentUser $currentUser,
        SessionInterface $session,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        list($queryFilters, $sort, $sortOrder, $page, $perPage) = $this->extractQuerySearchParameters(
            $request,
            $config,
            ['sort' => 'car.published', 'sortOrder' => 'desc']
        );
        $searchData = $carService->buildSearchFilters($queryFilters);
        $filtersItemsWithCounts =  $carService->searchFiltersItemsWithCarsCountForClientCatalog($searchData->filters);

        if ($searchData->filters) {
            $searchData->filters = $carService->validateFiltersAvailability($queryFilters, $searchData->filters, $filtersItemsWithCounts);
        }

        $data = $carService->searchCarsForClient(
            filters: $searchData->filters,
            sort: $sort,
            sortOrder: $sortOrder,
            page: $page,
            perPage: $perPage,
        );
        $session->set("lastSearchCarUrl", str_replace("-ajax", "", $request->getRequestTarget()));
        $session->set("lastSearchCarRouteName", str_replace("Ajax", "", $currentRoute->getName()));

        $carSearchUrl = $currentUser->isGuest()
            ? null
            : $carSearchUrlService->getCurrentUrlInCarSearchUrls($_SERVER['REQUEST_URI'], $currentUser->getId());

        return $dataResponseFactory->createResponse([
            'items' => $data->items,
            'totalCount' => $data->totalCount,
            'page' => $data->page,
            'filters' => $queryFilters,
            'filtersItemsWithCounts' => $filtersItemsWithCounts,
            'title' => $searchData->title,
            "dealer" => $searchData->dealer,
            "carSearchUrl" => $carSearchUrl
        ]);
    }

    public function wishlistAjax(
        CarService $carService,
        ServerRequestInterface $request,
        ConfigInterface $config,
        DataResponseFactoryInterface $dataResponseFactory,
        SessionInterface $session,
        CurrentRoute $currentRoute
    ): ResponseInterface {
        list($queryFilters, $sort, $sortOrder, $page, $perPage) = $this->extractQuerySearchParameters(
            $request,
            $config,
            ['sort' => 'car.published', 'sortOrder' => 'desc']
        );
        $searchData = $carService->buildSearchFilters($queryFilters);
        $filtersItemsWithCounts =  $carService->searchFiltersItemsWithCarsCountForWishlistCatalog($searchData->filters);

        $data = $carService->searchCarsForWishlist(
            filters: $searchData->filters,
            sort: $sort,
            sortOrder: $sortOrder,
            page: $page,
            perPage: $perPage,
        );
        $session->set("lastSearchCarUrl", str_replace("-ajax", "", $request->getRequestTarget()));
        $session->set("lastSearchCarRouteName", str_replace("Ajax", "", $currentRoute->getName()));

        return $dataResponseFactory->createResponse([
            'items' => $data->items,
            'totalCount' => $data->totalCount,
            'page' => $data->page,
            'filters' => $queryFilters,
            'filtersItemsWithCounts' => $filtersItemsWithCounts
        ]);
    }

    public function myCarsAjax(
        CarService $carService,
        ServerRequestInterface $request,
        ConfigInterface $config,
        DataResponseFactoryInterface $dataResponseFactory,
        SessionInterface $session,
        CurrentRoute $currentRoute,
        CurrentUser $currentUser
    ): ResponseInterface {
        list($queryFilters, $sort, $sortOrder, $page, $perPage) = $this->extractQuerySearchParameters(
            $request,
            $config,
            ['sort' => 'car.created', 'sortOrder' => 'desc']
        );
        $searchData = $carService->buildSearchFilters($queryFilters);
        $filtersItemsWithCounts =  $carService->searchFiltersItemsWithCarsCountForMyCarsCatalog($searchData->filters);

        $data = $carService->searchCarsForMyCars(
            clientId: $currentUser->getId(),
            filters: $searchData->filters,
            sort: $sort,
            sortOrder: $sortOrder,
            page: $page,
            perPage: $perPage,
        );
        $session->set("lastSearchCarUrl", str_replace("-ajax", "", $request->getRequestTarget()));
        $session->set("lastSearchCarRouteName", str_replace("Ajax", "", $currentRoute->getName()));

        return $dataResponseFactory->createResponse([
            'items' => $data->items,
            'totalCount' => $data->totalCount,
            'page' => $data->page,
            'filters' => $queryFilters,
            'filtersItemsWithCounts' => $filtersItemsWithCounts
        ]);
    }

    public function carSearchUrlsAjax(
        CarSearchUrlService $carSearchUrlService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
        ConfigInterface $config
    ): ResponseInterface {
        list($queryFilters, $sort, $sortOrder, $page, $perPage) = $this->extractQuerySearchParameters(
            $request,
            $config,
            ['sort' => 'carSearchUrl.created', 'sortOrder' => 'desc']
        );

        $data = $carSearchUrlService->searchCarSearchUrls(
            filters: [],
            sort: $sort,
            sortOrder: $sortOrder,
            page: $page,
            perPage: $perPage
        );

        return $dataResponseFactory->createResponse([
            'items' => $data->items,
            'filters' => [],
            'totalCount' => $data->totalCount,
            'page' => $data->page,
        ]);
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

    public function setGeoDataForPostalCodeAjax(
        GeoService $geoService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $result = $geoService->setGeoDataForPostalCodeFromArray($requestData);

        return $dataResponseFactory->createResponse(["result" => $result]);
    }

    public function getPostalCodeByGeoDataAjax(
        GeoService $geoService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $result = $geoService->getPostalCodeByGeoDataFromArray($requestData);

        return $dataResponseFactory->createResponse(["postalCode" => $result]);
    }

    public function addCarToWishlistAjax(
        CarService $carService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $totalCount = $carService->addCarToWishListFromArray($requestData);

        return $dataResponseFactory->createResponse(["isCarSaved" => 1, "totalCount" => $totalCount]);
    }

    public function removeCarFromWishlistAjax(
        CarService $carService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $totalCount = $carService->removeCarFromWishlistFromArray($requestData);

        return $dataResponseFactory->createResponse(["isCarSaved" => 0, "totalCount" => $totalCount]);
    }

    public function addCarSearchUrlAjax(
        CarSearchUrlService $carSearchUrlService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $data = $carSearchUrlService->addCarSearchUrlFromArray($requestData);

        return $dataResponseFactory->createResponse($data); // $data => ['id' => $id, 'totalCount' => $totalCount]
    }

    public function updateCarSearchUrlAjax(
        CarSearchUrlService $carSearchUrlService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $id = $carSearchUrlService->updateCarSearchUrlFromArray($requestData);

        return $dataResponseFactory->createResponse(['id' => $id]);
    }

    public function removeCarSearchUrlAjax(
        CarSearchUrlService $carSearchUrlService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $totalCount = $carSearchUrlService->removeCarSearchUrlFromArray($requestData);

        return $dataResponseFactory->createResponse(["totalCount" => $totalCount]);
    }

    public function deleteCarSearchUrlAjax(
        CarSearchUrlService $carSearchUrlService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $totalCount = $carSearchUrlService->deleteCarSearchUrlFromArray($requestData);

        return $dataResponseFactory->createResponse(["totalCount" => $totalCount]);
    }

    public function restoreCarSearchUrlAjax(
        CarSearchUrlService $carSearchUrlService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $totalCount = $carSearchUrlService->restoreCarSearchUrlFromArray($requestData);

        return $dataResponseFactory->createResponse(["totalCount" => $totalCount]);
    }

    public function checkCarSearchUrlAjax(
        CarSearchUrlService $carSearchUrlService,
        ServerRequestInterface $request,
        DataResponseFactoryInterface $dataResponseFactory,
    ): ResponseInterface {
        $requestData = $this->getRequestData($request);
        $carSearchUrlModel = $carSearchUrlService->checkCarSearchUrlFromArray($requestData);

        return $dataResponseFactory->createResponse(
            $carSearchUrlModel
            ? ['isUrlExists' => 1, 'id' => $carSearchUrlModel->id]
            : ['isUrlExists' => 0, 'id' => '']
        );
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
        $car = $carService->deleteCarMediaFromArray(requestData: $requestData, isClient: true);
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
        $car = $carService->setMediaMainFromArray(requestData: $requestData, isClient: true);
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
        $car = $carService->assignFilesToCarFromArray(requestData: $requestData, isClient: true, files: $files["files"]);
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
        $car = $carService->sortCarMediaFromArray(requestData: $requestData, isClient: true);
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
        $car = $carService->publishCarFromArray(requestData: $requestData, isClient: true);

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
            $carService->publishCarFromArray(requestData: (array)$requestData, isClient: true);
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
            $carService->saveDraftCarFromArray(requestData: (array)$requestData, isClient: true);
        }

        return $dataResponseFactory->createResponse(true);
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

        if ($car->{$key} === 'true') {
            $car->{$key} = 1;
        }

        if ($car->{$key} === 'false') {
            $car->{$key} = 0;
        }

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
            $car = $carService->getCarForClientEdit($publicId);
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
