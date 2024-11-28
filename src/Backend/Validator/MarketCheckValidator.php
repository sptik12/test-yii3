<?php

namespace App\Backend\Validator;

use Yiisoft\Validator\Rule\Regex;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Translator\TranslatorInterface;

final class MarketCheckValidator extends AbstractValidator
{
    public function __construct(
        protected TranslatorInterface $translator,
    ) {
        parent::__construct(translator: $translator);
    }

    public function getDataByVinCode(
        string $vinCode
    ): array {
        $data = compact("vinCode");

        /* Check general rules */
        $this->validateData($data, [
            'vinCode' => [
                new Required(),
                new Regex(
                    pattern: "/^[0-9A-Z]{17}$/",
                    message: $this->translator->translate("The VIN number contains 17 characters, including digits and capital letters")
                )
            ]
        ]);

        return [
            'vinCode' => $data['vinCode'],
        ];
    }
}
