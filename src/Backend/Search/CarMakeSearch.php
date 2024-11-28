<?php

namespace App\Backend\Search;

use Yiisoft\ActiveRecord\ActiveQuery;
use Yiisoft\Arrays\ArrayHelper;
use App\Backend\Model\Car\CarMakeStatus;

final class CarMakeSearch extends AbstractSearch
{
    protected function getDefaultOrder(): array
    {
        return ['carMake.name' => SORT_ASC];
    }



    protected function filterActive(ActiveQuery $query): ActiveQuery
    {
        $query->andWhere(['carMake.status' => CarMakeStatus::Active->value]);

        return $query;
    }
}
