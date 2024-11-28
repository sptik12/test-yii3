<?php

namespace App\Backend\Model\Car;

use Yiisoft\Translator\TranslatorInterface;

enum BedSize: string
{
    case Long = "long";
    case Regular = "regular";
    case Unknown = "unknown";

    public function title(TranslatorInterface $translator): string
    {
        $title = match ($this) {
            self::Long => 'Long',
            self::Regular => 'Regular',
            self::Unknown => 'Unknown',
        };

        return $translator->translate($title);
    }
}
