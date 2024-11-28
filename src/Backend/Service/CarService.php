<?php

namespace App\Backend\Service;

use App\Backend\Component\CarData\CarData;
use App\Backend\Component\CarData\CarDataInterface;
use App\Backend\Helper\DataHelper;
use App\Backend\Model\Car\BedSize;
use App\Backend\Model\Car\BodyType;
use App\Backend\Model\Car\CabinSize;
use App\Backend\Model\Car\CarCatalogType;
use App\Backend\Model\Car\CarMediaStatus;
use App\Backend\Model\Car\CarMediaType;
use App\Backend\Model\Car\CarModel;
use App\Backend\Model\Car\CarMediaModel;
use App\Backend\Model\Car\Drivetrain;
use App\Backend\Model\Car\Feature;
use App\Backend\Model\Car\FuelType;
use App\Backend\Model\Car\IntColor;
use App\Backend\Model\Car\ExtColor;
use App\Backend\Model\Car\PriceStatus;
use App\Backend\Model\Car\SafetyRating;
use App\Backend\Model\Car\CarStatus;
use App\Backend\Model\Car\Condition;
use App\Backend\Model\Car\Transmission;
use App\Backend\Model\Car\VehicleType;
use App\Backend\Model\Car\CarUserModel;
use App\Backend\Model\Dealer\DealerModel;
use App\Backend\Model\Province;
use App\Backend\Search\CarSearch;
use App\Backend\Component\Image\Image;
use App\Frontend\Helper\FormatHelper;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Files\FileHelper;
use Yiisoft\Injector\Injector;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Target\File\FileTarget;
use Yiisoft\Strings\StringHelper;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\User\CurrentUser;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Db\Expression\JsonExpression;
use Yiisoft\Db\Expression\Expression;
use Yiisoft\Router\UrlGeneratorInterface;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;

final class CarService extends AbstractService
{
    public function __construct(
        protected CarSearch $carSearch,
        protected UrlGeneratorInterface $urlGenerator,
        protected TranslatorInterface $translator,
        protected ConfigInterface $config,
        protected Injector $injector,
        protected Aliases $aliases
    ) {
        parent::__construct($injector);
    }


    /**
     * Find
     */
    public function findById(int $id): ?CarModel
    {
        return CarModel::findOne($id);
    }

    public function findByPublicId(string $publicId): ?CarModel
    {
        return CarModel::findOne(["publicId" => $publicId]);
    }

    public function findByVinCodeForDealer(string $vinCode, int $dealerId, ?int $excludeCarId = null): ?CarModel
    {
        if (!$excludeCarId) {
            return CarModel::findOne(["vinCode" => $vinCode, "dealerId" => $dealerId]);
        } else {
            return CarModel::findOne(['and', ["vinCode" => $vinCode, "dealerId" => $dealerId, ['<>', 'id', $excludeCarId]]]);
        }
    }

    public function findByVinCodeForClient(string $vinCode, int $clientId, ?int $excludeCarId = null): ?CarModel
    {
        if (!$excludeCarId) {
            return CarModel::findOne(["vinCode" => $vinCode, "clientId" => $clientId]);
        } else {
            return CarModel::findOne(['and', ["vinCode" => $vinCode, "clientId" => $clientId, ['<>', 'id', $excludeCarId]]]);
        }
    }

    public function findCarMedia(int $id, int $carId): ?CarMediaModel
    {
        return CarMediaModel::findOne(["id" => $id, "carId" => $carId]);
    }

    public function findFirstCarMedia(int $carId): ?CarMediaModel
    {
        $medias = CarMediaModel::find()->where(["carId" => $carId])->orderBy(['orderType' => SORT_ASC, 'order' => SORT_ASC])->limit(1)->all();

        return $medias ? $medias[0] : null;
    }

    public function findCarMedias(int $carId): array
    {
        return CarMediaModel::find()->where(["carId" => $carId])->orderBy(['orderType' => SORT_ASC, 'order' => SORT_ASC])->all();
    }

    public function findCarUser(int $carId, int $userId): ?CarUserModel
    {
        return CarUserModel::findOne(["userId" => $userId, "carId" => $carId]);
    }


    /**
     * Count
     */
    public function getCarUserCount($userId): int
    {
        return $this->carSearch->getTotalRecords(
            filters: ["active" => true],
            joinsWith: [
                "activeDealerOrClient",
                "carUser" => ["userId" => $userId, "joinType" => "INNER JOIN"]
            ]
        );
    }

    public function getClientCarsCount(int $userId): int
    {
        return $this->carSearch->getTotalRecords(
            filters: ["client" => $userId]
        );
    }


    public function validateFiltersAvailability(
        array &$queryFilters,
        array $filters,
        object $filtersItemsWithCarsCount
    ): array {
        if (property_exists($filtersItemsWithCarsCount, 'years')) {
            $availableYears = $filtersItemsWithCarsCount->years;

            if (array_key_exists('minYear', $filters) && !in_array($filters['minYear'], $availableYears)) {
                unset($filters['minYear']);
                unset($queryFilters['minYear']);
            }

            if (array_key_exists('maxYear', $filters) && !in_array($filters['maxYear'], $availableYears)) {
                unset($filters['maxYear']);
                unset($queryFilters['maxYear']);
            }
        }

        return $filters;
    }

    /**
     * Build filters
     */
    protected function buildSearchFilters(
        array $queryFilters,
        GeoService $geoService,
        DealerService $dealerService
    ): object {
        $filters = [];
        $postalCodeGeoData = null;

        if (array_key_exists('postalCode', $queryFilters)) {
            $postalCode = str_replace(' ', '', $queryFilters['postalCode']);
            $postalCodeGeoData = $geoService->setGeoDataForPostalCodeFromArray(["postalCode" => $postalCode]);

            if ($postalCodeGeoData) {
                $postalCodeGeoData = $this->hydrateModelToObject($postalCodeGeoData);
                $distance = 100000; // default National

                if (array_key_exists("distance", $queryFilters)) {
                    $distance = $queryFilters["distance"] != 'provincial' ? $queryFilters["distance"] : 100000;

                    if ($queryFilters["distance"] == 'provincial') {
                        $filters['province'] = $postalCodeGeoData->province;
                    }
                }

                $distance = $distance * 1000; // km => m
                $filters['distance'] = [
                    'distance' => $distance,
                    'longitude' => $postalCodeGeoData->longitude,
                    'latitude' => $postalCodeGeoData->latitude
                ];
            }
        }

        if (array_key_exists('makeModelPairs', $queryFilters)) {
            $filters['makeModelPairs'] = $queryFilters['makeModelPairs'];
        }

        $makeModelFilter = [];

        if (array_key_exists('make', $queryFilters)) {
            $makeModelFilter[] = $queryFilters['make'];
        }

        if (array_key_exists('model', $queryFilters)) {
            $makeModelFilter[] = $queryFilters['model'];
        }

        if ($makeModelFilter) {
            $filters['makeModelPairs'][] = implode(',', $makeModelFilter);
        }

        foreach ($queryFilters as $key => $value) {
            switch ($key) {
                case 'minYear':
                case 'maxYear':
                case 'minPrice':
                case 'maxPrice':
                case 'minMileage':
                case 'maxMileage':
                case 'minEngine':
                case 'maxEngine':
                case 'minFuelEconomy':
                case 'maxFuelEconomy':
                case 'minCo2':
                case 'maxCo2':
                case "minEvBatteryRange":
                case "maxEvBatteryRange":
                case "minEvBatteryTime":
                case "maxEvBatteryTime":
                case "minDaysOnMarket":
                case "maxDaysOnMarket":
                case "dealer":
                    $filters[$key] = (int)$value;
                    break;

                case 'bodyType':
                case 'transmission':
                case 'drivetrain':
                case 'fuelType':
                case 'feature':
                case 'doors':
                case 'seats':
                case 'cabinSize':
                case 'bedSize':
                case 'intColor':
                case 'extColor':
                case 'safetyRating':
                case 'status':
                    $filters[$key] = $value;
                    break;

                case 'condition':
                    $possibleValues = array_column(Condition::cases(), "value");

                    if (in_array($value, $possibleValues)) {
                        $filters['condition'] = $value;
                    }
                    break;

                case 'certifiedPreOwned':
                case 'withPhotos':
                case 'priceDrops':
                    $filters[$key] = (bool)$value;
                    break;
            };
        }

        $title = $postalCodeGeoData
            ? $this->translator->translate("Fuel-efficient cars for sale in {place}", ['place' => $postalCodeGeoData->region])
            : $this->translator->translate("Fuel-efficient cars for sale nationwide");

        $dealer = null;

        if (array_key_exists('dealer', $filters)) {
            $dealerId = (int)$filters["dealer"];
            $dealer = $dealerService->getDealer($dealerId);
        }

        return (object)compact("filters", "title", "dealer");
    }

    protected function buildMakeModelPairsSelects(
        array $queryFilters,
        string $routeName,
        CarMakeService $carMakeService,
        CarModelService $carModelService,
        CurrentUser $currentUser
    ): array {
        $makeModelPairsSelects = [];

        if (array_key_exists('makeModelPairs', $queryFilters)) {
            foreach ($queryFilters['makeModelPairs'] as $index => $makeModelPair) {
                $aMakeModelPair = explode(',', $makeModelPair);
                $makeId = (int)($aMakeModelPair[0]);

                if ($makeId == 0) {
                    $makeId = '';
                }

                $makeName = '';

                if ($makeId) {
                    $make = $carMakeService->findById($makeId);
                    $makeName = $make->name;
                }

                $modelId = count($aMakeModelPair) > 1 ? (int)($aMakeModelPair[1]) : '';

                if ($modelId == 0) {
                    $modelId = '';
                }

                $modelName = '';

                if ($modelId) {
                    $model = $carModelService->findById($modelId);
                    $modelName = $model->name;
                }

                $models = $makeId
                    ? $carModelService->searchModelsForView(makeId: $makeId, routeName: $routeName, currentUser: $currentUser)
                    : [];

                $object = new \stdClass();
                $object->index = $index;
                $object->makeId = $makeId;
                $object->modelId = $modelId;
                $object->models = $models;
                $object->makeName = $makeName;
                $object->modelName = $modelName;
                $makeModelPairsSelects[] = $object;
            }
        }

        return $makeModelPairsSelects;
    }

    /**
     * Search
     */
    protected function searchTotal(
        array $filters,
        array $joinsWith,
    ): int {
        return $this->carSearch->getTotalRecords(
            filters: $filters,
            joinsWith: $joinsWith,
        );
    }

    protected function searchCarsForClient(
        array $filters,
        string $sort,
        string $sortOrder,
        int $page,
        int $perPage,
        CurrentUser $currentUser
    ): object {
        $filters = array_merge($filters, ["active" => true]);
        $joinsWithForTotal = ["activeDealerOrClient"];
        $joinsWithForSearch = [
            'activeDealerOrClient',
            'carUser' => ["userId" => $currentUser->getId(), "joinType" => "LEFT JOIN"]
        ];

        return $this->searchCarsForCatalog(
            filters: $filters,
            sort: $sort,
            sortOrder: $sortOrder,
            page: $page,
            perPage: $perPage,
            additionalHydrator: function ($car) use ($currentUser) {
                $car->canSaveCarToWishlist = $currentUser->isGuest() ? 0 : 1;

                return $car;
            },
            joinsWithForTotal: $joinsWithForTotal,
            joinsWithForSearch: $joinsWithForSearch,
        );
    }

    protected function searchCarsForDealer(
        int $dealerId,
        array $filters,
        string $sort,
        string $sortOrder,
        int $page,
        int $perPage,
    ): object {
        $filters = array_merge($filters, ["dealer" => $dealerId]);

        return $this->searchCarsForCatalog(
            filters: $filters,
            sort: $sort,
            sortOrder: $sortOrder,
            page: $page,
            perPage: $perPage,
            additionalHydrator: function ($car) {
                $car->canSaveCarToWishlist = false;

                return $car;
            }
        );
    }

    protected function searchCarsForWishlist(
        array $filters,
        string $sort,
        string $sortOrder,
        int $page,
        int $perPage,
        CurrentUser $currentUser
    ): object {
        $filters = array_merge($filters, ["active" => true]);
        $joinsWith = [
            "activeDealerOrClient",
            "carUser" => ["userId" => $currentUser->getId(), "joinType" => "INNER JOIN"]
        ];

        return $this->searchCarsForCatalog(
            filters: $filters,
            sort: $sort,
            sortOrder: $sortOrder,
            page: $page,
            perPage: $perPage,
            additionalHydrator: function ($car) {
                $car->canSaveCarToWishlist = true;

                return $car;
            },
            joinsWithForTotal: $joinsWith,
            joinsWithForSearch: $joinsWith
        );
    }

    protected function searchCarsForMyCars(
        int $clientId,
        array $filters,
        string $sort,
        string $sortOrder,
        int $page,
        int $perPage,
    ): object {
        $filters = array_merge($filters, ["clientId" => $clientId]);

        return $this->searchCarsForCatalog(
            filters: $filters,
            sort: $sort,
            sortOrder: $sortOrder,
            page: $page,
            perPage: $perPage,
            additionalHydrator: function ($car) {
                $car->canSaveCarToWishlist = false;

                return $car;
            }
        );
    }

    protected function searchCarsForCatalog(
        array $filters,
        string $sort,
        string $sortOrder,
        int $page,
        int $perPage,
        ?callable $additionalHydrator = null,
        array $joinsWithForTotal = [],
        array $joinsWithForSearch = [],
    ): object {
        $items = [];
        $totalCount = $this->carSearch->getTotalRecords(
            filters: $filters,
            joinsWith: $joinsWithForTotal,
        );

        if ($totalCount) {
            $totalPages = ceil($totalCount / $perPage);

            if ($page > $totalPages) {
                $page = $totalPages;
            }

            $fields = [
                "car.id", "car.publicId", "car.status", "car.vinCode", "car.condition", "car.year", "car.trim",
                "car.bodyType", "car.features", "car.mileage", "car.fuelType", "car.transmission", "car.vehicleType",
                "car.drivetrain", "car.engine", "car.engineType", "car.price", "car.published", "car.stockNumber",
                "car.dealerId", "car.clientId"
            ];

            if (array_key_exists("distance", $filters)) {
                $longitude = $filters["distance"]["longitude"];
                $latitude = $filters["distance"]["latitude"];
                $fields[] = "ROUND(ST_Distance_Sphere(
                    point({$longitude}, {$latitude}),
                    point(`car`.`longitude`, `car`.`latitude`)
                )/1000,0) AS distance";
            }

            $joinsWith = array_merge(['mediaMain', 'make', 'model'], $joinsWithForSearch);
            $items = $this->carSearch->search(
                fields: $fields,
                filters: $filters,
                joinsWith: $joinsWith,
                sort: "{$sort} {$sortOrder}, car.id",
                perPage: $perPage,
                offset: ($page - 1) * $perPage,
            );

            foreach ($items as &$car) {
                $car = $this->hydrateToCatalogCard($car);
                $car = $additionalHydrator ? $additionalHydrator($car) : $car;
            }
        }

        return (object)compact("items", "totalCount", "page");
    }

    protected function searchFiltersItemsWithCarsCountForClientCatalog(
        array $filters,
        CurrentUser $currentUser
    ): object {
        $result = [];
        $filtersBy = [
            'makes',
            'feature',
            'bodyType',
            'transmission',
            'drivetrain',
            'fuelType',
            'doors',
            'seats',
            'cabinSize',
            'bedSize',
            'intColor',
            'extColor',
            'safetyRating'
        ];

        foreach ($filtersBy as $filterName) {
            $result[$filterName] = $this->carSearch->searchWithCountBy(
                filterName: $filterName,
                filters: $filters,
                joinsWith: ['activeDealerOrClient'],
                baseFilters: ['active' => true],
                currentUser: $currentUser
            );
        }

        $makeIds = [];

        if (array_key_exists('makeModelPairs', $filters)) {
            $makeModelPairs = $filters["makeModelPairs"];

            foreach ($makeModelPairs as $makeModelPair) {
                $aMakeModelPair = explode(',', $makeModelPair);
                $makeId = (int)($aMakeModelPair[0]);

                if ($makeId) {
                    $makeIds[] = $makeId;
                }
            }
        }

        $years = $this->carSearch->searchYears($makeIds);
        $result['years'] = $years;

        return (object)$result;
    }

    protected function searchFiltersItemsWithCarsCountForWishlistCatalog(
        array $filters,
        CurrentUser $currentUser
    ): object {
        $result = [];
        $filtersBy = [
            'makesInWishlist',
        ];

        foreach ($filtersBy as $filterName) {
            $resultKey = str_starts_with($filterName, 'makes') ? 'makes' : $filterName;
            $result[$resultKey] = $this->carSearch->searchWithCountBy(
                filterName: $filterName,
                filters: [],
                joinsWith: ['activeDealerOrClient'],
                baseFilters: ['active' => true],
                currentUser: $currentUser
            );
        }

        return (object)$result;
    }

    protected function searchFiltersItemsWithCarsCountForDealerCatalog(
        array $filters,
        CurrentUser $currentUser
    ): object {
        $result = [];
        $filtersBy = [
            "status"
            // 'makesForDealerCatalog'
        ];

        foreach ($filtersBy as $filterName) {
            $resultKey = str_starts_with($filterName, 'makes') ? 'makes' : $filterName;
            $result[$resultKey] = $this->carSearch->searchWithCountBy(
                filterName: $filterName,
                filters: $filters,
                joinsWith: [],
                baseFilters: ["dealerId" => $currentUser->getIdentity()->currentDealerId],
                currentUser: $currentUser
            );
        }

        return (object)$result;
    }

    protected function searchFiltersItemsWithCarsCountForMyCarsCatalog(
        array $filters,
        CurrentUser $currentUser
    ): object {
        $result = [];
        $filtersBy = [
            'status',
            'makesForMyCarsCatalog'
        ];

        foreach ($filtersBy as $filterName) {
            $resultKey = str_starts_with($filterName, 'makes') ? 'makes' : $filterName;
            $result[$resultKey] = $this->carSearch->searchWithCountBy(
                filterName: $filterName,
                filters: $filters,
                joinsWith: [],
                baseFilters: ["clientId" => $currentUser->getId()],
                currentUser: $currentUser
            );
        }

        return (object)$result;
    }

    protected function getNextPrevCars(
        int $carId,
        array $filters,
        string $sort,
        string $sortOrder,
        string $lastSearchCarRouteName,
        CurrentUser $currentUser
    ): object {
        $baseFilters = $joinsWith = [];

        switch ($lastSearchCarRouteName) {
            case "client.searchCar":
                $baseFilters = ["active" => true];
                $joinsWith = ["activeDealerOrClient"];
                break;
            case "client.wishlist":
                $baseFilters = [];
                $joinsWith = [
                    "activeDealerOrClient",
                    "carUser" => ["userId" => $currentUser->getId(), "joinType" => "INNER JOIN"]
                ];
                break;
            case "client.myCars":
                $baseFilters = ["clientId" => $currentUser->getId()];
                break;
            case "dealer.searchCar":
                $baseFilters = ["dealerId" => $currentUser->getIdentity()->currentDealerId];
                break;
        }

        $carNextPublicId = $carPrevPublicId = null;
        $filters = array_merge($filters, $baseFilters);
        $fields = ["car.id", "car.publicId"];

        if ($sort != "distance") {
            $fields[] = $sort;
        } else {
            $distanceFilterKey = array_key_exists("distance", $filters) ? "distance" : null;

            if ($distanceFilterKey) {
                $longitude = $filters["distance"]["longitude"];
                $latitude = $filters["distance"]["latitude"];
                $fields[] = "ROUND(ST_Distance_Sphere(point({$longitude}, {$latitude}),point(`car`.`longitude`, `car`.`latitude`))/1000,0) AS distance";
            } else {
                return (object)compact("carNextPublicId", "carPrevPublicId");
            }
        }

        $items = $this->carSearch->search(
            fields: $fields,
            filters: ['id' => $carId],
            joinsWith: $joinsWith,
            asArray: true
        );

        if ($items) {
            $currentCar = $items[0];
            $sortAttribute = str_replace("car.", "", $sort);
            $lessOrGreaterIdNext = ">";
            $sortOrderIdNext = "";
            $lessOrGreaterIdPrev = "<";
            $sortOrderIdPrev = "desc";
            $lessOrGreaterValueNext = $lessOrGreaterValuePrev = ">";
            $sortOrderValueNext = $sortOrderValuePrev = "";

            if ($sortOrder == "desc") {
                $lessOrGreaterValueNext = "<";
                $sortOrderValueNext = "desc";
            } else {
                $lessOrGreaterValuePrev = "<";
                $sortOrderValuePrev = "desc";
            }

            $filters['prevNext'] = [
                'lessOrGreaterValue' => $lessOrGreaterValueNext,
                'lessOrGreaterId' => $lessOrGreaterIdNext,
                'field' => $sort,
                "value" => $currentCar->{$sortAttribute},
                "currentId" => $currentCar->id
            ];

            if ($sort == "distance") {
                $filters["prevNext"]["longitude"] = $filters["distance"]["longitude"];
                $filters["prevNext"]["latitude"] = $filters["distance"]["latitude"];
            }

            $carNext = $this->carSearch->search(
                fields: $fields,
                filters: $filters,
                joinsWith: $joinsWith,
                sort: "{$sort} {$sortOrderValueNext}, car.id {$sortOrderIdNext}",
                perPage: 1
            );

            if ($carNext) {
                $carNext = $this->hydrateModelToObject($carNext[0]);
                $carNextPublicId = $carNext?->publicId;
            }

            $filters['prevNext']['lessOrGreaterValue'] = $lessOrGreaterValuePrev;
            $filters['prevNext']['lessOrGreaterId'] = $lessOrGreaterIdPrev;
            $carPrev = $this->carSearch->search(
                fields: $fields,
                filters: $filters,
                joinsWith: $joinsWith,
                sort: "{$sort} {$sortOrderValuePrev}, car.id {$sortOrderIdPrev}",
                perPage: 1
            );

            if ($carPrev) {
                $carPrev = $this->hydrateModelToObject($carPrev[0]);
                $carPrevPublicId = $carPrev?->publicId;
            }
        }

        return (object)compact("carNextPublicId", "carPrevPublicId");
    }


    /**
     * Methods
     */

    protected function getCarDataByVinCode(
        array $requestData,
        CarDataInterface $carDataFactory,
    ): CarData {
        $vinCode = $requestData["vinCode"];
        $carData = $carDataFactory->getCarDataByVinCode($vinCode);

        return $carData;
    }

    protected function getCarForClientEdit(
        string $publicId
    ): object {
        $car = $this->findByPublicId($publicId);
        $car = $this->hydrateToCarCardForEdit($car);
        $car->canSaveCarToWishlist = false;

        return $car;
    }

    protected function getCarForDealerEdit(
        string $publicId
    ): object {
        $car = $this->findByPublicId($publicId);
        $car = $this->hydrateToCarCardForEdit($car);
        $car->canSaveCarToWishlist = false;

        return $car;
    }

    protected function getCarForClientView(
        string $publicId,
        CurrentUser $currentUser
    ): object {
        $joinsWith = ["carUser" => ["userId" => $currentUser->getId(), "joinType" => "LEFT JOIN"]];
        $car = $this->carSearch->searchOne(
            fields: ["car.*"],
            filters: ["publicId" => $publicId],
            joinsWith: !$currentUser->isGuest() ? $joinsWith : []
        );

        $car = $this->hydrateToCarCardForView($car);
        $car->canSaveCarToWishlist = $currentUser->isGuest() ? 0 : 1;

        return $car;
    }

    protected function getCarForDealerView(
        string $publicId
    ): object {
        $car = $this->carSearch->searchOne(
            fields: ["car.*"],
            filters: ["publicId" => $publicId]
        );

        $car = $this->hydrateToCarCardForView($car);
        $car->canSaveCarToWishlist = false;

        return $car;
    }

    protected function getCarMedias(
        int $carId
    ): ?object {
        $carModel = $this->carSearch->searchOne(
            fields: ["car.id", "car.publicId"],
            filters: ["id" => $carId],
            joinsWith: ['mediaMain']
        );

        $car = $this->hydrateToCarMediasOnly($carModel);

        return $car;
    }

    protected function createCarFromArray(
        array $requestData,
        bool $isClient,
        CurrentUser $currentUser,
    ): ?CarModel {
        $requestData = (object)$requestData;
        $car = new CarModel();
        $car->vinCode = $requestData->vinCode;
        $car->price = $requestData->price;
        $car->status = $requestData->status;
        $car->makeId = $requestData->makeId;
        $car->modelId = $requestData->modelId;
        $car->trim = $requestData->trim;
        $car->mileage = $requestData->mileage;
        $car->condition = $requestData->condition;
        $car->year = $requestData->year;
        $car->bodyType = $requestData->bodyType;
        $car->vehicleType = $requestData->vehicleType;
        $car->engine = $requestData->engine;
        $car->engineType = $requestData->engineType;
        $car->transmission = $requestData->transmission;
        $car->drivetrain = $requestData->drivetrain;
        $car->cylinders = $requestData->cylinders;
        $car->fuelType = $requestData->fuelType;
        $car->fuelEconomy = $requestData->fuelEconomy;
        $car->co2 = $requestData->co2;
        $car->evBatteryRange = $requestData->evBatteryRange;
        $car->evBatteryTime = $requestData->evBatteryTime;
        $car->madeIn = $requestData->madeIn;
        $car->doors = $requestData->doors;
        $car->seats = $requestData->seats;
        $car->extColor = $requestData->extColor;
        $car->intColor = $requestData->intColor;
        $car->cabinSize = $requestData->cabinSize;
        $car->bedSize = $requestData->bedSize;
        $car->safetyRating = $requestData->safetyRating;
        $car->priceDrops = $requestData->priceDrops;
        $car->certifiedPreOwned = $requestData->certifiedPreOwned;
        $car->description = $requestData->description;
        $car->features = !empty($requestData->features) ? new JsonExpression($requestData->features) : null;
        $car->creatorId = $currentUser->getId();
        $car->dealerId = $requestData->dealerId;
        $car->clientId = $requestData->clientId;
        $car->carfaxUrl = $requestData->carfaxUrl;

        if ($car->clientId) {
            $client = $currentUser->getIdentity();
            $car->contactName = $client->username;
            $car->phone = $client->phone;
            $car->address = $client->address;
            $car->province = $client->province;
            $car->postalCode = $client->postalCode;
            $car->keepLocationPrivate = $client->keepLocationPrivate;
            $car->latitude = $client->latitude;
            $car->longitude = $client->longitude;
        } else {
            $dealer = $car->getDealer();
            $car->contactName = null;
            $car->phone = $dealer?->phone;
            $car->address = $dealer?->address;
            $car->province = $dealer?->province;
            $car->postalCode = $dealer?->postalCode;
            $car->keepLocationPrivate = 1;
            $car->latitude = $dealer?->latitude;
            $car->longitude = $dealer?->longitude;
        }

        // generated values
        $car->published = $car->status == CarStatus::Published->value ? new Expression("NOW()") : null;
        $car->publicId = $this->generatePublicId();

        $car->save();

        return $car;
    }

    protected function createEmptyCar(
        array $requestData, // these data filled in validator
        bool $isClient,
        CurrentUser $currentUser
    ): ?CarModel {
        return $this->createCarFromArray($requestData, $isClient, $currentUser);
    }

    protected function saveDraftCarFromArray(
        array $requestData,
        bool $isClient,
        GeoService $geoService
    ): ?CarModel {
        $requestData = (object)$requestData;
        $car = $this->findByPublicId($requestData->publicId);

        // status
        $car->status = CarStatus::Draft->value;

        // other
        $car->vinCode = $requestData->vinCode;
        $car->price = $requestData->price;
        $car->makeId = $requestData->makeId;
        $car->modelId = $requestData->modelId;
        $car->trim = $requestData->trim;
        $car->mileage = $requestData->mileage;
        $car->condition = $requestData->condition;
        $car->year = $requestData->year;
        $car->bodyType = $requestData->bodyType;
        $car->vehicleType = $requestData->vehicleType;
        $car->engine = $requestData->engine;
        $car->engineType = $requestData->engineType;
        $car->transmission = $requestData->transmission;
        $car->drivetrain = $requestData->drivetrain;
        $car->cylinders = $requestData->cylinders;
        $car->fuelType = $requestData->fuelType;
        $car->fuelEconomy = $requestData->fuelEconomy;
        $car->co2 = $requestData->co2;
        $car->evBatteryRange = $requestData->evBatteryRange;
        $car->evBatteryTime = $requestData->evBatteryTime;
        $car->madeIn = $requestData->madeIn;
        $car->doors = $requestData->doors;
        $car->seats = $requestData->seats;
        $car->extColor = $requestData->extColor;
        $car->intColor = $requestData->intColor;
        $car->cabinSize = $requestData->cabinSize;
        $car->bedSize = $requestData->bedSize;
        $car->safetyRating = $requestData->safetyRating;
        $car->priceDrops = $requestData->priceDrops;
        $car->certifiedPreOwned = $requestData->certifiedPreOwned;
        $car->description = $requestData->description;
        $car->features = !empty($requestData->features) ? new JsonExpression($requestData->features) : null;
        $car->carfaxUrl = $requestData->carfaxUrl;

        if ($isClient) {
            $car->contactName = $requestData->contactName;
            $car->phone = $requestData->phone;
            $car->address = $requestData->address;
            $car->province = $requestData->province;
            $car->postalCode = $requestData->postalCode;
            $car->keepLocationPrivate = $requestData->keepLocationPrivate;
        }

        $car->save();

        if ($isClient) {
            $car = $geoService->setCarGeoData($car->id);
        }

        return $car;
    }

    protected function publishCarFromArray(
        array $requestData,
        bool $isClient,
        GeoService $geoService
    ): ?CarModel {
        $requestData = (object)$requestData;
        $car = $this->findByPublicId($requestData->publicId);

        if ($car->status != CarStatus::Published->value) {
            $car->published = new Expression("NOW()");
        }

        // status
        $car->status = CarStatus::Published->value;

        $car->vinCode = $requestData->vinCode;
        $car->price = $requestData->price;
        $car->makeId = $requestData->makeId;
        $car->modelId = $requestData->modelId;
        $car->trim = $requestData->trim;
        $car->mileage = $requestData->mileage;
        $car->condition = $requestData->condition;
        $car->year = $requestData->year;
        $car->bodyType = $requestData->bodyType;
        $car->vehicleType = $requestData->vehicleType;
        $car->engine = $requestData->engine;
        $car->engineType = $requestData->engineType;
        $car->transmission = $requestData->transmission;
        $car->drivetrain = $requestData->drivetrain;
        $car->cylinders = $requestData->cylinders;
        $car->fuelType = $requestData->fuelType;
        $car->fuelEconomy = $requestData->fuelEconomy;
        $car->co2 = $requestData->co2;
        $car->evBatteryRange = $requestData->evBatteryRange;
        $car->evBatteryTime = $requestData->evBatteryTime;
        $car->madeIn = $requestData->madeIn;
        $car->doors = $requestData->doors;
        $car->seats = $requestData->seats;
        $car->extColor = $requestData->extColor;
        $car->intColor = $requestData->intColor;
        $car->cabinSize = $requestData->cabinSize;
        $car->bedSize = $requestData->bedSize;
        $car->safetyRating = $requestData->safetyRating;
        $car->priceDrops = $requestData->priceDrops;
        $car->certifiedPreOwned = $requestData->certifiedPreOwned;
        $car->description = $requestData->description;
        $car->features = !empty($requestData->features) ? new JsonExpression($requestData->features) : null;
        $car->carfaxUrl = $requestData->carfaxUrl;
        $car->contactName = $requestData->contactName;
        $car->phone = $requestData->phone;

        if ($isClient) {
            $car->contactName = $requestData->contactName;
            $car->phone = $requestData->phone;
            $car->address = $requestData->address;
            $car->province = $requestData->province;
            $car->postalCode = $requestData->postalCode;
            $car->keepLocationPrivate = $requestData->keepLocationPrivate;
        }

        $car->save();

        if ($isClient) {
            $car = $geoService->setCarGeoData($car->id);
        }

        return $car;
    }

    protected function assignFilesToCarFromArray(
        array $requestData,
        bool $isClient,
        array $files,
        CurrentUser $currentUser
    ): ?object {
        $requestData = (object)$requestData;
        $carId = $requestData->carId;

        if ($files) {
            $car = $this->findById($carId);

            foreach ($files as $file) {
                $this->uploadMedia($car, $file, creatorId: $currentUser->getId());
            }

            $this->setCarMainMedia($carId);
        }

        $car = $this->getCarMedias($requestData->carId);

        return $car;
    }

    protected function deleteCarMediaFromArray(
        array $requestData,
        bool $isClient,
        LoggerInterface $logger,
    ): ?object {
        $requestData = (object)$requestData;
        $carMediaModel = $this->findCarMedia($requestData->mediaId, $requestData->carId);
        $carPath = $this->getCarPath($requestData->carId);

        if ($carMediaModel) {
            $filesAttributes = ["baseUrl", "catalogThumbnailUrl", "galleryThumbnailUrl", "videoPreviewUrl"];

            foreach ($filesAttributes as $fileAttribute) {
                if ($carMediaModel->{$fileAttribute}) {
                    $fileName = pathinfo($carMediaModel->{$fileAttribute}, PATHINFO_BASENAME);

                    try {
                        unlink("{$carPath}/{$fileName}");
                    } catch (\Exception $e) {
                    }
                }
            }

            CarMediaModel::deleteAllRecords(["carId" => $requestData->carId, "id" => $requestData->mediaId]);
        }

        $this->setCarMainMedia($requestData->carId);
        $car = $this->getCarMedias($requestData->carId);

        return $car;
    }

    protected function setMediaMainFromArray(
        array $requestData,
        bool $isClient,
    ): ?object {
        $requestData = (object)$requestData;
        $carMediaModel = $this->findCarMedia($requestData->mediaId, $requestData->carId);
        $firstCarMediaModel = $this->findFirstCarMedia($requestData->carId);
        $tmp = $carMediaModel->order;
        $carMediaModel->updateAttributes(['order' => $firstCarMediaModel->order]);
        $firstCarMediaModel->updateAttributes(['order' => $tmp]);
        $this->setCarMainMedia($requestData->carId);
        $car = $this->getCarMedias($requestData->carId);

        return $car;
    }

    protected function sortCarMediaFromArray(
        array $requestData,
        bool $isClient,
    ): ?object {
        $requestData = (object)$requestData;
        $ids = explode(',', $requestData->ids);

        $carMedias = $this->findCarMedias($requestData->carId);

        foreach ($ids as $order => $id) {
            foreach ($carMedias as $carMedia) {
                if ($carMedia->id == $id) {
                    $carMedia->order = $order;
                    $carMedia->save();
                }
            }
        }

        $this->setCarMainMedia($requestData->carId);
        $car = $this->getCarMedias($requestData->carId);

        return $car;
    }




    protected function generatePublicId(int $length = 12): string
    {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $charactersLength = strlen($characters);
        $publicId = "";

        for ($i = 0; $i < $length; $i++) {
            $publicId .= $characters[rand(0, $charactersLength - 1)];
        }

        return $publicId;
    }

    protected function uploadMedia(CarModel $car, UploadedFileInterface $file, bool $isMain = false, ?int $creatorId = null): CarMediaModel
    {
        $carPath = $this->getCarPath($car->id);
        $makeName = str_replace(" ", "_", $car->getMake()->name ?? "NoMake");
        $modelName = str_replace(" ", "_", $car->getModel()->name ?? "NoModel");
        $time = hrtime(true);
        $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
        $fileName = "car-{$makeName}-{$modelName}-{$time}.{$extension}";
        $filePath = "{$carPath}/{$fileName}";
        $file->moveTo("{$filePath}");
        $mimeType = $file->getClientMediaType();
        $fileSize = $file->getSize();

        $catalogThumbnailFileName = $galleryThumbnailFileName = $videoPreviewFileName = null;

        if ($this->isFileImage($mimeType)) {
            $params = $this->config->get('params');
            $catalogThumbnailWidth = $params['uploadedFiles']['car']['catalogThumbnailWidth'];
            $catalogThumbnailHeight = $params['uploadedFiles']['car']['catalogThumbnailHeight'];
            $galleryThumbnailWidth = $params['uploadedFiles']['car']['galleryThumbnailWidth'];
            $galleryThumbnailHeight = $params['uploadedFiles']['car']['galleryThumbnailHeight'];
            $catalogThumbnailFileName = Image::resize($filePath, $mimeType, $catalogThumbnailWidth, $catalogThumbnailHeight);
            $galleryThumbnailFileName = Image::resize($filePath, $mimeType, $galleryThumbnailWidth, $galleryThumbnailHeight);
        }

        if ($this->isFileVideo($mimeType)) {
            $ffmpeg = FFMpeg::create();
            $fileNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
            $videoPreviewFileName = "{$fileNameWithoutExtension}.jpg";
            $video = $ffmpeg->open($filePath);
            $video->frame(TimeCode::fromSeconds(1))->save("{$carPath}/{$videoPreviewFileName}");
        }

        $carMediaModel = new CarMediaModel();
        $carMediaModel->isMain = $isMain;

        if ($this->isMediaForConvert($mimeType)) {
            $carMediaModel->isMain = false;
            $carMediaModel->status = CarMediaStatus::ToConvert->value;
        }

        $carMediaModel->carId = $car->id;
        $carMediaModel->baseUrl = $this->getCarFileUrl($car->id, $fileName);
        $carMediaModel->catalogThumbnailUrl = $this->getCarFileUrl($car->id, $catalogThumbnailFileName ?? null);
        $carMediaModel->galleryThumbnailUrl = $this->getCarFileUrl($car->id, $galleryThumbnailFileName ?? null);
        $carMediaModel->videoPreviewUrl = $this->getCarFileUrl($car->id, $videoPreviewFileName ?? null);
        $carMediaModel->mediaType = $mimeType;
        $carMediaModel->type = $this->isFileVideo($mimeType) ? CarMediaType::Video->value : CarMediaType::Image->value;
        $carMediaModel->orderType = $this->isFileVideo($mimeType) ? 1 : 0;
        $carMediaModel->fileSize = $fileSize;
        $carMediaModel->creatorId = $creatorId;
        $carMediaModel->save();

        $carMediaModel->updateAttributes(['order' => $carMediaModel->id]);

        return $carMediaModel;
    }

    protected function convertCarsVideos(
        Aliases $aliases
    ): int {
        $fileTarget = new FileTarget($aliases->get("@runtime/logs/convert-video.log"));
        $logger = new Logger([$fileTarget]);

        $count = 0;
        $carMediaModels = CarMediaModel::find()->where(["carMedia.status" => CarMediaStatus::ToConvert->value])->all();

        if ($carMediaModels) {
            $count = count($carMediaModels);
            $logger->info("{$count} video files will be converted");

            foreach ($carMediaModels as $carMediaModel) {
                $carPath = $this->getCarPath($carMediaModel->carId);
                $fileName = pathinfo($carMediaModel->baseUrl, PATHINFO_BASENAME);
                $filePath = "{$carPath}/{$fileName}";

                try {
                    $logger->info("Trying to convert {$filePath}");

                    // set status as Processing
                    $carMediaModel->status = CarMediaStatus::Processing->value;
                    $carMediaModel->save();

                    // convert video
                    $ffmpeg = FFMpeg::create();
                    $video = $ffmpeg->open($filePath);
                    $format = new \FFMpeg\Format\Video\X264();
                    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                    $fileName = str_replace(".{$extension}", ".mp4", $fileName);
                    $filePath = "{$carPath}/{$fileName}";
                    $video->save($format, $filePath);

                    // update media data in db and ser status as Active
                    $mimeType = "video/mp4";
                    $fileSize = filesize($filePath);
                    $carMediaModel->baseUrl = $this->getCarFileUrl($carMediaModel->carId, $fileName);
                    $carMediaModel->mediaType = $mimeType;
                    $carMediaModel->fileSize = $fileSize;
                    $carMediaModel->status = CarMediaStatus::Active->value;
                    $carMediaModel->save();

                    // set car main media if needed
                    $this->setCarMainMedia($carMediaModel->carId);

                    $logger->info("Convertion OK");
                } catch (\Exception $e) {
                    $logger->error($e);
                    $carMediaModel->status = CarMediaStatus::Failed->value;
                    $carMediaModel->save();
                }
            }
        }

        return $count;
    }

    protected function setCarMainMedias()
    {
        $carsIds = CarModel::find()->select(['id'])->column();

        foreach ($carsIds as $carId) {
            $this->setCarMainMedia($carId);
        }
    }

    protected function addCarToWishlistFromArray(
        array $requestData
    ): int {
        $requestData = (object)$requestData;
        $isExists = CarUserModel::find()->where(["carId" => $requestData->carId, "userId" => $requestData->userId])->exists();

        if (!$isExists) {
            $carUserModel = new CarUserModel();
            $carUserModel->carId =  $requestData->carId;
            $carUserModel->userId =  $requestData->userId;
            $carUserModel->save();
        }

        return $this->getCarUserCount($requestData->userId);
    }

    protected function removeCarFromWishlistFromArray(
        array $requestData
    ): int {
        $requestData = (object)$requestData;
        CarUserModel::deleteAllRecords(["carId" => $requestData->carId, "userId" => $requestData->userId]);

        return $this->getCarUserCount($requestData->userId);
    }


    protected function rebuildSessionData(
        object $car
    ): object {
        return $this->buildCarData($car);
    }

    protected function getPreviewCarDataFromArray(
        array $requestData
    ): ?object {
        return (object)$requestData;
    }

    protected function updateCarsLocationDataForDealer(
        int $dealerId,
        ConnectionInterface $db
    ) {
        $db->createCommand()->execute(
            "
                        UPDATE car, dealer
                                SET car.longitude = dealer.longitude,
                                    car.latitude = dealer.latitude,
                                    car.postalCode = dealer.postalCode,
                                    car.address = dealer.address,
                                    car.phone = dealer.phone,
                                    car.province = dealer.province
                        WHERE
                                car.dealerId = dealer.id AND
                                car.dealerId = {$dealerId}
                    "
        );
    }

    protected function deleteClientCars(
        int $userId,
        ConnectionInterface $db,
        Logger $logger
    ): bool {
        $cars = $this->carSearch->search(
            fields: ["car.id"],
            filters: ["client" => $userId]
        );
        $result = true;

        if ($cars) {
            $carsIds = array_column($cars, "id");
            $result = $this->deleteCars($carsIds, $db, $logger);
        }

        return $result;
    }

    protected function deleteCars(
        array $carsIds,
        ConnectionInterface $db,
        Logger $logger
    ): bool {
        $result = true;
        $transaction = $db->beginTransaction();

        try {
            CarMediaModel::deleteAllRecords(["carId" => $carsIds]);
            CarUserModel::deleteAllRecords(["carId" => $carsIds]);
            CarModel::deleteAllRecords(['id' => $carsIds]);
            $transaction->commit();
        } catch (\Throwable $e) {
            $result = false;
            $transaction->rollBack();
            $logger->error($e);
        }

        if ($result) {
            foreach ($carsIds as $carId) {
                $carPath = $this->getCarPath($carId);
                FileHelper::removeDirectory($carPath);
            }
        }

        return $result;
    }





    private function isMediaForConvert(string $mimeType): bool
    {
        return in_array($mimeType, ["video/x-msvideo", "video/avi", "video/divx", "video/quicktime", "video/x-ms-wmv", "video/ogg", "video/x-flv", "application/octet-stream"]);
    }

    private function getCarFileFolder(int $carId): string
    {
        $folderNumber = ceil($carId / 3000.0); // max 3000 cars per folder

        return "c{$folderNumber}";
    }

    private function getCarPath(int $carId): string
    {
        $fileFolder = $this->getCarFileFolder($carId);
        $path = $this->aliases->get("@uploads/cars/{$fileFolder}");
        FileHelper::ensureDirectory($path);
        $path = $this->aliases->get("@uploads/cars/{$fileFolder}/{$carId}");
        FileHelper::ensureDirectory($path);

        return $path;
    }

    private function getCarFileUrl(int $carId, ?string $fileName): ?string
    {
        if (!$fileName) {
            return null;
        }

        $fileFolder = $this->getCarFileFolder($carId);

        return "/uploads/cars/{$fileFolder}/{$carId}/{$fileName}";
    }

    private function getCarDefaultImage(): string
    {
        return CarModel::DEFAULT_IMAGE;
    }

    private function getDealerDefaultImage(): string
    {
        return DealerModel::DEFAULT_IMAGE;
    }

    private function setCarMainMedia(int $carId)
    {
        $firstCarMedia = $this->findFirstCarMedia($carId);

        if ($firstCarMedia) {
            $firstCarMediaId = $firstCarMedia->id;
            CarMediaModel::updateAllRecords(['isMain' => 0], ['isMain' => 1, 'carId' => $carId]);
            CarMediaModel::updateAllRecords(['isMain' => 1], ['id' => $firstCarMediaId]);
        }
    }

    private function hydrateToCatalogCard(
        CarModel $carModel
    ): object {
        $car = $this->hydrateModelToObject($carModel);
        $car->statusName = CarStatus::tryFrom($carModel->status)?->title($this->translator);
        $car->makeName = $carModel->getMake()?->name;
        $car->modelName = $carModel->getModel()?->name;
        $car->mileageName = $car->mileage . " " . $this->translator->translate("Km");
        $car->bodyTypeName = BodyType::tryFrom($carModel->bodyType)?->title($this->translator);
        $car->transmissionName = Transmission::tryFrom($carModel->transmission)?->title($this->translator);
        $car->drivetrainName = Drivetrain::tryFrom($carModel->drivetrain)?->title($this->translator);
        $car->fuelTypeName = FuelType::tryFrom($carModel->fuelType)?->title($this->translator);
        $car->engineName = $carModel->engine ? $carModel->engine . " " . $this->translator->translate("L") : null;
        $car->vehicleTypeName = VehicleType::tryFrom($carModel->vehicleType)?->title($this->translator);
        $car->featuresNames = array_map(fn($feature) => Feature::tryFrom($feature)?->title($this->translator), $carModel->features ?? []);
        $car->price = $car->price ? FormatHelper::formatMoney($this->config, $car->price ?? 0) : null;

        // medias
        $mainCarMediaModel = $carModel->getCarMediaMain();
        $car->hasMedia = !empty($mainCarMediaModel?->baseUrl);
        $car->mediaMain = $this->getCarMediaMainObject($mainCarMediaModel);

        // urls
        $car->clientViewUrl = $this->urlGenerator->generateAbsolute('client.viewCar', ['publicId' => $carModel->publicId]);
        $car->clientEditUrl = $this->urlGenerator->generateAbsolute('client.editCar', ['publicId' => $carModel->publicId]);
        $car->dealerEditUrl = $this->urlGenerator->generateAbsolute('dealer.editCar', ['publicId' => $carModel->publicId]);
        $car->dealerViewUrl = $this->urlGenerator->generateAbsolute('dealer.viewCar', ['publicId' => $carModel->publicId]);

        // wishlist
        $car->isCarSaved = isset($car->isCarSaved) ? (int)$car->isCarSaved : 0;

        return $car;
    }

    private function hydrateToCarCardForEdit(
        CarModel $carModel,
    ): object {
        $car = $this->hydrateModelToObject($carModel);
        $car->makeName = $carModel->getMake()?->name;
        $car->modelName = $carModel->getModel()?->name;

        $car = $this->buildCarData($car);

        // medias
        $car = $this->getMediaProperties($car, $carModel);

        // dealer info
        $dealer = $this->getDealerObject($carModel);
        $car->dealerInfo = $dealer;

        // client info
        $car->email = $carModel->clientId ? $carModel->getClient()->email : '';

        return $car;
    }

    private function hydrateToCarCardForView(
        CarModel $carModel
    ): object {
        $car = $this->hydrateModelToObject($carModel);
        $car->makeName = $carModel->getMake()?->name;
        $car->modelName = $carModel->getModel()?->name;

        $car = $this->buildCarData($car);

        // medias
        $car = $this->getMediaProperties($car, $carModel);

        // dealer info
        $dealer = $this->getDealerObject($carModel);
        $car->dealerInfo = $dealer;

        // wishlist
        $car->isCarSaved = isset($car->isCarSaved) ? (int)$car->isCarSaved : 0;

        return $car;
    }

    private function buildCarData(
        object $car
    ): object {
        $car->statusName = CarStatus::tryFrom($car->status)?->title($this->translator);
        $car->conditionName = Condition::tryFrom($car->condition)?->title($this->translator);
        $car->mileageName = $car->mileage . " " . $this->translator->translate("Km");
        $car->bodyTypeName = BodyType::tryFrom($car->bodyType)?->title($this->translator);
        $car->transmissionName = Transmission::tryFrom($car->transmission)?->title($this->translator);
        $car->drivetrainName = Drivetrain::tryFrom($car->drivetrain)?->title($this->translator);
        $car->fuelTypeName = FuelType::tryFrom($car->fuelType)?->title($this->translator);
        $car->engineName = $car->engine ? $car->engine . " " . $this->translator->translate("L") : $this->translator->translate("Unknown");
        $car->engineTypeName = !empty($car->engineType) ? $car->engineType : $this->translator->translate("Unknown");
        $car->vehicleTypeName = VehicleType::tryFrom($car->vehicleType)?->title($this->translator);
        $car->bedSizeName = BedSize::tryFrom($car->bedSize)?->title($this->translator);
        $car->cabinSizeName = CabinSize::tryFrom($car->cabinSize)?->title($this->translator);
        $car->cabinSizeName =  $car->cabinSizeName == "Unknown" ? null : $car->cabinSizeName;
        $car->intColorName = IntColor::tryFrom($car->intColor)?->title($this->translator);
        $car->intColorName =  $car->intColorName == "Unknown" ? null : $car->intColorName;
        $car->extColorName = ExtColor::tryFrom($car->extColor)?->title($this->translator);
        $car->extColorName =  $car->extColorName == "Unknown" ? null : $car->extColorName;
        $car->safetyRatingName = SafetyRating::tryFrom($car->safetyRating)?->title($this->translator);
        $car->fuelEconomyName = $car->fuelEconomy ? $car->fuelEconomy . " " . $this->translator->translate("L/100Km") : $this->translator->translate("Unknown");
        $car->co2Name = $car->co2 ? $car->co2 . " " . $this->translator->translate("g/Km") : $this->translator->translate("Unknown");
        $car->evBatteryRangeName = $car->evBatteryRange ? $car->evBatteryRange . " " . $this->translator->translate("Km") : null;
        $car->evBatteryTimeName = $car->evBatteryTime ? $car->evBatteryTime . " " . $this->translator->translate("H") : null;
        $car->featuresNames = array_map(fn($feature) => Feature::tryFrom($feature)?->title($this->translator), $car->features ?? []);
        $car->features = $car->features ?? [];
        $car->certifiedPreOwned = $car->certifiedPreOwned ? 1 : 0;
        $car->published = $car->published ? FormatHelper::formatDateShort($car->published, $this->config) : null;
        $car->priceDrops = $car->priceDrops ? 1 : 0;
        $car->priceFormatted = $car->price ? FormatHelper::formatMoney($this->config, $car->price) : null;
        $car->oldPrice = null;
        $car->priceStatusName = PriceStatus::Fairdeal->title($this->translator);
        $car->priceStatusColor = PriceStatus::Fairdeal->color();

        // description
        $wordsCountForTruncate = 400;
        $text = strip_tags($car->description);
        $words = StringHelper::countWords($text);
        $car->descriptionShort = $words < $wordsCountForTruncate
            ? $car->description
            : DataHelper::truncateHtml($car->description, $wordsCountForTruncate, $this->aliases, '<p style="display:inline;">...</p>');
        $car->isDescriptionTruncated = $words >= $wordsCountForTruncate;

        $car->keepLocationPrivate = $car->keepLocationPrivate ? 1 : 0;
        $car->displayedAddress = $this->getDisplayedAddress($car);
        $car->displayedPublicAddress = $this->getDisplayedPublicAddress($car);

        return $car;
    }

    private function hydrateToCarMediasOnly(
        CarModel $carModel,
    ): object {
        $car = $this->hydrateModelToObject($carModel);
        $car = $this->getMediaProperties($car, $carModel);

        return $car;
    }

    private function getCarMediaMainObject(
        ?CarMediaModel $mainCarMediaModel
    ): object {
        return (object)[
            'id' => $mainCarMediaModel?->id,
            'carId' => $mainCarMediaModel?->carId,
            'baseUrl' => $mainCarMediaModel?->baseUrl ?? $this->getCarDefaultImage(),
            'catalogUrl' => $this->isFileVideo($mainCarMediaModel?->mediaType)
                ? $mainCarMediaModel?->videoPreviewUrl
                : $mainCarMediaModel?->catalogThumbnailUrl ?? $this->getCarDefaultImage(),
            'videoPreviewUrl' => $mainCarMediaModel?->videoPreviewUrl,
            'title' => $mainCarMediaModel?->title,
            'mediaType' => $mainCarMediaModel?->mediaType,
            'type' => $mainCarMediaModel?->type,
            'fileSize' => $mainCarMediaModel?->fileSize,
            'isVideo' => $this->isFileVideo($mainCarMediaModel?->mediaType),
            'status' => $mainCarMediaModel?->status,
            'order' => $mainCarMediaModel?->order,
        ];
    }

    private function getCarMediasObjects(
        array $carMedias
    ): array {
        return array_map(fn($carMedia) => (object)[
            'id' => $carMedia->id,
            'carId' => $carMedia->carId,
            'isMain' => $carMedia->isMain,
            'baseUrl' => $carMedia?->baseUrl,
            'galleryUrl' => $carMedia?->galleryThumbnailUrl,
            'catalogUrl' => $carMedia?->catalogThumbnailUrl,
            'videoPreviewUrl' => $carMedia?->videoPreviewUrl,
            'title' => $carMedia->title,
            'mediaType' => $carMedia->mediaType,
            'type' => $carMedia->type,
            'fileSize' => $carMedia->fileSize,
            'isVideo' => $this->isFileVideo($carMedia?->mediaType),
            'status' => $carMedia->status,
            'order' => $carMedia->order,
        ], $carMedias);
    }

    private function getDealerObject(
        CarModel $carModel
    ): object {
        $dealer = $carModel->getDealer();
        $dealerProvince = Province::tryFrom($dealer?->province)?->title($this->translator);

        $dealerObject = new \stdClass();
        $dealerObject->name = $dealer?->name;
        $dealerObject->address = $dealer?->address;
        $dealerObject->province = $dealer?->province;
        $dealerObject->phone = $dealer?->phone;
        $dealerObject->postalCode = $dealer?->postalCode;
        $dealerObject->website = $dealer?->website;
        $dealerObject->logo = $dealer?->logo ?? $this->getDealerDefaultImage();
        $dealerObject->status = $dealer?->status;
        $dealerObject->displayedAddress = $this->getDisplayedAddress($dealerObject);

        return $dealerObject;
    }

    private function getMediaProperties(
        object $car,
        CarModel $carModel
    ): object {
        $mainCarMediaModel = $carModel->getCarMediaMain();
        $car->mediaMain = $this->getCarMediaMainObject($mainCarMediaModel);
        $car->mediasAll = $this->getCarMediasObjects($carModel->getCarMedias());
        $car->mediasActive = array_values(array_filter($car->mediasAll, function ($carMedia) {
            return $carMedia->status == CarMediaStatus::Active->value;
        }));
        $car->images = array_values(array_filter($car->mediasAll, function ($carMedia) {
            return $carMedia->type == CarMediaType::Image->value;
        }));
        $car->videosAll = array_values(array_filter($car->mediasAll, function ($carMedia) {
            return $carMedia->type == CarMediaType::Video->value;
        }));
        $car->videosActive = array_values(array_filter($car->mediasActive, function ($carMedia) {
            return $carMedia->type == CarMediaType::Video->value;
        }));
        $car->hasMedias = count($car->mediasActive) > 0;

        return $car;
    }

    private function getDisplayedAddress(
        object $obj
    ): string {
        $addressData = [];

        if ($obj->address) {
            $addressData[] = $obj->address;
        }

        if ($obj->province || $obj->postalCode) {
            $postalCode = $obj->postalCode ? substr_replace($obj->postalCode, ' ', 3, 0) : '';
            $addressData[] = trim("{$obj->province} {$postalCode}");
        }

        return implode(', ', $addressData);
    }

    private function getDisplayedPublicAddress(
        object $obj
    ): string {
        $addressData = [];

        if ($obj->address && $obj->keepLocationPrivate == 0) {
            $addressData[] = $obj->address;
        }

        if ($obj->province || $obj->postalCode) {
            $postalCode = $obj->postalCode ? substr_replace($obj->postalCode, ' ', 3, 0) : '';
            $addressData[] = trim("{$obj->province} {$postalCode}");
        }

        return implode(', ', $addressData);
    }
}
