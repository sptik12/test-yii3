<?php

namespace App\Backend\Validator;

use App\Backend\Exception\Http\ForbiddenException;
use App\Backend\Exception\Http\NotFoundException;
use App\Backend\Exception\ValidationException;
use App\Backend\Model\Car\CarModel;
use App\Backend\Model\Car\CarMediaModel;
use App\Backend\Model\Dealer\DealerModel;
use App\Backend\Model\User\UserModel;
use App\Backend\Model\Province;
use App\Backend\Service\DealerService;
use App\Backend\Service\GeoService;
use App\Backend\Service\UserService;
use App\Backend\Service\CarService;
use Yiisoft\User\CurrentUser;
use Yiisoft\Validator\Error;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\InEnum;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Regex;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rule\StringValue;
use Yiisoft\Validator\Validator;
use Yiisoft\Translator\TranslatorInterface;

abstract class AbstractValidator
{
    public function __construct(
        protected TranslatorInterface $translator
    ) {
    }

    public function validateData(mixed $data, callable|iterable|null|object|string $rules): Result
    {
        $result = (new Validator())->validate($data, $rules);

        if (!$result->isValid()) {
            $this->throwValidationException(source: null, errors: $result->getErrors());
        }

        return $result;
    }

    public function fetchRequired(array $requestData, array $fields): array
    {
        $data = $requestData;
        $missing = [];
        $values = [];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $values[$field] = $data[$field];
            } else {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            $s = count($missing) === 1 ? "" : "s";
            $article = count($missing) === 1 ? "is" : "are";
            $missingString = implode("', '", $missing);
            throw new ValidationException("Required field{$s} '{$missingString}' {$article} missing", 400);
        }

        return $values;
    }

    public function fetchOptional(array $requestData, array $fields, $defaultValue = null): array
    {
        $data = $requestData;
        $values = [];

        foreach ($fields as $field) {
            $values[$field] = $data[$field] ?? $defaultValue;
        }

        return $values;
    }

    public function throwValidationException(?string $source, string|array $errors, int $code = 422)
    {
        $exception = new ValidationException(code: $code);

        if (is_array($errors)) {
            $processedErrors = [];

            foreach ($errors as $error) {
                if ($error instanceof Error) {
                    $parameter = $error->getParameters();
                    $processedErrors[] = [
                        'source' => $parameter['property'] ?? null,
                        'message' => $error->getMessage()
                    ];
                } else {
                    $processedErrors[] = $error;
                }
            }

            $exception->setErrors($processedErrors);
        } else {
            $message = $errors;
            $exception->setError(compact("message", "source"));
        }

        throw $exception;
    }

    public function getFileUploadError(int $error, TranslatorInterface $translator): string
    {
        return match ($error) {
            UPLOAD_ERR_OK => $translator->translate("There is no error, the file uploaded with success"),
            UPLOAD_ERR_INI_SIZE => $translator->translate(
                "The uploaded file exceeds the upload_max_filesize directive in php.ini ({uploadMaxFileSize})",
                ['uploadMaxFileSize' => ini_get("upload_max_filesize")]
            ),
            UPLOAD_ERR_FORM_SIZE => $translator->translate("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"),
            UPLOAD_ERR_PARTIAL => $translator->translate("The uploaded file was only partially uploaded"),
            UPLOAD_ERR_NO_FILE => $translator->translate("No file was uploaded"),
            UPLOAD_ERR_NO_TMP_DIR => $translator->translate("Missing a temporary folder"),
            UPLOAD_ERR_CANT_WRITE => $translator->translate("Failed to write file to disk"),
            UPLOAD_ERR_EXTENSION => $translator->translate("A PHP extension stopped the file upload"),
            default => $translator->translate("Unknown error")
        };
    }





    protected function setFieldsToNullIfEmpty(array $requestData, array $fields): array
    {
        foreach ($fields as $field) {
            if (array_key_exists($field, $requestData) && empty($requestData[$field])) {
                $requestData[$field] = null;
            }
        }

        return $requestData;
    }


    protected function validateUploadedFiles(
        array $files,
        int $maxNumberOfUploadedFiles,
        int $maxUploadFileSizeMb,
        array $allowedMimeTypes
    ): void {
        $maxUploadFileSize = $maxUploadFileSizeMb * 1024 * 1024;

        if (count($files) > $maxNumberOfUploadedFiles) {
            $this->throwValidationException(
                "files",
                $this->translator->translate(
                    "Total number of uploaded files cannot be more than {maxNumberOfUploadedFiles}",
                    ["maxNumberOfUploadedFiles" => $maxNumberOfUploadedFiles]
                )
            );
        }

        foreach ($files as $file) {
            $error = $file->getError();

            if ($error !== UPLOAD_ERR_OK && $error != UPLOAD_ERR_NO_FILE) {
                $this->throwValidationException("files", $this->getFileUploadError($error, $this->translator));
            }

            $fileName = $file->getClientFilename();

            if ($file->getSize() > $maxUploadFileSize) {
                $this->throwValidationException(
                    "files",
                    $this->translator->translate(
                        "File {fileName} is bigger than {maxUploadFileSizeMb} MB",
                        ['fileName' => $fileName, 'maxUploadFileSizeMb' => $maxUploadFileSizeMb]
                    )
                );
            }

            $mimeType = $file->getClientMediaType();

            if (!in_array($mimeType, $allowedMimeTypes)) {
                $this->throwValidationException(
                    "files",
                    $this->translator->translate("File {fileName} is not a media. Invalid mime type {mimetype} ", ['fileName' => $fileName, 'mimetype' => $mimeType])
                );
            }
        }
    }

    protected function validateThatNoFilesUploaded(array $files): bool
    {
        foreach ($files as $file) {
            $error = $file->getError();

            // by some reason if no files were uploaded $request->getUploadedFiles() always return
            // an array with one element and error code = UPLOAD_ERR_NO_FILE
            if ($error == UPLOAD_ERR_NO_FILE) {
                return true;
            }
        }

        return false;
    }

    // create Dealer validation
    protected function validateCreateDealership(
        array $requestData,
        UserService $userService,
        GeoService $geoService
    ): array {
        $requestDataDealership = $this->fetchOptional($requestData, [
            "dealershipName",
            "businessNumber",
            "website",
            "dealershipPhone",
            "dealershipAddress",
            "dealershipPostalCode",
            "dealershipProvince",
        ]);
        $requestDataUser = $this->fetchOptional($requestData, [
            "username",
            "licenseNumber",
            "phone",
            "email",
            "representativeAddress",
            "representativePostalCode",
            "representativeProvince",
            "receiveEmails"
        ]);
        $requestDataUser["receiveEmails"] = !empty($requestData["receiveEmails"]) ? 1 : 0;

        /* Check general rules */
        $this->validateData($requestDataDealership, [
            "dealershipName" => [
                new Required(),
                $this->getDealerShipNameValidator()
            ],

            "businessNumber" => [
                new Required(),
                $this->getDealerShipLicenseValidator()
            ],

            'dealershipPhone' => [
                new Required(),
                $this->getPhoneValidator()
            ],

            "dealershipAddress" => [
                new Required(),
                new Length(min: 2, max: 64),
                new StringValue(
                    skipOnError: true
                )
            ],

            'dealershipPostalCode' => [
                new Required(),
                $this->getPostalCodeValidator()
            ],

            "dealershipProvince" => [
                new Required(),
                new InEnum(Province::class, useNames: true, skipOnError: true),
            ],
        ]);

        $this->validateData($requestDataUser, [
            'username' => [
                new Required(),
                $this->getUserNameValidator()
            ],

            "licenseNumber" => [
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

            "representativeAddress" => [
                new Required(),
                new Length(min: 2, max: 64),
                new StringValue(
                    skipOnError: true
                )
            ],

            'representativePostalCode' => [
                new Required(),
                $this->getPostalCodeValidator()
            ],

            "representativeProvince" => [
                new Required(),
                new InEnum(Province::class, useNames: true, skipOnError: true),
            ],
        ]);

        $requestDataDealership["name"] = $requestData["dealershipName"];
        $requestDataDealership["postalCode"] = $requestData["dealershipPostalCode"];
        $requestDataDealership["address"] = $requestData["dealershipAddress"];
        $requestDataDealership["province"] = $requestData["dealershipProvince"];
        $requestDataDealership["phone"] = $requestData["dealershipPhone"];

        /* Check specific rules for dealer */
        $requestDataDealership = $this->validatePostalCodeForGeoData($requestDataDealership, $geoService);

        $requestDataUser["address"] = $requestData["representativeAddress"];
        $requestDataUser["postalCode"] = $requestData["representativePostalCode"];
        $requestDataUser["province"] = $requestData["representativeProvince"];

        /* Check specific rules for user */
        $requestDataUser = $this->validatePostalCodeForGeoData($requestDataUser, $geoService);
        $this->validateUserWithEmailExists($requestDataUser['email'], $userService);

        return ["requestData" => compact("requestDataDealership", "requestDataUser")];
    }







    /* General regex validators */

    protected function getUserNameValidator($message = null): Regex
    {
        return new Regex(
            pattern: "/^[A-Za-z\s\'’`]{4,32}$/",
            message: $message ?? $this->translator->translate("Full Name must contain at least 4 and at most 32 symbols. Only Latin symbols, apostrophes, and spaces are allowed."),
            skipOnEmpty: true,
            skipOnError: true
        );
    }

    protected function getUserPasswordValidator(): Regex
    {
        return new RegEx(
            pattern: "/^(?=.*[a-z])(?=.*[A-Z])\S{5,}$/",
            message: $this->translator->translate("Password must contain at least 5 symbols, one of them must be uppercase and another one lowercase, and it must not contain spaces."),
            skipOnEmpty: true,
        );
    }

    protected function getDealerShipNameValidator(): Regex
    {
        return new Regex(
            pattern: "/^[0-9A-Za-z\s\'’`\.\-&]{2,64}$/",
            message: $this->translator->translate("Dealership must contain at least 2 and at most 64 symbols. Only Latin symbols, digits, apostrophes, hyphens, and spaces are allowed."),
            skipOnError: true
        );
    }

    protected function getDealerShipLicenseValidator(): Regex
    {
        return new Regex(
            pattern: "/^[0-9A-Z\s]{5,20}$/",
            message: $this->translator->translate("The dealer license must contain at least 5 and at most 20 symbols. Only uppercase Latin letters, spaces, and digits are allowed."),
            skipOnError: true
        );
    }

    protected function getPhoneValidator(): Regex
    {
        return new Regex(
            pattern: "/^[0-9 +.\(\)\-]{1,20}$/",
            message: $this->translator->translate("The phone number can contain only numbers, parentheses, dots, spaces, the + and the -."),
            skipOnEmpty: true,
            skipOnError: true
        );
    }

    protected function getPostalCodeValidator(): Regex
    {
        return new Regex(
            pattern: "/^(?:[A-Z]\d[A-Z]|[A-Z]\d[A-Z][ ]?\d[A-Z]\d)$/",
            message: $this->translator->translate("Please enter a valid postal code of 3 or 6 alphanumeric characters."),
            skipOnEmpty: true,
            skipOnError: true
        );
    }

    protected function getUserLicenseValidator(): Regex
    {
        return new Regex(
            pattern: "/^[0-9A-Z\s]{5,20}$/",
            message: $this->translator->translate("The license number must contain at least 5 and at most 20 symbols. Only uppercase Latin letters, spaces, and digits are allowed."),
            skipOnEmpty: true,
            skipOnError: true
        );
    }


    /*
     * Exists records validation
     */
    protected function validateIsLogged(CurrentUser $currentUser)
    {
        if ($currentUser->isGuest()) {
            throw new ForbiddenException($this->translator->translate("You are not logged. Please login to have ability manage searhes in wishlist"));
        }
    }

    protected function validateDealerExists(
        int $dealerId,
        DealerService $dealerService,
    ): ?DealerModel {
        $dealerModel = $dealerService->findById($dealerId);

        if (!$dealerModel) {
            throw new NotFoundException($this->translator->translate("This dealer cannot be found"));
        }

        return $dealerModel;
    }

    protected function validateUserExists(
        int $userId,
        UserService $userService
    ): ?UserModel {
        $userModel = $userService->findById($userId);

        if (!$userModel) {
            throw new NotFoundException("id", $this->translator->translate("This user cannot be found"));
        }

        return $userModel;
    }

    protected function validateUserWithEmailExists(
        string $email,
        UserService $userService
    ): ?UserModel {
        $userModel = $userService->findByEmail($email);

        if ($userModel) {
            $this->throwValidationException("email", $this->translator->translate("A user with this email is already registered"));
        }

        return $userModel;
    }

    protected function validateOtherUsersWithEmailExists(
        string $email,
        int $excludeUserId,
        UserService $userService
    ): void {
        $isExists = $userService->existsByEmail($email, $excludeUserId);

        if ($isExists) {
            $this->throwValidationException("email", $this->translator->translate("A user with this email is already registered"));
        }
    }

    protected function validateAccountManagerExists(
        int $accountManagerId,
        UserService $userService,
    ): ?UserModel {
        $userModel = $userService->findById($accountManagerId);

        if (!$userModel) {
            $this->throwValidationException("id", $this->translator->translate("This Account manager cannot be found"));
        }

        if (!$this->userService->isAccountManagerAdminOnly($accountManagerId)) {
            throw new ForbiddenException($this->translator->translate("Assigned account manager has no corresponding role"));
        }

        return $userModel;
    }

    protected function validateCanManageDealer(
        int $dealerId,
        DealerService $dealerService,
        UserService $userService,
        CurrentUser $currentUser
    ): void {
        $dealerModel = $this->validateDealerExists($dealerId, $dealerService);

        if (!$userService->isSuperAdmin($currentUser)) {
            if ($dealerModel->accountManagerId != $currentUser->getId()) {
                throw new ForbiddenException($this->translator->translate("You have no rights to manage this dealer"));
            }
        }
    }

    protected function validateCarExists(
        int $carId,
        CarService $carService
    ): ?CarModel {
        $carModel = $carService->findById($carId);

        if (!$carModel) {
            throw new NotFoundException($this->translator->translate("This car cannot be found"));
        }

        return $carModel;
    }

    protected function validateCarExistsByPublicId(
        string $publicId,
        CarService $carService
    ): ?CarModel {
        $carModel = $carService->findByPublicId($publicId);

        if (!$carModel) {
            throw new NotFoundException($this->translator->translate("This car cannot be found"));
        }

        return $carModel;
    }

    protected function validateCarDealerId(
        int $dealerId,
        CurrentUser $currentUser
    ): void {
        if ($dealerId != $currentUser->getIdentity()->currentDealerId) {
            throw new ForbiddenException($this->translator->translate("You have no rights to update this car"));
        }
    }

    protected function validateCarClientId(
        int $clientId,
        CurrentUser $currentUser
    ): void {
        if ($clientId != $currentUser->getId()) {
            throw new ForbiddenException($this->translator->translate("You have no rights to update this car"));
        }
    }

    protected function validateCarMediaExists(
        int $carMediaId,
        int $carId,
        CarService $carService
    ): ?CarMediaModel {
        $carMediaModel = $carService->findCarMedia($carMediaId, $carId);

        if (!$carMediaModel) {
            $this->throwValidationException("id", $this->translator->translate("This media cannot be found"));
        }

        return $carMediaModel;
    }

    protected function validatePostalCodeForGeoData(
        array $requestData,
        GeoService $geoService
    ): array {
        if ($requestData['postalCode']) {
            $requestData['postalCode'] = str_replace(' ', '', $requestData['postalCode']);

            if (array_key_exists("province", $requestData) && $requestData["province"]) {
                $firstPostalCodeChar = substr($requestData['postalCode'], 0, 1);
                $province = Province::tryFromName($requestData["province"]);

                if (!in_array($firstPostalCodeChar, $province->getFirstPostalCodeChar())) {
                    $this->throwValidationException(
                        "id",
                        $this->translator->translate(
                            "There is no postal code {postalCode} in province {province}",
                            ['postalCode' => $requestData['postalCode'], 'province' => $requestData["province"]]
                        )
                    );
                }
            }

            /* set geodata in cache just for postalcode in db, if postal code is wrong, we'll get an exception */
            $geoService->setGeoDataForPostalCodeFromArray($requestData);
        }

        return $requestData;
    }


    protected function validatePostalCodeAndProvince(
        array $requestData
    ): bool {
        return true;
    }
}
