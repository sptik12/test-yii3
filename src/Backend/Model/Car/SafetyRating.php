<?php

namespace App\Backend\Model\Car;

use Yiisoft\Translator\TranslatorInterface;

enum SafetyRating: string
{
    case Stars5 = "stars5";
    case Stars4 = "stars4";
    case Stars3 = "stars3";
    case NoRating = "norating";

    public function title(TranslatorInterface $translator): string
    {
        $title = match ($this) {
            self::Stars5 => "5 stars",
            self::Stars4 => "4+ stars",
            self::Stars3 => "3+ stars",
            self::NoRating => "No Rating",
        };

        return $translator->translate($title);
    }
}
