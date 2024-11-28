<?php

namespace App\Backend\Model\Car;

use Yiisoft\Translator\TranslatorInterface;

enum Condition: string
{
    case New = "new";
    case Used = "used";

    public function title(TranslatorInterface $translator): string
    {
        $title = match ($this) {
            self::New => 'New',
            self::Used => 'Used',
        };

        return $translator->translate($title);
    }

    public function titleEdit(TranslatorInterface $translator): string
    {
        $title = match ($this) {
            self::New => 'New',
            self::Used => 'Mileage',
        };

        return $translator->translate($title);
    }
}
