<?php

namespace App\Backend\Model\Car;

use Yiisoft\Translator\TranslatorInterface;

enum PriceStatus: string
{
    case NoRating = "norating";
    case Uncertain = "uncertain";
    case Overpriced = "overpriced";
    case Highpriced = "highpriced";
    case Fairdeal = "fairdeal";
    case Gooddeal = "gooddeal";
    case Greatdeal = "greatdeal";

    public function title(TranslatorInterface $translator): string
    {
        $title = match ($this) {
            self::NoRating => 'No Rating',
            self::Uncertain => 'Uncertain',
            self::Overpriced => 'Overpriced',
            self::Highpriced => 'High priced',
            self::Fairdeal => 'Fair Deal',
            self::Gooddeal => 'Good Deal',
            self::Greatdeal => 'Great Deal'
        };

        return $translator->translate($title);
    }

    public function color(): string
    {
        $color = match ($this) {
            self::NoRating => 'darkgrey',
            self::Uncertain => 'grey',
            self::Overpriced => 'red',
            self::Highpriced => 'red',
            self::Fairdeal => 'green',
            self::Gooddeal => 'green',
            self::Greatdeal => 'darkgreen'
        };

        return $color;
    }
}
