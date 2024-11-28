<?php

namespace App\Backend\Search;

use Yiisoft\ActiveRecord\ActiveQuery;
use App\Backend\Model\Car\CarModelStatus;

final class CarModelSearch extends AbstractSearch
{
    protected function getDefaultOrder(): array
    {
        return ['carModel.name' => SORT_ASC];
    }



    protected function filterActive(ActiveQuery $query): ActiveQuery
    {
        $query->andWhere(['carModel.status' => CarModelStatus::Active->value]);

        return $query;
    }

    protected function filterMake(ActiveQuery $query, int $makeId): ActiveQuery
    {
        $query->andWhere(['carModel.makeId' => $makeId]);

        return $query;
    }
}
