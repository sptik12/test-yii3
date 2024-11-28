<?php

namespace App\Backend\Model\User;

use AllowDynamicProperties;
use App\Backend\Model\Dealer\DealerModel;
use Yiisoft\ActiveRecord\ActiveQueryInterface;

#[AllowDynamicProperties]
final class UserDealerPositionModel extends \App\Backend\Model\AbstractModel
{
    public function relationQuery(string $name): ActiveQueryInterface
    {
        return match ($name) {
            'dealer' => $this->hasOne(DealerModel::class, ['id' => 'dealerId']),
            'user' => $this->hasOne(UserModel::class, ['id' => 'userId']),
        };
    }

    public function getDealer(): ?DealerModel
    {
        return $this->relation('dealer');
    }

    public function getUser(): ?UserModel
    {
        return $this->relation('user');
    }
}
