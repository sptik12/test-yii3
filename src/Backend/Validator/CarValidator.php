<?php

namespace App\Backend\Validator;

use App\Backend\Exception\Http\NotFoundException;
use App\Backend\Model\Car\CarSearchModel;
use App\Backend\Model\Car\Condition;
use App\Backend\Model\Car\CarStatus;
use App\Backend\Model\Car\BodyType;
use App\Backend\Model\Car\Transmission;
use App\Backend\Model\Car\FuelType;
use App\Backend\Model\Car\Drivetrain;
use App\Backend\Model\Car\ExtColor;
use App\Backend\Model\Car\IntColor;
use App\Backend\Model\Car\CabinSize;
use App\Backend\Model\Car\BedSize;
use App\Backend\Model\Car\SafetyRating;
use App\Backend\Model\Car\Feature;
use App\Backend\Model\Car\VehicleType;
use App\Backend\Model\Dealer\DealerStatus;
use App\Backend\Model\Province;
use App\Backend\Service\CarService;
use App\Backend\Service\GeoService;
use App\Backend\Service\UserService;
use App\Backend\Service\DealerService;
use Yiisoft\User\CurrentUser;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule\InEnum;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Number;
use Yiisoft\Validator\Rule\Regex;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rule\Each;
use Yiisoft\Validator\Rule\In;
use Yiisoft\Validator\Rule\StringValue;
use Yiisoft\Validator\Rule\Url;
use Yiisoft\Validator\ValidationContext;
use Yiisoft\Translator\TranslatorInterface;
use App\Backend\Exception\Http\ForbiddenException;

final class CarValidator extends AbstractValidator
{
    private $validatedFields = [
        "vinCode",
        "condition",
        "mileage",
        "makeId",
        "modelId",
        "year",
        "engineType",
        "engine",
        "vehicleType",
        "evBatteryRange",
        "evBatteryTime",
        "fuelType",
        "fuelEconomy",
        "co2",
        "drivetrain",
        "transmission",
        "safetyRating",
        "certifiedPreOwned",
        "bodyType",
        "extColor",
        "doors",
        "cabinSize",
        "intColor",
        "bedSize",
        "seats",
        "features",
        "description",
        "price",
        "status",
        "cylinders",
        "trim",
        "madeIn",
        "priceDrops",
        "carfaxUrl"
    ];

    private $clientValidatedFields = [
        "contactName",
        "phone",
        "address",
        "province",
        "postalCode",
        "keepLocationPrivate"
    ];

    public function __construct(
        protected TranslatorInterface $translator
    ) {
        parent::__construct(translator: $translator);
    }

    public function getCarDataByVinCode(
        array $requestData
    ): array {
        $requestData = $this->fetchRequired($requestData, ["vinCode"]);

        /* Check general rules */
        $this->validateData($requestData, [
            'vinCode' => [
                new Required(),
                new Regex(
                    pattern: "/^[0-9A-Z]{17}$/",
                    message: $this->translator->translate("The VIN number contains 17 characters, including digits and capital letters"),
                    skipOnError: true,
                )
            ],
        ]);

        return compact("requestData");
    }

    public function getCarForClientEdit(
        string $publicId,
        CarService $carService,
        UserService $userService,
        CurrentUser $currentUser
    ): array {
        /* Check general rules */
        $this->validateData(["publicId" => $publicId], [
            'publicId' => [
                new StringValue()
            ],
        ]);

        /* Check specific rules */
        $carModel = $this->validateCarExistsByPublicId($publicId, $carService);

        if ($carModel->clientId != $currentUser->getId()) {
            throw new ForbiddenException($this->translator->translate("You have no access to this car"));
        }

        return compact("publicId");
    }

    public function getCarForDealerEdit(
        string $publicId,
        CarService $carService,
        UserService $userService,
        CurrentUser $currentUser
    ): array {
        /* Check general rules */
        $this->validateData(["publicId" => $publicId], [
            'publicId' => [
                new StringValue()
            ],
        ]);

        /* Check specific rules */
        $carModel = $this->validateCarExistsByPublicId($publicId, $carService);

        if ($userService->isDealerOnly($currentUser->getId())) {
            if ($carModel->dealerId != $currentUser->getIdentity()->currentDealerId) {
                throw new ForbiddenException($this->translator->translate("You have no access to this car"));
            }
        }

        return compact("publicId");
    }

    public function getCarForClientView(
        string $publicId,
        CarService $carService,
        DealerService $dealerService,
        CurrentUser $currentUser
    ): array {
        /* Check general rules */
        $this->validateData(["publicId" => $publicId], [
            'publicId' => [
                new StringValue()
            ],
        ]);

        /* Check specific rules */
        $carModel = $this->validateCarExistsByPublicId($publicId, $carService);

        if ($carModel->clientId != $currentUser->getId() && $carModel->status != CarStatus::Published->value) {
            throw new ForbiddenException($this->translator->translate("This car is not published"));
        }

        if ($carModel->dealerId) {
            $dealerModel = $this->validateDealerExists($carModel->dealerId, $dealerService);

            if ($dealerModel->status != DealerStatus::Active->value) {
                throw new ForbiddenException($this->translator->translate("Cars of this dealer are not accessed for public view"));
            }
        }

        return compact("publicId");
    }

    public function getCarForDealerView(
        string $publicId,
        CarService $carService
    ): array {
        /* Check general rules */
        $this->validateData(["publicId" => $publicId], [
            'publicId' => [
                new StringValue()
            ],
        ]);

        /* Check specific rules */
        $this->validateCarExistsByPublicId($publicId, $carService);

        return compact("publicId");
    }

    public function createCarFromArray(
        array $requestData,
        bool $isClient,
        DealerService $dealerService,
        CarService $carService,
        ConfigInterface $config,
        CurrentUser $currentUser,
    ): array {
        $requestData = $this->fetchOptional($requestData, array_merge($this->validatedFields, ['status']));
        $requestData["priceDrops"] = 0;

        if (!$requestData["status"]) {
            $requestData["status"] = CarStatus::Draft->value;
        }

        /* Check general rules */
        $this->validateCarProperties($requestData);

        /* Check specific rules */
        if ($isClient) {
            $requestData['dealerId'] = null;
            $requestData['clientId'] = $currentUser->getIdentity()->getId();

            if ($requestData["vinCode"]) {
                $this->validateCarVinCodeForClient($requestData["vinCode"], $requestData['clientId'], null, $carService);
            }

            $this->validateMaxNumberOfClientCars($requestData['clientId'], $carService, $config);
        } else {
            $dealerModel = $this->validateDealerExists($currentUser->getIdentity()->currentDealerId, $dealerService);
            $requestData['dealerId'] = $dealerModel->id;
            $requestData['clientId'] = null;

            if ($requestData["vinCode"]) {
                $this->validateCarVinCodeForDealer($requestData["vinCode"], $requestData['dealerId'], null, $carService);
            }
        }
        // $requestData = $this->validateCarForDealerOrClient($requestData, $isClient, $dealerService, $carService, $currentUser);

        return compact("requestData", "isClient");
    }

    public function createEmptyCar(
        array $requestData,
        bool $isClient,
        DealerService $dealerService,
        CarService $carService,
        ConfigInterface $config,
        CurrentUser $currentUser,
    ): array {
        $requestData = $this->fetchOptional($requestData, $this->validatedFields);
        $requestData["condition"] = Condition::Used->value;
        $requestData["safetyRating"] = SafetyRating::NoRating->value;
        $requestData["extColor"] = ExtColor::Unknown->value;
        $requestData["cabinSize"] = CabinSize::Unknown->value;
        $requestData["intColor"] = IntColor::Unknown->value;
        $requestData["bedSize"] = BedSize::Unknown->value;
        $requestData["certifiedPreOwned"] = 0;
        $requestData["price"] = 0;
        $requestData["priceDrops"] = 0;
        $requestData["status"] = CarStatus::Draft->value;

        /* Check specific rules */
        if ($isClient) {
            $requestData['dealerId'] = null;
            $requestData['clientId'] = $currentUser->getIdentity()->getId();

            $this->validateMaxNumberOfClientCars($requestData['clientId'], $carService, $config);
        } else {
            $dealerModel = $this->validateDealerExists($currentUser->getIdentity()->currentDealerId, $dealerService);
            $requestData['dealerId'] = $dealerModel->id;
            $requestData['clientId'] = null;
        }

        return compact("requestData", "isClient");
    }

    public function saveDraftCarFromArray(
        array $requestData,
        bool $isClient,
        CarService $carService,
        CurrentUser $currentUser,
        GeoService $geoService
    ): array {
        $requestData = $this->fetchOptional(
            $requestData,
            array_merge($this->validatedFields, ['publicId'], $this->clientValidatedFields)
        );
        $requestData["certifiedPreOwned"] = !empty($requestData["certifiedPreOwned"]) ? 1 : 0;
        $requestData["priceDrops"] = 0;
        $requestData["status"] = CarStatus::Draft->value;
        $requestData = $this->setFieldsToNullIfEmpty($requestData, [
            'condition', 'makeId', 'modelId', 'year', 'vehicleType',
            'fuelType', 'drivetrain', 'transmission', 'bodyType'
        ]);

        /* Check general rules */
        $this->validateCarProperties($requestData);

        /* Check specific rules */
        $carModel = $this->validateCarExistsByPublicId($requestData["publicId"], $carService);

        if ($isClient) {
            $this->validateCarClientId($carModel->clientId, $currentUser);
            $requestData = $this->validateClientProperties($requestData, $geoService);

            if ($requestData["vinCode"]) {
                $this->validateCarVinCodeForClient($requestData["vinCode"], $carModel->clientId, $carModel->id, $carService);
            }
            $requestData['dealerId'] = null;
            $requestData['clientId'] = $carModel->clientId;
        } else {
            $this->validateCarDealerId($carModel->dealerId, $currentUser);

            if ($requestData["vinCode"]) {
                $this->validateCarVinCodeForDealer($requestData["vinCode"], $carModel->dealerId, $carModel->id, $carService);
            }
            $requestData['dealerId'] = $carModel->dealerId;
            $requestData['clientId'] = null;
        }

        return compact("requestData", "isClient");
    }

    public function getPreviewCarDataFromArray(
        array $requestData
    ): array {
        $requestData = $this->fetchOptional(
            $requestData,
            array_merge($this->validatedFields, ['publicId'], $this->clientValidatedFields)
        );
        $requestData["certifiedPreOwned"] = !empty($requestData["certifiedPreOwned"]) ? 1 : 0;
        $requestData["priceDrops"] = 0;
        $requestData["status"] = CarStatus::Draft->value;
        $requestData = $this->setFieldsToNullIfEmpty($requestData, [
            'condition', 'makeId', 'modelId', 'year', 'vehicleType',
            'fuelType', 'drivetrain', 'transmission', 'bodyType'
        ]);

        return compact("requestData");
    }

    public function publishCarFromArray(
        array $requestData,
        bool $isClient,
        DealerService $dealerService,
        CarService $carService,
        CurrentUser $currentUser,
        GeoService $geoService
    ): array {
        $requestData = $this->fetchOptional(
            $requestData,
            array_merge($this->validatedFields, ['publicId'], $this->clientValidatedFields)
        );
        $requestData["priceDrops"] = 0;
        $requestData["status"] = CarStatus::Published->value;

        /* Check general rules */
        $this->validateCarProperties($requestData);

        /* Check specific rules */
        $carModel = $this->validateCarExistsByPublicId($requestData["publicId"], $carService);

        if ($isClient) {
            $this->validateCarClientId($carModel->clientId, $currentUser);
            $requestData = $this->validateClientProperties($requestData, $geoService);

            if ($requestData["vinCode"]) {
                $this->validateCarVinCodeForClient($requestData["vinCode"], $carModel->clientId, $carModel->id, $carService);
            }

            $requestData['dealerId'] = null;
            $requestData['clientId'] = $carModel->clientId;
        } else {
            $this->validateCarDealerId($carModel->dealerId, $currentUser);

            if ($requestData["vinCode"]) {
                $this->validateCarVinCodeForDealer($requestData["vinCode"], $carModel->dealerId, $carModel->id, $carService);
            }

            $dealerModel = $this->validateDealerExists($carModel->dealerId, $dealerService);

            if ($dealerModel->status != DealerStatus::Active->value) {
                throw new ForbiddenException($this->translator->translate("Your application is not approved or disabled"));
            }

            $requestData['dealerId'] = $carModel->dealerId;
            $requestData['clientId'] = null;
        }


        return compact("requestData", "isClient");
    }

    public function assignFilesToCarFromArray(
        array $requestData,
        bool $isClient,
        array $files,
        CarService $carService,
        ConfigInterface $config,
        CurrentUser $currentUser
    ): array {
        $requestData = $this->fetchRequired($requestData, ['carId']);

        // upload params
        $params = $config->get('params');
        $allowedMimeTypes = $params['uploadedFiles']['car']['allowedMimeTypes'];
        $maxNumberOfUploadedFiles = $params['uploadedFiles']['car']['maxNumberOfUploadedFiles'];
        $maxNumberOfAssignedFiles = $isClient
            ? $params['uploadedFiles']['car']['maxNumberOfAssignedClientFiles']
            : $params['uploadedFiles']['car']['maxNumberOfAssignedDealerFiles'];
        $maxUploadFileSizeMb = $params['uploadedFiles']['car']['maxUploadFileSize'];

        /* Check general rules */
        $this->validateData($requestData, [
            'carId' => [
                new Integer()
            ],
        ]);

        $carId = $requestData["carId"];

        if ($this->validateThatNoFilesUploaded($files)) {
            return ["carId" => $carId, "files" => []];
        }

        $this->validateUploadedFiles($files, $maxNumberOfUploadedFiles, $maxUploadFileSizeMb, $allowedMimeTypes);

        /* Check specific rules */
        $carModel = $this->validateCarExists($requestData["carId"], $carService);

        if ($isClient) {
            $this->validateCarClientId($carModel->clientId, $currentUser);
        } else {
            $this->validateCarDealerId($carModel->dealerId, $currentUser);
        }

        $carMedias = $carModel->getCarMedias();

        if (count($carMedias) + count($files) > $maxNumberOfAssignedFiles) {
            $this->throwValidationException(
                "files",
                $this->translator->translate(
                    "Total number of assigned files cannot be more than {maxNumberOfAssignedFiles}",
                    ["maxNumberOfAssignedFiles" => $maxNumberOfAssignedFiles]
                )
            );
        }

        return compact("requestData", "isClient", "files");
    }

    public function deleteCarMediaFromArray(
        array $requestData,
        bool $isClient,
        CarService $carService,
        CurrentUser $currentUser
    ): array {
        $requestData = $this->fetchRequired($requestData, [
            "carId",
            "mediaId"
        ]);

        /* Check general rules */
        $this->validateData($requestData, [
            'carId' => [
                new Integer()
            ],
            'mediaId' => [
                new Integer()
            ],
        ]);

        /* Check specific rules */
        $carModel = $this->validateCarExists($requestData["carId"], $carService);

        if ($isClient) {
            $this->validateCarClientId($carModel->clientId, $currentUser);
        } else {
            $this->validateCarDealerId($carModel->dealerId, $currentUser);
        }

        return compact("requestData", "isClient");
    }

    public function setMediaMainFromArray(
        array $requestData,
        bool $isClient,
        CarService $carService,
        CurrentUser $currentUser
    ): array {
        $requestData = $this->fetchRequired($requestData, [
            "carId",
            "mediaId"
        ]);

        /* Check general rules */
        $this->validateData($requestData, [
            'carId' => [
                new Integer()
            ],
            'mediaId' => [
                new Integer()
            ],
        ]);

        /* Check specific rules */
        $carModel = $this->validateCarExists($requestData["carId"], $carService);
        $this->validateCarMediaExists($requestData["mediaId"], $requestData["carId"], $carService);

        if ($isClient) {
            $this->validateCarClientId($carModel->clientId, $currentUser);
        } else {
            $this->validateCarDealerId($carModel->dealerId, $currentUser);
        }

        return compact("requestData", "isClient");
    }

    public function sortCarMediaFromArray(
        array $requestData,
        bool $isClient,
        CarService $carService,
        CurrentUser $currentUser,
    ): array {
        $requestData = $this->fetchRequired($requestData, [
            "carId",
            "ids",
        ]);

        /* Check general rules */
        $this->validateData($requestData, [
            'carId' => [
                new Integer()
            ],
        ]);

        /* Check specific rules */
        $carModel = $this->validateCarExists($requestData["carId"], $carService);

        if ($isClient) {
            $this->validateCarClientId($carModel->clientId, $currentUser);
        } else {
            $this->validateCarDealerId($carModel->dealerId, $currentUser);
        }


        return compact("requestData", "isClient");
    }

    public function addCarToWishlistFromArray(
        array $requestData,
        CarService $carService,
        CurrentUser $currentUser
    ): array {
        $requestData = $this->fetchOptional(
            $requestData,
            [
                "carId",
            ]
        );

        $this->validateData($requestData, [
            'carId' => [
                new Required(),
                new Integer(),
            ],
        ]);

        /* Check specific rules */
        $this->validateCarExists($requestData['carId'], $carService);
        $this->validateIsLogged($currentUser);

        $requestData["userId"] = $currentUser->getId();

        return ["requestData" => $requestData];
    }

    public function removeCarFromWishlistFromArray(
        array $requestData,
        CarService $carService,
        CurrentUser $currentUser
    ): array {
        $requestData = $this->fetchOptional(
            $requestData,
            [
                "carId",
            ]
        );

        $this->validateData($requestData, [
            'carId' => [
                new Required(),
                new Integer(),
            ],
        ]);

        /* Check specific rules */
        $this->validateCarExists($requestData['carId'], $carService);
        $this->validateIsLogged($currentUser);

        $carUserModel = $carService->findCarUser($requestData['carId'], $currentUser->getId());

        if ($carUserModel && $carUserModel->userId != $currentUser->getId()) {
            throw new ForbiddenException('id', $this->translator->translate("You have no rights to remove this car from wishlist of other user "));
        }

        $requestData["userId"] = $currentUser->getId();

        return ["requestData" => $requestData];
    }


    public function searchCarsForClient(
        array $filters,
        string $sort,
        string $sortOrder,
        int $page,
        int $perPage,
        DealerService $dealerService,
    ): array {
        $data = compact("sort", "sortOrder", "page", "perPage");

        /* Check general rules */
        $this->validateData($data, [
            'sort' => [
                new In(
                    values: ["car.published", "car.mileage", "car.year", "car.price", "distance"],
                    message: $this->translator->translate("Invalid sort parameter")
                )
            ],
            'sortOrder' => [
                new In(
                    values: ["desc", "asc"],
                    message: $this->translator->translate("Invalid sort order parameter")
                )
            ],
            'page' => new Integer(min: 1),
            'perPage' => new Integer(min: 1),
        ]);

        if ($sort == "distance" && empty($filters["distance"]) && empty($filters["province"])) {
            $this->throwValidationException(null, $this->translator->translate("To sort cars by distance you should specify Postal Code in filters"));
        }

        if (array_key_exists('dealer', $filters)) {
            $dealerId = (int)$filters["dealer"];
            $dealer = $dealerService->findById($dealerId);

            if (!$dealer) {
                throw new NotFoundException($this->translator->translate("Sorry, we cannot find this dealer"));
            }

            if ($dealer->status != DealerStatus::Active->value) {
                throw new ForbiddenException($this->translator->translate("Cars of this dealer are not allowed for public view"));
            }
        }

        return compact("filters", "sort", "sortOrder", "page", "perPage");
    }

    public function searchCarsForWishlist(
        array $filters,
        string $sort,
        string $sortOrder,
        int $page,
        int $perPage,
        CurrentUser $currentUser
    ): array {
        /* Check specific rules */
        if ($currentUser->isGuest()) {
            throw new ForbiddenException($this->translator->translate("You are not logged. Please login to user wishlist"));
        }

        return compact("filters", "sort", "sortOrder", "page", "perPage");
    }

    public function searchWishlistUrls(
        array $filters,
        string $sort,
        string $sortOrder,
        int $page,
        int $perPage,
        CurrentUser $currentUser
    ): array {
        /* Check specific rules */
        if ($currentUser->isGuest()) {
            throw new ForbiddenException($this->translator->translate("You are not logged. Please login to user wishlist"));
        }

        return compact("filters", "sort", "sortOrder", "page", "perPage");
    }





    private function validateCarProperties(array $requestData): Result
    {
        return $this->validateData($requestData, [
            'status' => [
                new Required(),
                new InEnum(CarStatus::class, skipOnError: true),
            ],
            'vinCode' => [
                new Regex(
                    pattern: "/^[0-9A-Z]{17}$/",
                    message: $this->translator->translate("The VIN number contains 17 characters, including digits and capital letters"),
                    skipOnEmpty: true
                )
            ],
            'condition' => [
                new Required(when: self::isCarPublished(...), message: $this->translator->translate("Condition cannot be blank")),
                new InEnum(Condition::class, skipOnError: true, skipOnEmpty: true),
            ],
            'mileage' => [
                new Integer(min: 0, skipOnEmpty: true)
            ],
            'makeId' => [
                new Required(when: self::isCarPublished(...), message: $this->translator->translate("Make cannot be blank")),
                new Integer(skipOnError: true, skipOnEmpty: true)
            ],
            'modelId' => [
                new Required(when: self::isCarPublished(...), message: $this->translator->translate("Model cannot be blank")),
                new Integer(skipOnError: true, skipOnEmpty: true)
            ],
            'year' => [
                new Required(when: self::isCarPublished(...), message: $this->translator->translate("Year cannot be blank")),
                new Integer(min: 1990, max: date("Y"), skipOnError: true, skipOnEmpty: true)
            ],
            'engineType' => [
                new Required(when: self::isCarPublished(...), message: $this->translator->translate("Engine Type cannot be blank")),
                new Length(max: 64, skipOnError: true, skipOnEmpty: true)
            ],
            'engine' => [
                new Number(skipOnEmpty: true)
            ],
            'vehicleType' => [
                new Required(when: self::isCarPublished(...), message: $this->translator->translate("Vehicle Type cannot be blank")),
                new InEnum(VehicleType::class, skipOnError: true, skipOnEmpty: true),
            ],
            'evBatteryRange' => [
                new Integer(min: 0, skipOnEmpty: true)
            ],
            'evBatteryTime' => [
                new Integer(min: 0, skipOnEmpty: true)
            ],
            'fuelType' => [
                new Required(when: self::isCarPublished(...), message: $this->translator->translate("Fuel Type cannot be blank")),
                new InEnum(FuelType::class, skipOnError: true, skipOnEmpty: true),
            ],
            'fuelEconomy' => [
                new Number(min: 0, skipOnEmpty: true)
            ],
            'co2' => [
                new Integer(min: 0, skipOnEmpty: true)
            ],
            'drivetrain' => [
                new Required(when: self::isCarPublished(...), message: $this->translator->translate("Drivetrain cannot be blank")),
                new InEnum(Drivetrain::class, skipOnError: true, skipOnEmpty: true),
            ],
            'transmission' => [
                new Required(when: self::isCarPublished(...), message: $this->translator->translate("Transmission cannot be blank")),
                new InEnum(Transmission::class, skipOnError: true, skipOnEmpty: true),
            ],
            'safetyRating' => [
                new Required(when: self::isCarPublished(...), message: $this->translator->translate("Safety Rating cannot be blank")),
                new InEnum(SafetyRating::class, skipOnError: true, skipOnEmpty: true),
            ],
            'certifiedPreOwned' => [
                new Integer(min: 0, max: 1, skipOnEmpty: true)
            ],
            'bodyType' => [
                new Required(when: self::isCarPublished(...), message: $this->translator->translate("Body Type cannot be blank")),
                new InEnum(BodyType::class, skipOnError: true, skipOnEmpty: true),
            ],
            'extColor' => [
                new Required(when: self::isCarPublished(...), message: $this->translator->translate("Exterior cannot be blank")),
                new InEnum(ExtColor::class, skipOnError: true),
            ],
            'doors' => [
                new Required(when: self::isCarPublished(...), message: $this->translator->translate("Doors number cannot be blank")),
                new Integer(min: 1, max: 5, skipOnEmpty: true),
            ],
            'cabinSize' => [
                new Required(when: self::isCarPublished(...), message: $this->translator->translate("Cabin size cannot be blank")),
                new InEnum(CabinSize::class, skipOnError: true),
            ],
            'intColor' => [
                new Required(when: self::isCarPublished(...)),
                new InEnum(IntColor::class, skipOnError: true),
            ],
            'bedSize' => [
                new InEnum(BedSize::class, skipOnEmpty: true),
            ],
            'seats' => [
                new Required(when: self::isCarPublished(...), message: $this->translator->translate("Seats number cannot be blank")),
                new Integer(min: 1, max: 6, skipOnEmpty: true),
            ],
            'features' => [
                new Each(
                    new InEnum(Feature::class),
                    skipOnEmpty: true,
                ),
            ],
            'price' => [
                new Required(when: self::isCarPublished(...), message: $this->translator->translate("Price value cannot be blank")),
                new Number(min: 1, max: 999999999, skipOnError: true, when: self::isCarPublished(...)),
                new Number(min: 0, max: 999999999, skipOnError: true, skipOnEmpty: true, when: self::isCarNotPublished(...)),
            ],
            'cylinders' => [
                new Integer(min: 0, max: 16, skipOnEmpty: true)
            ],
            'priceDrops' => [
                new Integer(min: 0, max: 1, skipOnEmpty: true)
            ],
            'carfaxUrl' => [
                new Url(skipOnEmpty: true),
            ],
        ]);
    }

    private function validateClientProperties(array $requestData, GeoService $geoService): array
    {
        $requestData["keepLocationPrivate"] = !empty($requestData["keepLocationPrivate"]) ? 1 : 0;

        $this->validateData($requestData, [
            'contactName' => [
                new Required(when: self::isCarPublished(...)),
                $this->getUserNameValidator($this->translator->translate("Client Contact Name must contain at least 4 and at most 32 symbols. Only Latin symbols, apostrophes, and spaces are allowed."))
            ],
            'phone' => [
                $this->getPhoneValidator()
            ],
            'address' => [
                new Required(when: self::isCarPublished(...)),
                new Length(skipOnEmpty: true, skipOnError: true, min: 2, max: 64),
                new StringValue(
                    skipOnError: true
                )
            ],
            'postalCode' => [
                new Required(when: self::isCarPublished(...)),
                $this->getPostalCodeValidator()
            ],
            'province' => [
                new Required(when: self::isCarPublished(...)),
                new InEnum(Province::class, useNames: true, skipOnEmpty: true, skipOnError: true),
            ],
        ]);

        /* Check specific rules */
        $requestData = $this->validatePostalCodeForGeoData($requestData, $geoService);

        return $requestData;
    }

    private static function isCarPublished(mixed $value, ValidationContext $context): bool
    {
        return $context->getDataSet()->getPropertyValue('status') == CarStatus::Published->value;
    }

    private static function isCarNotPublished(mixed $value, ValidationContext $context): bool
    {
        return $context->getDataSet()->getPropertyValue('status') != CarStatus::Published->value;
    }

    private function validateCarVinCodeForDealer(
        string $vinCode,
        int $dealerId,
        ?int $excludeCarId,
        CarService $carService
    ): void {
        $carModel = $carService->findByVinCodeForDealer($vinCode, $dealerId, $excludeCarId);

        if ($carModel) {
            $this->throwValidationException("vinCode", $this->translator->translate("Car with this Vin Code already present in current dealer catalog"));
        }
    }

    private function validateCarVinCodeForClient(
        string $vinCode,
        int $clientId,
        ?int $excludeCarId,
        CarService $carService
    ): void {
        $carModel = $carService->findByVinCodeForClient($vinCode, $clientId, $excludeCarId);

        if ($carModel) {
            $this->throwValidationException("vinCode", $this->translator->translate("Car with this Vin Code already present in your cars catalog"));
        }
    }

    private function validateCarForDealerOrClient(
        array $requestData,
        bool $isClient,
        DealerService $dealerService,
        CarService $carService,
        CurrentUser $currentUser,
    ): array {
        if (!$isClient) {
            $dealerModel = $this->validateDealerExists($currentUser->getIdentity()->currentDealerId, $dealerService);
            $requestData['dealerId'] = $dealerModel->id;
            $requestData['clientId'] = null;

            if ($requestData["vinCode"]) {
                $this->validateCarVinCodeForDealer($requestData["vinCode"], $requestData['dealerId'], null, $carService);
            }
        } else {
            $requestData['dealerId'] = null;
            $requestData['clientId'] = $currentUser->getIdentity()->getId();

            if ($requestData["vinCode"]) {
                $this->validateCarVinCodeForClient($requestData["vinCode"], $requestData['clientId'], null, $carService);
            }
        }

        return $requestData;
    }

    private function validateMaxNumberOfClientCars(
        $clientId,
        CarService $carService,
        ConfigInterface $config,
    ) {
        $params = $config->get('params');
        $maxNumberOfClientCars = $params['settings']['maxNumberOfClientCars'];

        $carsCount = $carService->getClientCarsCount($clientId);

        if ($carsCount > $maxNumberOfClientCars) {
            $this->throwValidationException(
                "id",
                $this->translator->translate("You cannot add more than {maxNumberOfClientCars} cars", ['maxNumberOfClientCars' => $maxNumberOfClientCars])
            );
        }
    }
}
