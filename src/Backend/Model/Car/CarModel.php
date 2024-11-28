<?php

namespace App\Backend\Model\Car;

use AllowDynamicProperties;
use App\Backend\Model\Dealer\DealerModel;
use App\Backend\Model\User\UserModel;
use Yiisoft\ActiveRecord\ActiveQueryInterface;

#[AllowDynamicProperties]
final class CarModel extends \App\Backend\Model\AbstractModel
{
    const MIN_DOORS = 2;
    const MAX_DOORS = 5;

    const MIN_SEATS = 2;

    const DEFAULT_IMAGE = "/uploads/cars/default.svg";

    public function relationQuery(string $name): ActiveQueryInterface
    {
        return match ($name) {
            'dealer' => $this->hasOne(DealerModel::class, ['id' => 'dealerId']),
            'client' => $this->hasOne(UserModel::class, ['id' => 'clientId']),
            'make' => $this->hasOne(CarMakeModel::class, ['id' => 'makeId']),
            'model' => $this->hasOne(CarModelModel::class, ['id' => 'modelId']),
            'carMedias' => $this->hasMany(CarMediaModel::class, ['carId' => 'id'])
                ->orderBy(["orderType" => SORT_ASC, "order" => SORT_ASC]),
            'carMediasActive' => $this->hasMany(CarMediaModel::class, ['carId' => 'id'])
                ->onCondition(['carMedia.status' => CarMediaStatus::Active->value])
                ->orderBy(["orderType" => SORT_ASC, "order" => SORT_ASC]),
            'carMediaMain' => $this->hasOne(CarMediaModel::class, ['carId' => 'id'])->onCondition(['carMedia.isMain' => 1]),
            'carUser' => $this->hasOne(CarUserModel::class, ['carId' => 'id']),
            default => parent::relationQuery($name),
        };
    }

    public function getDealer(): ?DealerModel
    {
        return $this->relation('dealer');
    }

    public function getClient(): ?UserModel
    {
        return $this->relation('client');
    }

    public function getMake(): ?CarMakeModel
    {
        return $this->relation('make');
    }

    public function getModel(): ?CarModelModel
    {
        return $this->relation('model');
    }

    public function getCarMedias(): array
    {
        return $this->relation('carMedias');
    }

    public function getCarMediasActive(): array
    {
        return $this->relation('carMediasActive');
    }

    public function getCarMediaMain(): ?CarMediaModel
    {
        return $this->relation('carMediaMain');
    }
}
