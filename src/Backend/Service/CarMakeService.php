<?php

namespace App\Backend\Service;

use App\Backend\Model\Car\CarMakeModel;
use App\Backend\Search\CarMakeSearch;
use App\Backend\Component\CarData\CarDataInterface;
use Yiisoft\Injector\Injector;

final class CarMakeService extends AbstractService
{
    public function __construct(
        protected CarMakeSearch $carMakeSearch,
        protected Injector $injector
    ) {
        parent::__construct($injector);
    }

    /**
     * Find
     */
    public function findById(string $id): ?CarMakeModel
    {
        return CarMakeModel::findOne($id);
    }

    public function findByName(string $name): ?CarMakeModel
    {
        return CarMakeModel::findOne(['name' => $name]);
    }

    /**
     * Search
     */
    protected function searchMakes(
        array $filters = []
    ): array {
        $makes = $this->carMakeSearch->search(filters: $filters);

        foreach ($makes as &$make) {
            $make = $this->hydrateModelToObject($make);
        }

        return $makes;
    }

    /**
     * Methods
     */

    protected function fillMakesTable(
        CarDataInterface $carData
    ): int {
        $makesNamesInService = $carData->getAllMakes();
        $makes = $this->searchMakes(); // active filter is not needed here!
        $makesNamesInDb = array_column($makes, "name");
        $makesNamesToStore = array_diff($makesNamesInService, $makesNamesInDb);

        foreach ($makesNamesToStore as $makeName) {
            $make = new CarMakeModel();
            $make->name = $makeName;
            $make->save();
        }

        return count($makesNamesToStore);
    }
}
