<?php

namespace App\Backend\Model\Car;

use Yiisoft\Translator\TranslatorInterface;

enum VehicleType: string
{
    case Car = "car";
    case Truck = "truck";
    case Motorcycle = "motorcycle";

    public function title(TranslatorInterface $translator): string
    {
        $title = match ($this) {
            self::Car => 'Car',
            self::Truck => 'Truck',
            self::Motorcycle => 'Motorcycle'
        };

        return $translator->translate($title);
    }
}
