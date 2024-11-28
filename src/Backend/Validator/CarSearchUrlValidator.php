<?php

namespace App\Backend\Validator;

use App\Backend\Model\Car\CarSearchUrlModel;
use Yiisoft\User\CurrentUser;
use Yiisoft\Translator\TranslatorInterface;
use App\Backend\Exception\Http\ForbiddenException;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;

final class CarSearchUrlValidator extends AbstractValidator
{
    public function __construct(
        protected TranslatorInterface $translator
    ) {
        parent::__construct(translator: $translator);
    }

    public function addCarSearchUrlFromArray(
        array $requestData,
        CurrentUser $currentUser
    ): array {
        $requestData = $this->fetchOptional(
            $requestData,
            [
                "title",
                "url",
                "filters"
            ]
        );

        $this->validateData($requestData, [
            'title' => [
                new Required(),
                new Length(max: 512, skipOnError: true),
            ],
            'url' => [
                new Required(),
                new Length(max: 2048, skipOnError: true),
            ],
            'filters' => [
                new Required()
            ]
        ]);

        /* Check specific rules */
        $this->validateIsLogged($currentUser);

        $requestData["userId"] = $currentUser->getId();

        $isExists = CarSearchUrlModel::find()->where(["url" => $requestData["url"], "userId" => $requestData["userId"]])->exists();

        if ($isExists) {
            $this->throwValidationException('url', $this->translator->translate("You have already saved search with these filters to your wishlist"));
        }

        return ["requestData" => $requestData];
    }

    public function updateCarSearchUrlFromArray(
        array $requestData,
        CurrentUser $currentUser
    ): array {
        $requestData = $this->fetchOptional(
            $requestData,
            [
                "id",
                "title",
                "url",
                "filters"
            ]
        );

        $this->validateData($requestData, [
            'id' => [
                new Required(),
                new Integer(min: 1, skipOnError: true),
            ],
            'title' => [
                new Required(),
                new Length(max: 512, skipOnError: true),
            ],
            'url' => [
                new Required(),
                new Length(max: 2048, skipOnError: true),
            ],
            'filters' => [
                new Required()
            ]
        ]);

        /* Check specific rules */
        $this->validateIsLogged($currentUser);
        $carSearchUrlModel = $this->validateIsCarSearchExists($requestData["id"]);
        $this->validatePermissions($carSearchUrlModel, $currentUser);

        return ["requestData" => $requestData];
    }

    public function removeCarSearchUrlFromArray(
        array $requestData,
        CurrentUser $currentUser
    ): array {
        $requestData = $this->fetchOptional(
            $requestData,
            [
                "id",
            ]
        );

        $this->validateData($requestData, [
            'id' => [
                new Required(),
                new Integer(min: 1, skipOnError: true),
            ],
        ]);

        /* Check specific rules */
        $this->validateIsLogged($currentUser);
        $carSearchUrlModel = $this->validateIsCarSearchExists($requestData["id"]);
        $this->validatePermissions($carSearchUrlModel, $currentUser);

        return ["requestData" => $requestData];
    }

    public function deleteCarSearchUrlFromArray(
        array $requestData,
        CurrentUser $currentUser
    ): array {
        $requestData = $this->fetchOptional(
            $requestData,
            [
                "id",
            ]
        );

        $this->validateData($requestData, [
            'id' => [
                new Required(),
                new Integer(min: 1, skipOnError: true),
            ],
        ]);

        /* Check specific rules */
        $this->validateIsLogged($currentUser);
        $carSearchUrlModel = $this->validateIsCarSearchExists($requestData["id"]);
        $this->validatePermissions($carSearchUrlModel, $currentUser);

        return ["requestData" => $requestData];
    }

    public function restoreCarSearchUrlFromArray(
        array $requestData,
        CurrentUser $currentUser
    ): array {
        $requestData = $this->fetchOptional(
            $requestData,
            [
                "id",
            ]
        );

        $this->validateData($requestData, [
            'id' => [
                new Required(),
                new Integer(min: 1, skipOnError: true),
            ],
        ]);

        /* Check specific rules */
        $this->validateIsLogged($currentUser);
        $carSearchUrlModel = $this->validateIsCarSearchExists($requestData["id"]);
        $this->validatePermissions($carSearchUrlModel, $currentUser);

        return ["requestData" => $requestData];
    }

    public function checkCarSearchUrlFromArray(
        array $requestData,
        CurrentUser $currentUser
    ): array {
        $requestData = $this->fetchOptional(
            $requestData,
            [
                "url",
            ]
        );

        $this->validateData($requestData, [
            'url' => [
                new Required(),
                new Length(max: 2048, skipOnError: true),
            ],
        ]);

        /* Check specific rules */
        $this->validateIsLogged($currentUser);

        $requestData["userId"] = $currentUser->getId();

        return ["requestData" => $requestData];
    }

    public function searchCarSearchUrls(
        array $filters,
        string $sort,
        string $sortOrder,
        int $page,
        int $perPage,
        CurrentUser $currentUser
    ): array {
        /* Check specific rules */
        $this->validateIsLogged($currentUser);

        return compact("filters", "sort", "sortOrder", "page", "perPage");
    }





    private function validateIsCarSearchExists(int $id): ?CarSearchUrlModel
    {
        $carSearchUrlModel = CarSearchUrlModel::findOne($id);

        if (!$carSearchUrlModel) {
            $this->throwValidationException('url', $this->translator->translate("This search don't exists"));
        }

        return $carSearchUrlModel;
    }

    private function validatePermissions(CarSearchUrlModel $carSearchUrlModel, CurrentUser $currentUser)
    {
        if ($carSearchUrlModel->userId != $currentUser->getId()) {
            throw new ForbiddenException('id', $this->translator->translate("You have no rights to manage this search"));
        }
    }
}
