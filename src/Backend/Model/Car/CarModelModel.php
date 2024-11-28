<?php

namespace App\Backend\Model\Car;

use AllowDynamicProperties;
use Yiisoft\ActiveRecord\ActiveQueryInterface;

#[AllowDynamicProperties]
final class CarModelModel extends \App\Backend\Model\AbstractModel
{
    public function relationQuery(string $name): ActiveQueryInterface
    {
        return match ($name) {
            'make' => $this->hasOne(CarMakeModel::class, ['id' => 'makeId']),
            'cars' => $this->hasMany(CarModel::class, ['modelId' => 'id']),
            default => parent::relationQuery($name),
        };
    }

    public function getMake(): ?CarMakeModel
    {
        return $this->relation('make');
    }

    public function getCars(): array
    {
        return $this->relation('cars');
    }
}
