<?php

namespace App\Backend\Model\Car;

use AllowDynamicProperties;
use Yiisoft\ActiveRecord\ActiveQueryInterface;

#[AllowDynamicProperties]
final class CarMediaModel extends \App\Backend\Model\AbstractModel
{
    public function relationQuery(string $name): ActiveQueryInterface
    {
        return match ($name) {
            'car' => $this->hasOne(CarModel::class, ['id' => 'carId']),
            default => parent::relationQuery($name),
        };
    }

    public function getCar(): ?CarModel
    {
        return $this->relation('car');
    }
}
