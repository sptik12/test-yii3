<?php

namespace App\Backend\Model\Car;

use Yiisoft\Translator\TranslatorInterface;

enum ExtColor: string
{
    case Blue = "blue";
    case Black = "black";
    case Brown = "brown";
    case Gold = "gold";
    case Green = "green";
    case Grey = "grey";
    case Orange = "orange";
    case Red = "red";
    case Silver = "silver";
    case White = "white";
    case Yellow = "yellow";
    case Unknown = "unknown";

    public function title(TranslatorInterface $translator): string
    {
        $title = match ($this) {
            self::Blue => 'Blue',
            self::Black => 'Black',
            self::Brown => 'Brown',
            self::Gold => 'Gold',
            self::Green => 'Green',
            self::Grey => 'Grey',
            self::Orange => 'Orange',
            self::Red => 'Red',
            self::Silver => 'Silver',
            self::White => 'White',
            self::Yellow => 'Yellow',
            self::Unknown => 'Other',
        };

        return $translator->translate($title);
    }
}
