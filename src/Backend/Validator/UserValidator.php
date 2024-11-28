<?php

namespace App\Backend\Validator;

use App\Backend\Model\User\Role;
use App\Backend\Model\Province;
use App\Backend\Service\CarService;
use App\Backend\Service\DealerService;
use App\Backend\Service\GeoService;
use App\Backend\Service\UserDealerPositionService;
use App\Backend\Service\UserService;
use Yiisoft\User\CurrentUser;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\InEnum;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Number;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rule\StringValue;
use Yiisoft\Validator\ValidationContext;

final class UserValidator extends AbstractValidator
{
    public function __construct(
        protected UserService $userService,
        protected CurrentUser $currentUser,
        protected TranslatorInterface $translator
    ) {
        parent::__construct(translator: $translator);
    }

    public function getUser(
        int $id
    ): array {
        /* Check general rules */
        $this->validateData(["id" => $id], [
            'id' => [
                new Integer()
            ],
        ]);

        /* Check specific rules */
        $this->validateUserExists($id, $this->userService);

        return compact("id");
    }

    public function addUserFromArray(
        array $requestData,
        DealerService $dealerService,
        GeoService $geoService
    ): array {
        $requestData = $this->fetchOptional($requestData, [
            "dealerId",
            "username",
            "email",
            "role",
            "licenseNumber",
            "address",
            "province",
            "postalCode",
            "phone",
            "receiveEmails",
            "customComission"
        ]);
        $requestData["receiveEmails"] = !empty($requestData["receiveEmails"]) ? 1 : 0;

        $this->validateData($requestData, [
            'username' => [
                new Required(),
                $this->getUserNameValidator()
            ],

            'email' => [
                new Required(),
                new Email(skipOnError: true),
            ],

            'role' => [
                new Required(),
                new InEnum(Role::class, skipOnError: true),
            ],

            'dealerId' => [
                new Required(when: self::isDealerRole(...)),
                new Integer(skipOnEmpty: true, skipOnError: true),
            ],

            "licenseNumber" => [
                new Required(when: self::isDealerRole(...)),
                $this->getUserLicenseValidator()
            ],

            'phone' => [
                new Required(when: self::isDealerRole(...)),
                $this->getPhoneValidator()
            ],

            'address' => [
                new Required(when: self::isDealerRole(...)),
                new Length(skipOnEmpty: true, skipOnError: true, min: 2, max: 64),
                new StringValue(
                    skipOnError: true
                )
            ],

            'postalCode' => [
                new Required(when: self::isDealerRole(...)),
                $this->getPostalCodeValidator()
            ],

            'province' => [
                new Required(when: self::isDealerRole(...)),
                new InEnum(Province::class, useNames: true, skipOnEmpty: true, skipOnError: true),
            ],

            'customComission' => [
                new Required(when: self::isAccountManagerRole(...)),
                new Number(min: 0, max: 99, skipOnEmpty: true, skipOnError: true),
            ]
        ]);

        /* Check specific rules */
        $requestData = $this->validatePostalCodeForGeoData($requestData, $geoService);
        $this->validateUserWithEmailExists($requestData["email"], $this->userService);

        $role = Role::tryFrom($requestData["role"]);

        if ($role->isDealerRole()) {
            $this->validateDealerExists($requestData["dealerId"], $dealerService);
        }

        $requestData["role"] = $role;

        return ["requestData" => $requestData];
    }

    public function updateUserFromArray(
        array $requestData,
        GeoService $geoService
    ): array {
        $requestData = $this->fetchOptional(
            $requestData,
            [
                "id",
                "username",
                "email",
                "licenseNumber",
                "address",
                "province",
                "postalCode",
                "phone",
                "receiveEmails",
                "customComission"
            ]
        );
        $requestData["receiveEmails"] = !empty($requestData["receiveEmails"]) ? 1 : 0;

        $this->validateData($requestData, [
            'id' => [
                new Required(),
                new Integer(),
            ],
        ]);

        $this->validateUserExists($requestData['id'], $this->userService);
        $user = $this->userService->getUser($requestData['id']);
        $isUserDealerValidation = static function (mixed $value, ValidationContext $context) use ($user): bool {
            return $user->isDealer;
        };
        $isUserAccountManagerValidation = static function (mixed $value, ValidationContext $context) use ($user): bool {
            return $user->isAccountManager;
        };

        $this->validateData($requestData, [
            'username' => [
                $this->getUserNameValidator()
            ],

            'email' => [
                new Required(),
                new Email(skipOnError: true),
            ],

            "licenseNumber" => [
                new Required(when: $isUserDealerValidation),
                $this->getUserLicenseValidator()
            ],

            'phone' => [
                new Required(when: $isUserDealerValidation),
                $this->getPhoneValidator()
            ],

            'address' => [
                new Required(when: $isUserDealerValidation),
                new Length(skipOnEmpty: true, skipOnError: true, min: 2, max: 64),
                new StringValue(
                    skipOnError: true
                )
            ],

            'postalCode' => [
                new Required(when: $isUserDealerValidation),
                $this->getPostalCodeValidator()
            ],

            'province' => [
                new Required(when: $isUserDealerValidation),
                new InEnum(Province::class, useNames: true, skipOnError: true, skipOnEmpty: true),
            ],

            'customComission' => [
                new Required(when: $isUserAccountManagerValidation),
                new Number(min: 0, max: 99, skipOnEmpty: true, skipOnError: true),
            ]
        ]);

        /* Check specific rules */
        $requestData = $this->validatePostalCodeForGeoData($requestData, $geoService);
        $this->validateOtherUsersWithEmailExists($requestData['email'], $requestData['id'], $this->userService);

        return ["requestData" => $requestData];
    }

    public function addRoleToUserFromArray(
        array $requestData,
        DealerService $dealerService,
        GeoService $geoService
    ): array {
        $requestData = $this->fetchOptional(
            $requestData,
            [
                "userId",
                "dealerId",
                "role",
                "licenseNumber",
                "address",
                "province",
                "postalCode",
                "phone",
                "receiveEmails",
                "customComission"
            ]
        );
        $requestData["receiveEmails"] = !empty($requestData["receiveEmails"]) ? 1 : 0;

        $this->validateData($requestData, [
            'userId' => [
                new Required(),
                new Integer(),
            ],

            'role' => [
                new Required(),
                new InEnum(Role::class, skipOnError: true),
            ],

            'dealerId' => [
                new Required(when: self::isDealerRole(...)),
                new Integer(skipOnEmpty: true, skipOnError: true),
            ],

            "licenseNumber" => [
                new Required(when: self::isDealerRole(...)),
                $this->getUserLicenseValidator()
            ],

            'phone' => [
                new Required(when: self::isDealerRole(...)),
                $this->getPhoneValidator()
            ],

            'address' => [
                new Required(when: self::isDealerRole(...)),
                new Length(skipOnEmpty: true, skipOnError: true, min: 2, max: 64),
                new StringValue(
                    skipOnError: true
                )
            ],

            'postalCode' => [
                new Required(when: self::isDealerRole(...)),
                $this->getPostalCodeValidator()
            ],

            'province' => [
                new Required(when: self::isDealerRole(...)),
                new InEnum(Province::class, useNames: true, skipOnEmpty: true, skipOnError: true),
            ],

            'customComission' => [
                new Required(when: self::isAccountManagerRole(...)),
                new Number(min: 0, max: 99, skipOnEmpty: true, skipOnError: true),
            ]
        ]);

        /* Check specific rules */
        $requestData = $this->validatePostalCodeForGeoData($requestData, $geoService);
        $this->validateUserExists($requestData['userId'], $this->userService);

        $role = Role::tryFrom($requestData["role"]);

        if ($role->isDealerRole()) {
            $this->validateDealerExists($requestData["dealerId"], $dealerService);
        }

        $requestData["role"] = $role;

        return ["requestData" => $requestData];
    }

    public function unassignRoleFromUserFromArray(
        array $requestData,
        DealerService $dealerService,
        UserDealerPositionService $userDealerPositionService
    ): array {
        $requestData = $this->fetchOptional(
            $requestData,
            [
                "userId",
                "dealerId",
                "role"
            ]
        );

        $this->validateData($requestData, [
            'userId' => [
                new Required(),
                new Integer(),
            ],

            'dealerId' => [
                new Required(when: self::isDealerRole(...)),
                new Integer(skipOnEmpty: true, skipOnError: true),
            ],

            'role' => [
                new Required(),
                new InEnum(Role::class, skipOnError: true),
            ],
        ]);

        /* Check specific rules */
        $this->validateUserExists($requestData['userId'], $this->userService);

        $role = Role::tryFrom($requestData["role"]);

        if ($role->isDealerRole()) {
            $this->validateDealerExists($requestData["dealerId"], $dealerService);
            $this->validateCanUnassignUserFromDealer($requestData["userId"], $requestData["dealerId"], $userDealerPositionService);
            $this->validateCanUnassignUserFromAccountManager($requestData["userId"], $dealerService);
        }

        $requestData["role"] = $role;

        return ["requestData" => $requestData];
    }







    public function addUserToDealerFromArray(
        array $requestData,
        DealerService $dealerService,
        GeoService $geoService
    ): array {
        $requestData = $this->fetchOptional($requestData, [
            "dealerId",
            "username",
            "licenseNumber",
            "phone",
            "email",
            "address",
            "postalCode",
            "province",
            "role",
            "receiveEmails"
        ]);
        $requestData["receiveEmails"] = !empty($requestData["receiveEmails"]) ? 1 : 0;

        $this->validateData($requestData, [
            'dealerId' => [
                new Required(),
                new Integer(min: 1),
            ],

            'username' => [
                new Required(),
                $this->getUserNameValidator()
            ],

            'licenseNumber' => [
                new Required(),
                $this->getUserLicenseValidator()
            ],

            'phone' => [
                new Required(),
                $this->getPhoneValidator()
            ],

            'email' => [
                new Required(),
                new Email(skipOnError: true),
            ],

            'address' => [
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

            'province' => [
                new Required(),
                new InEnum(Province::class, useNames: true, skipOnError: true),
            ],

            'role' => [
                new Required(),
                new InEnum(Role::class, skipOnError: true),
            ],
        ]);

        /* Check specific rules */
        $requestData = $this->validatePostalCodeForGeoData($requestData, $geoService);
        $this->validateCanManageDealer($requestData["dealerId"], $dealerService, $this->userService, $this->currentUser);
        $this->validateUserWithEmailExists($requestData['email'], $this->userService);

        $role = Role::tryFrom($requestData["role"]);

        if (!$role->isDealerRole()) {
            $this->throwValidationException("role", $this->translator->translate("You are trying to assign user not to dealer role"));
        }

        $requestData["role"] = $role;

        return ["requestData" => $requestData];
    }

    public function updateUserToDealerFromArray(
        array $requestData,
        DealerService $dealerService,
        GeoService $geoService
    ): array {
        $requestData = $this->fetchOptional($requestData, [
            "id",
            "dealerId",
            "username",
            "licenseNumber",
            "phone",
            "email",
            "address",
            "postalCode",
            "province",
            "role",
            "receiveEmails"
        ]);
        $requestData["receiveEmails"] = !empty($requestData["receiveEmails"]) ? 1 : 0;

        $this->validateData($requestData, [
            'id' => [
                new Required(),
                new Integer(),
            ],

            'dealerId' => [
                new Required(),
                new Integer(min: 1),
            ],

            'username' => [
                new Required(),
                $this->getUserNameValidator()
            ],

            'licenseNumber' => [
                new Required(),
                $this->getUserLicenseValidator()
            ],

            'phone' => [
                new Required(),
                $this->getPhoneValidator()
            ],

            'email' => [
                new Required(),
                new Email(skipOnError: true),
            ],

            'address' => [
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

            'province' => [
                new Required(),
                new InEnum(Province::class, useNames: true, skipOnError: true),
            ],

            'role' => [
                new Required(),
                new InEnum(Role::class, skipOnError: true),
            ],
        ]);


        /* Check specific rules */
        $requestData = $this->validatePostalCodeForGeoData($requestData, $geoService);
        $this->validateCanManageDealer($requestData["dealerId"], $dealerService, $this->userService, $this->currentUser);
        $this->validateUserExists($requestData["id"], $this->userService);
        $this->validateOtherUsersWithEmailExists($requestData['email'], $requestData['id'], $this->userService);

        $role = Role::tryFrom($requestData["role"]);

        if (!$role->isDealerRole()) {
            $this->throwValidationException("role", $this->translator->translate("You are trying to assign user not to dealer role"));
        }

        $requestData["role"] = $role;

        return ["requestData" => $requestData];
    }

    public function unassignUserFromDealerFromArray(
        array $requestData,
        DealerService $dealerService,
        UserDealerPositionService $userDealerPositionService
    ): array {
        $requestData = $this->fetchRequired($requestData, [
            "userId",
            "dealerId"
        ]);

        /* Check general rules */
        $this->validateData($requestData, [
            'userId' => [
                new Integer()
            ],
            'dealerId' => [
                new Integer()
            ],
        ]);

        /* Check specific rules */
        $userModel = $this->validateUserExists($requestData["userId"], $this->userService);
        $this->validateCanManageDealer($requestData["dealerId"], $dealerService, $this->userService, $this->currentUser);
        $this->validateCanUnassignUserFromDealer($requestData["userId"], $requestData["dealerId"], $userDealerPositionService);

        return compact("requestData");
    }

    public function setUserAsPrimaryDealerFromArray(
        array $requestData,
        DealerService $dealerService
    ): array {
        $requestData = $this->fetchRequired($requestData, [
            "userId",
            "dealerId"
        ]);

        /* Check general rules */
        $this->validateData($requestData, [
            'userId' => [
                new Integer()
            ],
            'dealerId' => [
                new Integer()
            ],
        ]);

        /* Check specific rules */
        $this->validateUserExists($requestData["userId"], $this->userService);
        $this->validateCanManageDealer($requestData["dealerId"], $dealerService, $this->userService, $this->currentUser);

        return compact("requestData");
    }

    public function validateDeleteUserFromArray(
        array $requestData,
        CurrentUser $currentUser,
    ): array {
        $requestData = $this->fetchRequired($requestData, [
            "userId",
        ]);

        /* Check general rules */
        $this->validateData($requestData, [
            'userId' => [
                new Integer()
            ],
        ]);

        /* Check specific rules */
        if ($requestData["userId"] == $currentUser->getId()) {
            $this->throwValidationException("id", $this->translator->translate("You cannot remove yourself"));
        }

        return compact("requestData");
    }

    public function setUserDeletionDateFromArray(
        array $requestData,
        CurrentUser $currentUser,
    ): array {
        $requestData = $this->fetchRequired($requestData, [
            "userId",
        ]);

        /* Check general rules */
        $this->validateData($requestData, [
            'userId' => [
                new Integer()
            ],
        ]);

        /* Check specific rules */
        if ($requestData["userId"] == $currentUser->getId()) {
            $this->throwValidationException("id", $this->translator->translate("You cannot remove yourself"));
        }

        return compact("requestData");
    }

    public function clearUserDeletionDateFromArray(
        array $requestData,
    ): array {
        $requestData = $this->fetchRequired($requestData, [
            "userId",
        ]);

        /* Check general rules */
        $this->validateData($requestData, [
            'userId' => [
                new Integer()
            ],
        ]);

        return compact("requestData");
    }

    public function deleteUserFromArray(
        array $requestData,
        CurrentUser $currentUser,
        CarService $carService,
        DealerService $dealerService,
        UserDealerPositionService $userDealerPositionService
    ): array {
        $requestData = $this->fetchRequired($requestData, [
            "userId",
        ]);

        /* Check general rules */
        $this->validateData($requestData, [
            'userId' => [
                new Integer()
            ],
        ]);

        /* Check specific rules */
        if ($requestData["userId"] == $currentUser->getId()) {
            $this->throwValidationException("id", $this->translator->translate("You cannot remove yourself"));
        }

        $userModel = $this->validateUserExists($requestData["userId"], $this->userService);
        // $this->validateCanSuspendOrDeleteAccountManager($userModel->id, $dealerService);
        // $this->validateCanSuspendOrDeleteDealer($userModel->id, $userDealerPositionService);
        // $this->validateCanDeleteClient($userModel->id, $carService);

        return compact("requestData");
    }

    public function suspendUserFromArray(
        array $requestData,
        CurrentUser $currentUser,
        DealerService $dealerService,
        UserDealerPositionService $userDealerPositionService
    ): array {
        $requestData = $this->fetchRequired($requestData, [
            "userId",
            "notifyUser"
        ]);

        /* Check general rules */
        $this->validateData($requestData, [
            'userId' => [
                new Integer()
            ],
            'notifyUser' => [
                new Integer(min: 0, max: 1),
            ],
        ]);

        /* Check specific rules */
        $userModel = $this->validateUserExists($requestData["userId"], $this->userService);

        if ($requestData["userId"] == $currentUser->getId()) {
            $this->throwValidationException("id", $this->translator->translate("You cannot suspend yourself"));
        }

        $this->validateCanSuspendOrDeleteAccountManager($userModel->id, $dealerService);
        $this->validateCanSuspendOrDeleteDealer($userModel->id, $userDealerPositionService);

        return compact("requestData");
    }

    public function unsuspendUserFromArray(
        array $requestData,
        CurrentUser $currentUser
    ): array {
        $requestData = $this->fetchRequired($requestData, [
            "userId"
        ]);

        /* Check general rules */
        $this->validateData($requestData, [
            'userId' => [
                new Integer()
            ],
        ]);

        /* Check specific rules */
        $userModel = $this->validateUserExists($requestData["userId"], $this->userService);

        return compact("requestData");
    }





    private static function isDealerRole(mixed $value, ValidationContext $context): bool
    {
        $role = Role::tryFrom($context->getDataSet()->getPropertyValue('role'));

        return $role->isDealerRole();
    }

    private static function isAccountManagerRole(mixed $value, ValidationContext $context): bool
    {
        $role = Role::tryFrom($context->getDataSet()->getPropertyValue('role'));

        return $role == Role::AdminAccountManager;
    }

    private function validateCanSuspendOrDeleteAccountManager(
        int $userId,
        DealerService $dealerService
    ) {
        if ($this->userService->isAccountManagerAdminOnly($userId)) {
            $accountManagersIds = $this->userService->getAccountManagersIds();

            if (count($accountManagersIds) == 1) {
                $this->throwValidationException("id", $this->translator->translate("You are trying to remove or suspend last Account Manager in the system. Don't do it!"));
            }

            $countDealers = $dealerService->getAccountManagerDealersCount($userId);

            if ($countDealers > 0) {
                $this->throwValidationException(
                    "id",
                    $this->translator->translate("You cannot remove or suspend this Account Manager because of {countDealers} dealerships assigned to him", ['countDealers' => $countDealers])
                );
            }
        }
    }

    private function validateCanSuspendOrDeleteDealer(
        int $userId,
        UserDealerPositionService $userDealerPositionService
    ) {
        if ($this->userService->isDealerOnly($userId)) {
            $dealerPositions = $userDealerPositionService->search(filters: ["user" => $userId]);

            foreach ($dealerPositions as $dealerPosition) {
                if ($userDealerPositionService->searchTotal(filters: ["dealer" => $dealerPosition->dealerId]) == 1) {
                    $this->throwValidationException(
                        "id",
                        $this->translator->translate("You cannot remove or suspend this user because there are no other users in his dealership")
                    );
                }
            }
        }
    }

    private function validateCanUnassignUserFromAccountManager(
        int $userId,
        DealerService $dealerService
    ) {
        if ($this->userService->isAccountManagerAdminOnly($userId)) {
            $countDealers = $dealerService->getAccountManagerDealersCount($userId);

            if ($countDealers > 0) {
                $this->throwValidationException(
                    "id",
                    $this->translator->translate("You cannot unassign user from Account Manager role because of {countDealers} dealerships assigned to him", ['countDealers' => $countDealers])
                );
            }
        }
    }

    private function validateCanUnassignUserFromDealer(
        int $userId,
        int $dealerId,
        UserDealerPositionService $userDealerPositionService
    ) {
        if ($this->userService->isDealerOnly($userId)) {
            if ($userDealerPositionService->searchTotal(filters: ["dealerId" => $dealerId]) == 1) {
                $this->throwValidationException(
                    "id",
                    $this->translator->translate("You cannot unassign this user from dealership because there are no other users in it")
                );
            }
        }
    }

    private function validateCanDeleteClient(
        int $userId,
        CarService $carService
    ) {
        $countCars = $carService->getClientCarsCount($userId);

        if ($countCars > 0) {
            $this->throwValidationException(
                "id",
                $this->translator->translate("You cannot remove this user because of {countCars} cars in his catalog", ['countCars' => $countCars])
            );
        }
    }
}
