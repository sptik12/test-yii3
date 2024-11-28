<?php

namespace App\Backend\Validator;

use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rule\StringValue;

final class CarModelValidator extends AbstractValidator
{
    public function __construct(
        protected TranslatorInterface $translator,
    ) {
        parent::__construct(translator: $translator);
    }

    public function getModelsForViewFromArray(
        array $requestData
    ): array {
        $requestData = $this->fetchOptional($requestData, ["makeId", "routeName"]);

        /* Check general rules */
        $this->validateData($requestData, [
            'makeId' => [
                new Required(),
                new Integer(skipOnEmpty: true)
            ],
            'routeName' => [
                new Required(),
                new StringValue(skipOnEmpty: true)
            ],
        ]);

        return compact("requestData");
    }

    public function getModelsForEditFromArray(
        array $requestData
    ): array {
        $requestData = $this->fetchOptional($requestData, ["makeId"]);

        /* Check general rules */
        $this->validateData($requestData, [
            'makeId' => [
                new Required(),
                new Integer(skipOnEmpty: true)
            ],
        ]);

        return compact("requestData");
    }
}
