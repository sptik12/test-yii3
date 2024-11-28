<?php

namespace App\Backend\Model\Car;

use Yiisoft\Translator\TranslatorInterface;

enum IntColor: string
{
    case Black = "black";
    case Brown = "brown";
    case Grey = "grey";
    case Orange = "orange";
    case Red = "red";
    case White = "white";
    case Unknown = "unknown";

    public function title(TranslatorInterface $translator): string
    {
        $title = match ($this) {
            self::Black => 'Black',
            self::Brown => 'Brown',
            self::Grey => 'Grey',
            self::Orange => 'Orange',
            self::Red => 'Red',
            self::White => 'White',
            self::Unknown => 'Other',
        };

        return $translator->translate($title);
    }
}
