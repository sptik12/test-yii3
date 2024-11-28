<?php

namespace App\Backend\Model;

use PhpParser\Builder\Enum_;
use Yiisoft\Translator\TranslatorInterface;

enum Province: string
{
    case AB = "Alberta";
    case BC = "British Columbia";
    case MB = "Manitoba";
    case NB = "New Brunswick";
    case NL = "Newfoundland and Labrador";
    case NT = "Northwest Territories";
    case NS = "Nova Scotia";
    case NU = "Nunavut";
    case ON = "Ontario";
    case PE = "Prince Edward Island";
    case QC = "Quebec";
    case SK = "Saskatchewan";
    case YT = "Yukon";

    public function title(TranslatorInterface $translator): string
    {
        return $translator->translate($this->value);
    }

    public static function tryFromName(?string $name): ?Province
    {
        foreach (self::cases() as $case) {
            if ($name === $case->name) {
                return $case;
            }
        }

        return null;
    }

    public function getFirstPostalCodeChar(): ?array
    {
        return match ($this) {
            self::AB => ["T"],
            self::BC => ["V"],
            self::MB => ["R"],
            self::NB => ["E"],
            self::NL => ["A"],
            self::NT => ["X"],
            self::NS => ["B"],
            self::NU => ["X"],
            self::ON => ["K", "L", "N", "P", "M"],
            self::PE => ["C"],
            self::QC => ["G", "J", "H"],
            self::SK => ["S"],
            self::YT => ["Y"],
            default => null
        };
    }
}
