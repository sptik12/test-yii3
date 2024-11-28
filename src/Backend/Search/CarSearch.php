<?php

namespace App\Backend\Search;

use App\Backend\Model\Car\CarModel;
use App\Backend\Model\Car\CarStatus;
use App\Backend\Model\Dealer\DealerStatus;
use App\Backend\Model\User\Status;
use Yiisoft\ActiveRecord\ActiveQuery;
use Yiisoft\Db\Expression\Expression;
use Yiisoft\Strings\Inflector;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\User\CurrentUser;

final class CarSearch extends AbstractSearch
{
    public function __construct(
        private CarMakeSearch $carMakeSearch,
        private CarModelSearch $carModelSearch,
        private readonly TranslatorInterface $translator
    ) {
    }

    public function searchWithCountBy(
        string $filterName,
        array $filters,
        array $joinsWith,
        array $baseFilters = [],
        CurrentUser $currentUser
    ): array {
        switch ($filterName) {
            case "makes":
                // currently filters and joins are not taken into account for makes because withExistingFilters=false
                $filters = ['active' => true];
                $result = $this->searchWithCountByMake($filters);
                break;

            case "makesInWishlist":
                $result = $this->searchWithCountByMakeInWishlist($currentUser->getId());
                break;

            case "makesForDealerCatalog":
                $filters = ['dealer' => $currentUser->getIdentity()->currentDealerId];
                $result = $this->searchWithCountByMake($filters);
                break;

            case "makesForMyCarsCatalog":
                $filters = array_merge(['client' => $currentUser->getId()]);
                $result = $this->searchWithCountByMake($filters);
                break;

            case "feature":
                $result = $this->searchWithCountByFeatures($filters, $joinsWith);
                break;

            default:
                $result = $this->defaultSearchWithCountBy($filterName, $filters, $joinsWith, $baseFilters);
                break;
        }

        return $result;
    }

    public function searchWithCountByModel(
        int $makeId,
        array $filters = [],
        array $joinsWith = []
    ): array {
        $query = $this->find()
            ->select(["car.modelId"])
            ->addSelect("COUNT(car.id) AS countCars")
            ->groupBy(["car.modelId"]);
        $query = $this->applyFilters($query, $filters);
        $query = $this->applyJoins($query, $joinsWith);
        $allItems = $query->asArray()->all();
        $allItems = ArrayHelper::index($allItems, 'modelId');

        $models = $this->carModelSearch->search(filters: ['make' => $makeId]);
        $result = [];

        foreach ($models as $model) {
            if (array_key_exists($model->id, $allItems)) {
                $object = new \stdClass();
                $object->id = $model->id;
                $object->name = $model->name;
                $object->countCars = $allItems[$model->id]['countCars'];
                $result[] = $object;
            }
        }

        return $result;
    }

    public function searchYears(array $makeIds): array
    {
        $query = $this->find()->select(["car.year"]);
        $query = $this->applyFilters($query, ["active" => true]);

        if ($makeIds) {
            $query = $query->andWhere(['or', ["car.makeId" => $makeIds]]);
        }
        $query = $query->distinct()->orderBy(["car.year" => SORT_DESC]);
        $years = $query->column('year');

        return $years;
    }





    protected function getDefaultOrder(): array
    {
        return ['car.created' => SORT_DESC];
    }



    protected function filterActive(ActiveQuery $query): ActiveQuery
    {
        $query->andWhere(['car.status' => CarStatus::Published->value]);

        return $query;
    }

    // Currently this filter is the same as 'active'. May be 'active' will be changed
    protected function filterPublished(ActiveQuery $query): ActiveQuery
    {
        $query->andWhere(['car.status' => CarStatus::Published->value]);

        return $query;
    }

    protected function filterDealer(ActiveQuery $query, ?int $dealerId = null): ActiveQuery
    {
        if ($dealerId) {
            $query->andWhere(['car.dealerId' => $dealerId]);
        }

        return $query;
    }

    protected function filterClient(ActiveQuery $query, ?int $clientId = null): ActiveQuery
    {
        if ($clientId) {
            $query->andWhere(['car.clientId' => $clientId]);
        }

        return $query;
    }

    protected function filterMinYear(ActiveQuery $query, ?int $minYear): ActiveQuery
    {
        if ($minYear) {
            $query->andWhere(['>=', 'car.year', $minYear]);
        }

        return $query;
    }

    protected function filterMaxYear(ActiveQuery $query, ?int $maxYear): ActiveQuery
    {
        if ($maxYear) {
            $query->andWhere(['<=', 'car.year', $maxYear]);
        }

        return $query;
    }

    protected function filterMinPrice(ActiveQuery $query, ?int $minPrice): ActiveQuery
    {
        if ($minPrice) {
            $query->andWhere(['>=', 'car.price', $minPrice]);
        }

        return $query;
    }

    protected function filterMaxPrice(ActiveQuery $query, ?int $maxPrice): ActiveQuery
    {
        if ($maxPrice) {
            $query->andWhere(['<=', 'car.price', $maxPrice]);
        }

        return $query;
    }

    protected function filterMake(ActiveQuery $query, array|int $makesIds): ActiveQuery
    {
        if ($makesIds) {
            $query->andWhere(['car.makeId' => $makesIds]);
        }

        return $query;
    }

    protected function filterModel(ActiveQuery $query, int $modelId): ActiveQuery
    {
        if ($modelId) {
            $query->andWhere(['car.modelId' => $modelId]);
        }

        return $query;
    }

    protected function filterMakeModelPairs(ActiveQuery $query, array $makeModelPairs): ActiveQuery
    {
        if ($makeModelPairs) {
            if (count($makeModelPairs) > 1) {
                $conditions = ['or'];

                foreach ($makeModelPairs as $makeModelPair) {
                    $aMakeModelPair = explode(',', $makeModelPair);
                    $makeId = (int)($aMakeModelPair[0]);

                    if ($makeId) {
                        if (count($aMakeModelPair) > 1) {
                            $modelId = (int)($aMakeModelPair[1]);

                            if ($modelId) {
                                $conditions[] = ["and", ["car.makeId" => $makeId], ["car.modelId" => $modelId]];
                            } else {
                                $conditions[] = ["car.makeId" => $makeId];
                            }
                        } else {
                            $conditions[] = ["car.makeId" => $makeId];
                        }
                    }
                }

                $query->andWhere($conditions);
            } else {
                $makeModelPair = $makeModelPairs[0];
                $aMakeModelPair = explode(',', $makeModelPair);
                $makeId = (int)($aMakeModelPair[0]);

                if ($makeId) {
                    $query->andWhere(["car.makeId" => $makeId]);
                }

                if (count($aMakeModelPair) > 1) {
                    $modelId = (int)($aMakeModelPair[1]);

                    if ($modelId) {
                        $query->andWhere(["car.modelId" => $modelId]);
                    }
                }
            }
        }

        return $query;
    }

    protected function filterBodyType(ActiveQuery $query, array|int $bodyType): ActiveQuery
    {
        if ($bodyType) {
            $query->andWhere(['car.bodyType' => $bodyType]);
        }

        return $query;
    }

    protected function filterCondition(ActiveQuery $query, string $condition): ActiveQuery
    {
        if ($condition) {
            $query->andWhere(['car.condition' => $condition]);
        }

        return $query;
    }

    protected function filterMinMileage(ActiveQuery $query, ?int $minMileage): ActiveQuery
    {
        if ($minMileage) {
            $query->andWhere(['>=', 'car.mileage', $minMileage]);
        }

        return $query;
    }

    protected function filterMaxMileage(ActiveQuery $query, ?int $maxMileage): ActiveQuery
    {
        if ($maxMileage) {
            $query->andWhere(['<=', 'car.mileage', $maxMileage]);
        }

        return $query;
    }

    protected function filterMinEngine(ActiveQuery $query, ?int $minEngine): ActiveQuery
    {
        if ($minEngine) {
            $query->andWhere(['>=', 'car.engine', $minEngine]);
        }

        return $query;
    }

    protected function filterMaxEngine(ActiveQuery $query, ?int $maxEngine): ActiveQuery
    {
        if ($maxEngine) {
            $query->andWhere(['<=', 'car.engine', $maxEngine]);
        }

        return $query;
    }

    protected function filterMinFuelEconomy(ActiveQuery $query, ?int $minFuelEconomy): ActiveQuery
    {
        if ($minFuelEconomy) {
            $query->andWhere(['>=', 'car.fuelEconomy', $minFuelEconomy]);
        }

        return $query;
    }

    protected function filterMaxFuelEconomy(ActiveQuery $query, ?int $maxFuelEconomy): ActiveQuery
    {
        if ($maxFuelEconomy) {
            $query->andWhere(['<=', 'car.fuelEconomy', $maxFuelEconomy]);
        }

        return $query;
    }

    protected function filterMinCo2(ActiveQuery $query, ?int $minCo2): ActiveQuery
    {
        if ($minCo2) {
            $query->andWhere(['>=', 'car.co2', $minCo2]);
        }

        return $query;
    }

    protected function filterMaxCo2(ActiveQuery $query, ?int $maxCo2): ActiveQuery
    {
        if ($maxCo2) {
            $query->andWhere(['<=', 'car.co2', $maxCo2]);
        }

        return $query;
    }

    protected function filterMinEvBatteryRange(ActiveQuery $query, ?int $minEvBatteryRange): ActiveQuery
    {
        if ($minEvBatteryRange) {
            $query->andWhere(['>=', 'car.evBatteryRange', $minEvBatteryRange]);
        }

        return $query;
    }

    protected function filterMaxEvBatteryRange(ActiveQuery $query, ?int $maxEvBatteryRange): ActiveQuery
    {
        if ($maxEvBatteryRange) {
            $query->andWhere(['<=', 'car.evBatteryRange', $maxEvBatteryRange]);
        }

        return $query;
    }

    protected function filterMinEvBatteryTime(ActiveQuery $query, ?int $minEvBatteryTime): ActiveQuery
    {
        if ($minEvBatteryTime) {
            $query->andWhere(['>=', 'car.evBatteryTime', $minEvBatteryTime]);
        }

        return $query;
    }

    protected function filterMaxEvBatteryTime(ActiveQuery $query, ?int $maxEvBatteryTime): ActiveQuery
    {
        if ($maxEvBatteryTime) {
            $query->andWhere(['<=', 'car.evBatteryTime', $maxEvBatteryTime]);
        }

        return $query;
    }

    protected function filterMinDaysOnMarket(ActiveQuery $query, ?int $minDaysOnMarket): ActiveQuery
    {
        if ($minDaysOnMarket) {
            $query->andWhere(new Expression("DATEDIFF(CURRENT_DATE, published) >= {$minDaysOnMarket}"));
        }

        return $query;
    }

    protected function filterMaxDaysOnMarket(ActiveQuery $query, ?int $maxDaysOnMarket): ActiveQuery
    {
        if ($maxDaysOnMarket) {
            $query->andWhere(new Expression("DATEDIFF(CURRENT_DATE, published) <= {$maxDaysOnMarket}"));
        }

        return $query;
    }

    protected function filterFeature(ActiveQuery $query, array $features): ActiveQuery
    {
        if ($features) {
            $encoded = json_encode($features);
            $query->andWhere(new Expression("JSON_CONTAINS(car.features, '{$encoded}') = 1"));
        }

        return $query;
    }

    protected function filterTransmission(ActiveQuery $query, array $transmission): ActiveQuery
    {
        if ($transmission) {
            $query->andWhere(['car.transmission' => $transmission]);
        }

        return $query;
    }

    protected function filterDrivetrain(ActiveQuery $query, array $drivetrain): ActiveQuery
    {
        if ($drivetrain) {
            $query->andWhere(['car.drivetrain' => $drivetrain]);
        }

        return $query;
    }

    protected function filterFuelType(ActiveQuery $query, array $fuelType): ActiveQuery
    {
        if ($fuelType) {
            $query->andWhere(['car.fuelType' => $fuelType]);
        }

        return $query;
    }

    protected function filterDoors(ActiveQuery $query, array $doors): ActiveQuery
    {
        if ($doors) {
            $query->andWhere(['car.doors' => $doors]);
        }

        return $query;
    }

    protected function filterSeats(ActiveQuery $query, array $seats): ActiveQuery
    {
        if ($seats) {
            $query->andWhere(['car.seats' => $seats]);
        }

        return $query;
    }

    protected function filterBedSize(ActiveQuery $query, array $bedSize): ActiveQuery
    {
        if ($bedSize) {
            $query->andWhere(['car.bedSize' => $bedSize]);
        }

        return $query;
    }

    protected function filterCabinSize(ActiveQuery $query, array $cabinSize): ActiveQuery
    {
        if ($cabinSize) {
            $query->andWhere(['car.cabinSize' => $cabinSize]);
        }

        return $query;
    }

    protected function filterIntColor(ActiveQuery $query, array $intColor): ActiveQuery
    {
        if ($intColor) {
            $query->andWhere(['car.intColor' => $intColor]);
        }

        return $query;
    }

    protected function filterExtColor(ActiveQuery $query, array $extColor): ActiveQuery
    {
        if ($extColor) {
            $query->andWhere(['car.extColor' => $extColor]);
        }

        return $query;
    }

    protected function filterSafetyRating(ActiveQuery $query, array $safetyRating): ActiveQuery
    {
        if ($safetyRating) {
            $query->andWhere(['car.safetyRating' => $safetyRating]);
        }

        return $query;
    }

    protected function filterCertifiedPreOwned(ActiveQuery $query): ActiveQuery
    {
        $query->andWhere(['car.certifiedPreOwned' => 1]);

        return $query;
    }

    protected function filterPriceDrops(ActiveQuery $query): ActiveQuery
    {
        $query->andWhere(['car.priceDrops' => 1]);

        return $query;
    }

    protected function filterWithPhotos(ActiveQuery $query): ActiveQuery
    {
        $query->joinWith("carMediaMain")->andWhere(['carMedia.isMain' => 1]);

        return $query;
    }

    protected function filterPostalCode(ActiveQuery $query, string $postalCode): ActiveQuery
    {
        if ($postalCode) {
            // such code cause incorrect sql query, Yii3 bug
            // $query->andWhere(['like', "dealer.postalCode", "{$postalCode}%", false]);
            $query->andWhere("dealer.postalCode like '{$postalCode}%'");
        }

        return $query;
    }

    protected function filterDistance(ActiveQuery $query, array $params): ActiveQuery
    {
        $distance = $params["distance"];
        $longitude = $params["longitude"];
        $latitude = $params["latitude"];

        if ($distance && $distance <= 1000000) {
            $query->andWhere(
                [
                    '<=',
                    "ST_Distance_Sphere(point({$longitude}, {$latitude}), point(`car`.`longitude`, `car`.`latitude`))",
                    $distance
                ]
            );
        }

        return $query;
    }

    protected function filterProvince(ActiveQuery $query, string $province): ActiveQuery
    {
        if ($province) {
            $query->andWhere(["car.province" => $province]);
        }

        return $query;
    }

    protected function filterStatus(ActiveQuery $query, array $statuses): ActiveQuery
    {
        if ($statuses) {
            $query->andWhere(['car.status' => $statuses]);
        }

        return $query;
    }

    protected function filterPrevNext(ActiveQuery $query, array $params): ActiveQuery
    {
        $field = $params["field"];
        $lessOrGreaterValue = $params["lessOrGreaterValue"];
        $lessOrGreaterId = $params["lessOrGreaterId"];
        $value = $params["value"];
        $currentId = $params["currentId"];

        if ($field == 'distance') {
            $longitude = $params["longitude"];
            $latitude = $params["latitude"];
            $field = "ST_Distance_Sphere(point({$longitude}, {$latitude}),point(`car`.`longitude`, `car`.`latitude`))";
        }

        $query->andWhere([
            'or',
            [$lessOrGreaterValue, $field, $value],
            ['and', [$field => $value], [$lessOrGreaterId, "car.id", $currentId]]
        ]);

        return $query;
    }


    protected function joinMake(ActiveQuery $query): ActiveQuery
    {
        $query->addSelect(["car.makeId"])->joinWith("make");

        return $query;
    }

    protected function joinModel(ActiveQuery $query): ActiveQuery
    {
        $query->addSelect(["car.modelId"])->joinWith("model");

        return $query;
    }

    protected function joinMediaMain(ActiveQuery $query): ActiveQuery
    {
        $query->joinWith("carMediaMain");

        return $query;
    }

    protected function joinActiveDealer(ActiveQuery $query): ActiveQuery
    {
        $query->joinWith(["dealer"])->andWhere(['dealer.status' => DealerStatus::Active->value]);

        return $query;
    }

    protected function joinDealer(ActiveQuery $query): ActiveQuery
    {
        $query->addSelect(["car.dealerId"])->joinWith(["dealer"]);

        return $query;
    }

    protected function joinActiveDealerOrClient(ActiveQuery $query): ActiveQuery
    {
        $query->joinWith(["dealer", "client"])->andWhere(['or', ['dealer.status' => DealerStatus::Active->value], ['user.status' => Status::Active->value]]);

        return $query;
    }

    protected function joinCarUser(ActiveQuery $query, array $params): ActiveQuery
    {
        $userId = $params["userId"];
        $joinType = $params["joinType"];

        if ($userId) {
            $query
                ->addSelect(["IF(carUser.id IS NULL, 0, 1) AS isCarSaved"])
                ->joinWith(
                    ["carUser" => function (ActiveQuery $query) use ($userId) { $query->onCondition(['carUser.userId' => $userId]);}],
                    eagerLoading: false,
                    joinType: $joinType
                );
        }

        return $query;
    }

    protected function joinCarUserCount(ActiveQuery $query, int $userId): ActiveQuery
    {
        if ($userId) {
            $query
                ->joinWith(
                    ["carUser" => function (ActiveQuery $query) use ($userId) { $query->onCondition(['carUser.userId' => $userId]);}],
                    eagerLoading: false,
                    joinType: "INNER JOIN"
                );
        }

        return $query;
    }





    private function defaultSearchWithCountBy(string $filterName, array $filters, array $joinsWith, array $basefilters = []): array
    {
        /* Build query */
        $field = $filterName;
        $query = $this->find()
            ->select(["car.{$field}"])
            ->addSelect("COUNT(car.id) AS countCars")
            ->groupBy(["car.{$field}"]);
        $query = $this->applyFilters($query, $basefilters);

        $allItems = $query->asArray()->all();
        $allItems = ArrayHelper::index($allItems, $field);

        /* Get filters without the specified filter */
        $filters = array_diff_key($filters, [$filterName => true]);
        $query = $this->applyFilters($query, $filters);
        $query = $this->applyJoins($query, $joinsWith);
        $items = $query->asArray()->all();
        $items = array_map(fn($item) => (object)$item, $items);

        return $this->prepareDataForSearchWithCountBy($filterName, $items, $allItems);
    }

    private function prepareDataForSearchWithCountBy(string $filterName, array $items, array $allItems): array
    {
        $result = [];

        switch ($filterName) {
            case "doors":
                for ($i = CarModel::MIN_DOORS; $i <= CarModel::MAX_DOORS; $i++) {
                    if (array_key_exists($i, $allItems)) {
                        $object = new \stdClass();
                        $object->id = "d{$i}";
                        $object->value = $i;
                        $object->name = $i;
                        $object->countCars = 0;

                        foreach ($items as $item) {
                            if ($item->doors == $i) {
                                $object->countCars = $item->countCars;
                                break;
                            }
                        }

                        $result[] = $object;
                    }
                }
                break;

            case "seats":
                for ($i = CarModel::MIN_SEATS; $i <= 6; $i++) {
                    if (array_key_exists($i, $allItems)) {
                        $object = new \stdClass();
                        $object->id = "s{$i}";
                        $object->value = $i;
                        $object->name = ($i >= 6) ? "6+" : $i;
                        $object->countCars = 0;

                        foreach ($items as $item) {
                            if ($item->seats == $i) {
                                $object->countCars = $item->countCars;
                                break;
                            }
                        }

                        $result[] = $object;
                    }
                }
                break;

            default:
                $enumName = match ($filterName) {
                    "status" => "CarStatus",
                    default => (new Inflector())->toPascalCase($filterName)
                };
                $enumClass = "\App\Backend\Model\Car\\{$enumName}";
                $field = match ($filterName) {
                    default => $filterName
                };

                foreach ($enumClass::cases() as $enumCase) {
                    if (array_key_exists($enumCase->value, $allItems)) {
                        $object = new \stdClass();
                        $object->id = $enumCase->value;
                        $object->name = $enumCase->title($this->translator);
                        $object->countCars = 0;

                        if ($filterName == 'bodyType') {
                            $object->picture = $enumCase->picture();
                            $object->iconHeight = $enumCase->iconHeight();
                            $object->iconWidth = $enumCase->iconWidth();
                        }

                        foreach ($items as $item) {
                            if ($item->{$field} == $enumCase->value) {
                                $object->countCars = $item->countCars;
                                break;
                            }
                        }

                        $result[] = $object;
                    }
                }
                break;
        }

        return $result;
    }

    private function searchWithCountByMake(array $filters): array
    {
        $query = $this->find()
            ->select(["car.makeId"])
            ->addSelect("COUNT(car.id) AS countCars")
            ->groupBy(["car.makeId"]);
        $query = $this->applyFilters($query, $filters);
        $allItems = $query->asArray()->all();
        $allItems = ArrayHelper::index($allItems, 'makeId');

        $makes = $this->carMakeSearch->search(filters: ['active' => true]);
        $result = [];

        foreach ($makes as $make) {
            if (array_key_exists($make->id, $allItems)) {
                $object = new \stdClass();
                $object->id = $make->id;
                $object->name = $make->name;
                $object->countCars = $allItems[$make->id]['countCars'];
                $result[] = $object;
            }
        }

        return $result;
    }

    private function searchWithCountByMakeInWishlist(int $userId): array
    {
        $query = $this->find()
            ->select(["car.makeId"])
            ->addSelect("COUNT(car.id) AS countCars")
            ->groupBy(["car.makeId"]);
        $query = $this->applyJoins($query, ["carUserCount" => $userId]);

        $allItems = $query->asArray()->all();
        $allItems = ArrayHelper::index($allItems, 'makeId');
        $makes = $this->carMakeSearch->search(filters: ['active' => true]);
        $result = [];

        foreach ($makes as $make) {
            if (array_key_exists($make->id, $allItems)) {
                $object = new \stdClass();
                $object->id = $make->id;
                $object->name = $make->name;
                $object->countCars = $allItems[$make->id]['countCars'];
                $result[] = $object;
            }
        }

        return $result;
    }

    private function searchWithCountByFeatures(array $filters, array $joinsWith): array
    {
        $query = $this->find()->select(["jsonFeatures.feature"])->addSelect("COUNT(car.id) AS countCars");
        $query->join('JOIN', "JSON_TABLE(car.features, '$[*]' COLUMNS (feature VARCHAR(255) PATH '$')) jsonFeatures");
        $query = $this->applyFilters($query, ["active" => true]);
        $query->groupBy(["jsonFeatures.feature"]);
        $allItems = $query->asArray()->all();
        $allItems = ArrayHelper::index($allItems, 'feature');

        $filters = array_diff_key($filters, ['feature' => true]);
        $query = $this->applyFilters($query, $filters);
        $query = $this->applyJoins($query, $joinsWith);
        $items = $query->asArray()->all();
        $items = array_map(fn($item) => (object)$item, $items);

        return $this->prepareDataForSearchWithCountBy('feature', $items, $allItems);
    }
}
