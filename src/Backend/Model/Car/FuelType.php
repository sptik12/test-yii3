<?php

namespace App\Backend\Model\Car;

use Yiisoft\Translator\TranslatorInterface;

enum FuelType: string
{
    case Electric = "electric";
    case Diesel = "diesel";
    case Biodiesel = "biodiesel";
    case E85 = "e85";
    case M85 = "m85";
    case Premium = "premium";
    case Unleaded = "unleaded";
    case Gas = "gas";
    case Hydrogen = "hydrogen";

    public function title(TranslatorInterface $translator): string
    {
        $title = match ($this) {
            self::Electric => 'Electric',
            self::Diesel => 'Diesel',
            self::Biodiesel => 'Biodiesel',
            self::E85 => 'E85',
            self::M85 => 'M85',
            self::Premium => 'Premium',
            self::Unleaded => 'Unleaded',
            self::Gas => 'Compressed Natural Gas',
            self::Hydrogen => 'Hydrogen'
        };

        return $translator->translate($title);
    }
}
