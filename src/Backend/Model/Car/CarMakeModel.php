<?php

namespace App\Backend\Model\Car;

use AllowDynamicProperties;
use Yiisoft\ActiveRecord\ActiveQueryInterface;

#[AllowDynamicProperties]
final class CarMakeModel extends \App\Backend\Model\AbstractModel
{
    public function relationQuery(string $name): ActiveQueryInterface
    {
        return match ($name) {
            'cars' => $this->hasMany(CarModel::class, ['makeId' => 'id']),
            default => parent::relationQuery($name),
        };
    }

    public function getCars(): array
    {
        return $this->relation('cars');
    }
}
