<?php

namespace App\Backend\Search;

use Yiisoft\ActiveRecord\ActiveQuery;
use Yiisoft\Db\Expression\Expression;

final class UserDealerPositionSearch extends AbstractSearch
{
    public function getUserDealershipsWithLastMember(
        int $userId
    ): array {
        return $this->find()
            ->select(["userDealerPosition.dealerId as id", "dealer.name"])
            ->joinWith(["dealer"])
            ->where([
                "userDealerPosition.userId" => $userId
            ])
            ->andWhere(["in", "userDealerPosition.dealerId",
                $this->find()->select(["userDealerPosition.dealerId"])->groupBy(["userDealerPosition.dealerId"])->having("COUNT(*) = 1")->column()
            ])->asArray()->all();
    }





    protected function filterDealer(ActiveQuery $query, int $dealerId): ActiveQuery
    {
        if ($dealerId) {
            $query->andWhere(['userDealerPosition.dealerId' => $dealerId]);
        }

        return $query;
    }

    protected function filterUser(ActiveQuery $query, int $userId): ActiveQuery
    {
        if ($userId) {
            $query->andWhere(['userDealerPosition.userId' => $userId]);
        }

        return $query;
    }

    protected function joinUser(ActiveQuery $query): ActiveQuery
    {
        $query->addSelect(["user.email", "user.username", "user.status"])->joinWith(["user"]);

        return $query;
    }

    protected function joinDealer(ActiveQuery $query): ActiveQuery
    {
        $query->addSelect(["dealer.name"])->joinWith(["dealer"]);

        return $query;
    }
}
