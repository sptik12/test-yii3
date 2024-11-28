<?php

namespace App\Backend\Service;

use App\Backend\Component\CarData\CarDataInterface;
use App\Backend\Model\Car\CarModelModel;
use App\Backend\Search\CarModelSearch;
use App\Backend\Search\CarSearch;
use Yiisoft\User\CurrentUser;
use Yiisoft\Injector\Injector;

final class CarModelService extends AbstractService
{
    public function __construct(
        protected CarModelSearch $carModelSearch,
        protected CarSearch $carSearch,
        protected Injector $injector
    ) {
        parent::__construct($injector);
    }

    /**
     * Find
     */
    public function findById(string $id): ?CarModelModel
    {
        return CarModelModel::findOne($id);
    }

    public function findByName(string $name): ?CarModelModel
    {
        return CarModelModel::findOne(['name' => $name]);
    }

    /**
     * Search
     */
    protected function searchModelsForView(
        ?int $makeId,
        string $routeName,
        CurrentUser $currentUser,
    ): array {
        if (!$makeId) {
            return [];
        }

        $baseFilters = $joinsWith = [];

        switch ($routeName) {
            case "client.searchCar":
                $baseFilters = ["active" => true];
                $joinsWith = ["activeDealerOrClient"];
                break;
            case "client.wishlist":
                $baseFilters = ["active" => true];
                $joinsWith = ["carUserCount" => $currentUser->getId()];
                break;
            case "client.myCars":
                $baseFilters = ["clientId" => $currentUser->getId()];
                break;
            case "dealer.searchCar":
                $baseFilters = ["dealerId" => $currentUser->getIdentity()->currentDealerId];
                break;
        }

        return $this->carSearch->searchWithCountByModel(makeId: $makeId, filters: $baseFilters, joinsWith: $joinsWith);
    }

    protected function searchModelsForEdit(
        ?int $makeId
    ): array {
        $models = [];

        if ($makeId) {
            $models = $this->carModelSearch->search(filters: ["make" => $makeId]);

            foreach ($models as &$model) {
                $model = $this->hydrateModelToObject($model);
            }
        }

        return $models;
    }

    /**
     * Methods
     */
    protected function getModel(string $id): object
    {
        return $this->findById($id);
    }

    protected function getModelsForViewFromArray(
        array $requestData,
        CurrentUser $currentUser,
    ): array {
        $makeId = $requestData["makeId"];
        $routeName = $requestData["routeName"];

        return $this->searchModelsForView(makeId: $makeId, routeName: $routeName, currentUser: $currentUser);
    }

    protected function getModelsForEditFromArray(
        array $requestData
    ): array {
        $makeId = $requestData["makeId"];

        return $this->searchModelsForEdit(makeId: $makeId);
    }


    protected function fillModelsTable(
        CarDataInterface $carData,
        CarMakeService $carMakeService
    ): int {
        $makes = $carMakeService->searchMakes();// active filter is not needed here!
        $storedModelsCount = 0;

        foreach ($makes as $make) {
            $modelsNamesInService = $carData->getModels($make->name);
            $models = $this->searchModelsForEdit(makeId: $make->id); // active filter is not needed here!
            $modelsNamesInDb = array_column($models, "name");
            $modelsNamesToStore = array_diff($modelsNamesInService, $modelsNamesInDb);

            foreach ($modelsNamesToStore as $modelName) {
                $model = new CarModelModel();
                $model->name = $modelName;
                $model->makeId = $make->id;
                $model->save();
            }

            $storedModelsCount += count($modelsNamesToStore);
        }

        return $storedModelsCount;
    }
}
