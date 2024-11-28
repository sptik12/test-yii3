<?php

namespace App\Backend\Model\Car;

use Yiisoft\Translator\TranslatorInterface;

enum CabinSize: string
{
    case Crew = "crew";
    case Extended = "extended";
    case LargeCrew = "largeCrew";
    case Regular = "regular";
    case Unknown = "unknown";

    public function title(TranslatorInterface $translator): string
    {
        $title = match ($this) {
            self::Crew => 'Crew',
            self::Extended => 'Extended',
            self::LargeCrew => 'Large Crew',
            self::Regular => 'Regular',
            self::Unknown => 'Unknown',
        };

        return $translator->translate($title);
    }
}
