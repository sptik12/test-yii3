<?php

namespace App\Backend\Model\Car;

use AllowDynamicProperties;
use App\Backend\Model\User\UserModel;
use Yiisoft\ActiveRecord\ActiveQueryInterface;

#[AllowDynamicProperties]
final class CarUserModel extends \App\Backend\Model\AbstractModel
{
    public function relationQuery(string $name): ActiveQueryInterface
    {
        return match ($name) {
            'car' => $this->hasOne(CarModel::class, ['id' => 'carId']),
            'user' => $this->hasOne(UserModel::class, ['id' => 'userId']),
            default => parent::relationQuery($name),
        };
    }

    public function getCar(): ?CarModel
    {
        return $this->relation('car');
    }

    public function getUser(): ?UserModel
    {
        return $this->relation('user');
    }
}
