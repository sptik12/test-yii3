<?php

namespace App\Backend\Validator;

use App\Backend\Exception\Http\NotFoundException;
use App\Backend\Model\Dealer\DealerModel;
use App\Backend\Model\Province;
use App\Backend\Service\DealerService;
use App\Backend\Service\GeoService;
use App\Backend\Service\UserService;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\User\CurrentUser;
use Yiisoft\Validator\Rule\InEnum;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Regex;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rule\StringValue;
use Yiisoft\Validator\Rule\Url;
use App\Backend\Exception\Http\ForbiddenException;

final class DealerValidator extends AbstractValidator
{
    public function __construct(
        protected DealerService $dealerService,
        protected UserService $userService,
        protected CurrentUser $currentUser,
        protected TranslatorInterface $translator
    ) {
        parent::__construct(translator: $translator);
    }

    public function getDealer(
        int $id
    ): array {
        /* Check general rules */
        $this->validateData(["id" => $id], [
            'id' => [
                new Integer()
            ],
        ]);

        /* Check specific rules */
        $this->validateDealerExists($id, $this->dealerService);

        return compact("id");
    }

    public function createDealerShipFromArray(
        array $requestData,
        GeoService $geoService
    ): array {
        $requestDataDealership = $this->fetchOptional($requestData, [
            'accountManagerId',
        ]);

        /* Check general rules */
        $this->validateData(
            $requestDataDealership,
            [
                "accountManagerId" => [
                    new Required(),
                    new Integer(min: 1)
                ]
            ]
        );

        /* Check specific rules */
        $this->validateAccountManagerExists($requestDataDealership["accountManagerId"], $this->userService);

        $requestData = $this->validateCreateDealership($requestData, $this->userService, $geoService);
        $requestData["requestData"]["requestDataDealership"]["accountManagerId"] = $requestDataDealership["accountManagerId"];

        return $requestData;
    }

    public function updateDealerFromArray(
        array $requestData,
        GeoService $geoService
    ): array {
        $requestDataDealership = $this->fetchOptional($requestData, [
            'id',
            'accountManagerId',
            "name",
            "businessNumber",
            "website",
            "phone",
            "address",
            "postalCode",
            "province",
            "googleMapsBusinessUrl",
            "googleMapsReviewsUrl",
        ]);

        /* Check general rules */
        $this->validateData($requestDataDealership, [
            "id" => [
                new Required(),
                new Integer(min: 1)
            ],

            "accountManagerId" => [
                new Required(),
                new Integer(min: 1)
            ],

            "name" => [
                new Required(),
                $this->getDealerShipNameValidator()
            ],

            "businessNumber" => [
                new Required(),
                $this->getDealerShipLicenseValidator()
            ],

            'phone' => [
                new Required(),
                $this->getPhoneValidator()
            ],

            "address" => [
                new Required(),
                new Length(min: 2, max: 64),
                new StringValue(
                    skipOnError: true
                )
            ],

            'postalCode' => [
                new Required(),
                $this->getPostalCodeValidator()
            ],

            "province" => [
                new Required(),
                new InEnum(Province::class, useNames: true, skipOnError: true),
            ],

            "googleMapsBusinessUrl" => [
                new Url(skipOnEmpty: true),
                new Regex("/^https?:\/\/(www\.)?google\.com(\.([a-z]+))?\/maps\/place\/.*?\/@[-0-9\.]+,[-0-9\.]+,[-0-9a-z]+\/data=(.*?)$/i", skipOnEmpty: true)
            ]
        ]);

        $requestDataDealership['postalCode'] = str_replace(' ', '', $requestDataDealership['postalCode']);

        /* Check specific rules */
        $this->validateAccountManagerExists($requestDataDealership["accountManagerId"], $this->userService);
        $this->validateCanManageDealer($requestDataDealership["id"], $this->dealerService, $this->userService, $this->currentUser);

        /* set geodata in cache just for postalcode in db, if postal code is wrong, we'll get an exception */
        $this->validatePostalCodeForGeoData($requestDataDealership, $geoService);

        /* check ability to get geodata for full address */
        // $query = "{$requestDataDealership["address"]}, {$requestDataDealership["postalCode"]} {$requestDataDealership["province"]}";
        // $geoService->getGeoData($query, $requestDataDealership["postalCode"]);

        return ["requestData" => $requestDataDealership];
    }


    public function approveDealerFromArray(
        array $requestData
    ): array {
        $requestData = $this->fetchRequired($requestData, [
            "dealerId",
        ]);

        /* Check general rules */
        $this->validateData($requestData, [
            'dealerId' => [
                new Integer()
            ],
        ]);

        /* Check specific rules */
        $this->validateCanManageDealer($requestData["dealerId"], $this->dealerService, $this->userService, $this->currentUser);

        return compact("requestData");
    }

    public function suspendDealerFromArray(
        array $requestData
    ): array {
        $requestData = $this->fetchRequired($requestData, [
            "dealerId",
        ]);

        /* Check general rules */
        $this->validateData($requestData, [
            'dealerId' => [
                new Integer()
            ],
        ]);

        /* Check specific rules */
        $this->validateCanManageDealer($requestData["dealerId"], $this->dealerService, $this->userService, $this->currentUser);

        return compact("requestData");
    }

    public function unsuspendDealerFromArray(
        array $requestData
    ): array {
        $requestData = $this->fetchRequired($requestData, [
            "dealerId",
        ]);

        /* Check general rules */
        $this->validateData($requestData, [
            'dealerId' => [
                new Integer()
            ],
        ]);

        /* Check specific rules */
        $this->validateCanManageDealer($requestData["dealerId"], $this->dealerService, $this->userService, $this->currentUser);

        return compact("requestData");
    }

    public function assignAccountManagersFromArray(
        array $requestData,
        UserService $userService,
        CurrentUser $currentUser
    ): array {
        $requestData = $this->fetchRequired($requestData, [
            "accountManagerId",
            "dealersIds"
        ]);

        /* Check general rules */
        $this->validateData($requestData, [
            'accountManagerId' => [
                new Integer()
            ],
        ]);

        /* Check specific rules */
        if (!$userService->isSuperAdmin($currentUser)) {
            throw new ForbiddenException($this->translator->translate("You have no rights to assign Account manager to dealers"));
        }

        return compact("requestData");
    }


    public function uploadDealerLogoFromArray(
        array $requestData,
        array $files,
        ConfigInterface $config
    ): array {
        $requestData = $this->fetchRequired($requestData, ['dealerId']);

        // upload params
        $params = $config->get('params');
        $allowedMimeTypes = $params['uploadedFiles']['logo']['allowedMimeTypes'];
        $maxUploadFileSizeMb = $params['uploadedFiles']['logo']['maxUploadFileSize'];

        /* Check general rules */
        $this->validateData($requestData, [
            'dealerId' => [
                new Integer()
            ],
        ]);

        /* Check specific rules */
        $this->validateCanManageDealer($requestData["dealerId"], $this->dealerService, $this->userService, $this->currentUser);

        if ($this->validateThatNoFilesUploaded($files)) {
            return ["dealerId" => $requestData["dealerId"], "files" => []];
        }

        $this->validateUploadedFiles($files, 1, $maxUploadFileSizeMb, $allowedMimeTypes);

        return compact("requestData", "files");
    }
}
