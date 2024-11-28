<?php

namespace App\Backend\Search;

use App\Backend\Model\Dealer\DealerStatus;
use Yiisoft\ActiveRecord\ActiveQuery;
use Yiisoft\Db\Expression\Expression;

final class DealerSearch extends AbstractSearch
{
    protected function getDefaultOrder(): array
    {
        return ['dealer.name' => SORT_ASC];
    }


    protected function filterActive(ActiveQuery $query): ActiveQuery
    {
        $query->andWhere(['dealer.status' => DealerStatus::Active->value]);

        return $query;
    }

    protected function filterTableTyped(ActiveQuery $query, string $searchQuery): ActiveQuery
    {
        $searchQueryExpression = new Expression(":query", ['query' => "%{$searchQuery}%"]);
        $query->andWhere([
            "OR",
            ['like', 'dealer.name', $searchQueryExpression],
            ['like', 'dealer.businessNumber', $searchQueryExpression],
            ['like', 'dealer.province', $searchQueryExpression],
            ['like', 'dealer.address', $searchQueryExpression],
            ['like', 'dealer.website', $searchQueryExpression],
            ['like', 'dealer.postalCode', $searchQueryExpression],
        ]);

        return $query;
    }

    protected function filterRegistered(ActiveQuery $query, array $dates): ActiveQuery
    {
        $dateFrom = $dates[0] ?? null;
        $dateTo = $dates[1] ?? null;

        if ($dateFrom) {
            $query->andWhere(['>=', 'dealer.created', $dateFrom]);
        }

        if ($dateTo) {
            $query->andWhere(['<=', 'dealer.created', $dateTo]);
        }

        return $query;
    }

    protected function filterStatus(ActiveQuery $query, string $status): ActiveQuery
    {
        if (!empty($status)) {
            $query->andWhere(['dealer.status' => $status]);
        }

        return $query;
    }

    protected function filterAccountManager(ActiveQuery $query, int|string $accountManagerId): ActiveQuery
    {
        if (!empty($accountManagerId)) {
            $query->andWhere(['dealer.accountManagerId' => $accountManagerId]);
        }

        return $query;
    }

    protected function joinAccountManager(ActiveQuery $query): ActiveQuery
    {
        $query->addSelect(["user.username as accountManagerName"])->joinWith("accountManager", eagerLoading: false);

        return $query;
    }


    protected function sortByGeo(ActiveQuery $query, string $sortColumn, int $sortOrder): ActiveQuery
    {
        $query->addOrderBy([
            'longitude' => $sortOrder,
            'latitude' => $sortOrder,
        ]);

        return $query;
    }
}
