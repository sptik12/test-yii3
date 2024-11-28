<?php

namespace App\Backend\Model\Car;

use Yiisoft\Translator\TranslatorInterface;

enum BodyType: string
{
    case Suv = "suv";
    case Sedan = "sedan";
    case Pickup = "pickup";
    case Coupe = "coupe";
    case Wagon = "wagon";
    case Truck = "truck";
    case Van = "van";
    case Minivan = "minivan";

    public function title(TranslatorInterface $translator): string
    {
        $title = match ($this) {
            self::Suv => 'Suv',
            self::Sedan => 'Sedan',
            self::Pickup => 'Pickup',
            self::Coupe => 'Coupe',
            self::Wagon => 'Wagon',
            self::Truck => 'Truck',
            self::Van => 'Van',
            self::Minivan => 'Minivan',
        };

        return $translator->translate($title);
    }

    public function picture(): string
    {
        return match ($this) {
            self::Suv => 'Suv',
            self::Sedan => 'Sedan',
            self::Pickup => 'Pickup',
            self::Coupe => 'Coupe',
            self::Wagon => 'Wagon',
            self::Truck => 'Truck',
            self::Van => 'Van',
            self::Minivan => 'Minivan',
        };
    }

    public function iconWidth(): string
    {
        return match ($this) {
            self::Suv => 76,
            self::Sedan => 76,
            self::Pickup => 76,
            self::Coupe => 76,
            self::Wagon => 83,
            self::Truck => 86,
            self::Van => 76,
            self::Minivan => 76,
        };
    }

    public function iconHeight(): string
    {
        return match ($this) {
            self::Suv => 32,
            self::Sedan => 24,
            self::Pickup => 28,
            self::Coupe => 24,
            self::Wagon => 27,
            self::Truck => 43,
            self::Van => 30,
            self::Minivan => 29,
        };
    }
}
