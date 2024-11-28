<?php

namespace App\Backend\Validator;

use Yiisoft\Validator\Rule\Number;
use Yiisoft\Validator\Rule\Regex;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Validator\Rule\Required;

final class GeoValidator extends AbstractValidator
{
    public function __construct(
        protected TranslatorInterface $translator,
    ) {
        parent::__construct(translator: $translator);
    }

    public function setGeoDataForPostalCodeFromArray(
        array $requestData
    ): array {
        $requestData = $this->fetchOptional($requestData, [
            "postalCode"
        ]);

        /* Check general rules */
        $this->validateData($requestData, [
            'postalCode' => [
                $this->getPostalCodeValidator()
            ],
        ]);

        // replace spaces in postalCode
        $requestData['postalCode'] = str_replace(' ', '', $requestData['postalCode']);

        return compact("requestData");
    }

    public function getPostalCodeByGeoDataFromArray(
        array $requestData
    ): array {
        $requestData = $this->fetchRequired($requestData, [
            "latitude",
            'longitude'
        ]);

        /* Check general rules */
        $this->validateData($requestData, [
            'latitude' => [
                new Required(),
                new Number(skipOnError: true)
            ],
            'longitude' => [
                new Required(),
                new Number(skipOnError: true)
            ],
        ]);

        return compact("requestData");
    }
}
