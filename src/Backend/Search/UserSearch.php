<?php

namespace App\Backend\Search;

use Yiisoft\ActiveRecord\ActiveQuery;
use App\Backend\Model\User\UserDealerPositionModel;
use App\Backend\Model\User\Status;
use Yiisoft\Db\Expression\Expression;
use Yiisoft\Router\CurrentRoute;

final class UserSearch extends AbstractSearch
{
    protected function getDefaultOrder(): array
    {
        return ['user.email' => SORT_ASC, 'user.username' => SORT_ASC];
    }




    protected function filterActive(ActiveQuery $query): ActiveQuery
    {
        $query->andWhere(['user.status' => Status::Active->value]);

        return $query;
    }

    protected function filterDealer(ActiveQuery $query, ?int $dealerId = null): ActiveQuery
    {
        if ($dealerId) {
            $usersIds = UserDealerPositionModel::find()->where(['dealerId' => $dealerId])->column();

            if (count($usersIds)) {
                $query->andWhere(['user.id' => $usersIds]);
            } else {
                $query->andWhere(['user.id' => null]);
            }
        }

        return $query;
    }

    protected function filterTableTyped(ActiveQuery $query, string $searchQuery): ActiveQuery
    {
        $searchQueryExpression = new Expression(":query", ['query' => "%{$searchQuery}%"]);
        $query->andWhere([
            "OR",
            ['like', 'user.username', $searchQueryExpression],
            ['like', 'user.email', $searchQueryExpression],
        ]);

        return $query;
    }

    protected function filterRegistered(ActiveQuery $query, array $dates): ActiveQuery
    {
        $dateFrom = $dates[0] ?? null;
        $dateTo = $dates[1] ?? null;

        if ($dateFrom) {
            $query->andWhere(['>=', 'user.created', $dateFrom]);
        }

        if ($dateTo) {
            $query->andWhere(['<=', 'user.created', $dateTo]);
        }

        return $query;
    }

    protected function filterStatus(ActiveQuery $query, string $status): ActiveQuery
    {
        if (!empty($status)) {
            $query->andWhere(['user.status' => $status]);
        }

        return $query;
    }

    protected function filterRole(ActiveQuery $query, string $role): ActiveQuery
    {
        if (!empty($role)) {
            $query->andWhere(['`rbacAssignment`.`item_name`' => $role]);
        }

        return $query;
    }

    protected function filterDeletionDate(ActiveQuery $query, ?int $isDeletedOnly): ActiveQuery
    {
        if ($isDeletedOnly) {
            $query->andWhere('`user`.`deletionDate` IS NOT NULL');
        }

        return $query;
    }


    protected function sortByRolesList(ActiveQuery $query, string $sortColumn, int $sortOrder): ActiveQuery
    {
        $query->addOrderBy(['roles' => $sortOrder]);

        return $query;
    }

    protected function joinRolesList(ActiveQuery $query): ActiveQuery
    {
        $query
            ->addSelect("GROUP_CONCAT(`rbacAssignment`.`item_name`) as `roles`")
            ->joinWith(["rbacAssignments.userDealerPositions"], eagerLoading: false)
            ->groupBy("user.id");

        return $query;
    }

    protected function joinUserDealersList(ActiveQuery $query): ActiveQuery
    {
        $query
            ->addSelect("GROUP_CONCAT(`dealer`.`name`) as `dealers`")
            ->joinWith("userDealerPosition.dealer", eagerLoading: false)
            ->groupBy("user.id");

        return $query;
    }

    protected function joinUserDealerPosition(ActiveQuery $query, int $dealerId): ActiveQuery
    {
        $query
            ->addSelect(["userDealerPosition.dealerId", "userDealerPosition.role"])
            ->joinWith(
                [
                    "userDealerPosition" => function (ActiveQuery $query) use ($dealerId) { $query->onCondition(['dealerId' => $dealerId]); }
                ],
                eagerLoading: false,
                joinType: " INNER JOIN"
            );

        return $query;
    }

    protected function joinRoles(ActiveQuery $query): ActiveQuery
    {
        $query
            ->addSelect(["`rbacAssignment`.`item_name` as `role`", "userDealerPosition.dealerId", "dealer.name as dealer"])
            ->joinWith("rbacAssignments.userDealerPositions.dealer", eagerLoading: false)
            ->andWhere("`rbacAssignment`.`item_name` IS NOT NULL");

        return $query;
    }

    protected function joinRole(ActiveQuery $query, string $role): ActiveQuery
    {
        $query
            ->addSelect(["`rbacAssignment`.`item_name` as `role`"])
            ->joinWith(
                [
                    "rbacAssignments" => function (ActiveQuery $query) use ($role) { $query->onCondition(['rbacAssignment.item_name' => $role]); }
                ],
                eagerLoading: false,
                joinType: "INNER JOIN"
            );

        return $query;
    }
}
