<?php

namespace App\Backend\Model;

use AllowDynamicProperties;
use App\Backend\Model\Dealer\DealerModel;
use App\Backend\Model\User\UserDealerPositionModel;
use Yiisoft\ActiveRecord\ActiveQueryInterface;

#[AllowDynamicProperties]
final class RbacAssignmentModel extends \App\Backend\Model\AbstractModel
{
    public function relationQuery(string $name): ActiveQueryInterface
    {
        return match ($name) {
            'userDealerPositions' => $this->hasMany(
                UserDealerPositionModel::class,
                [
                    'userId' => 'user_id',
                    'role' => 'item_name'
                ]
            ),

            'rbacItem' => $this->hasOne(
                RbacItemModel::class,
                [
                    'name' => 'item_name',
                ]
            ),

            default => parent::relationQuery($name),
        };
    }

    public function getUserDealerPositions(): ?array
    {
        return $this->relation('userDealerPositions');
    }

    public function getRbacItem(): ?RbacItemModel
    {
        return $this->relation('rbacItem');
    }
}
