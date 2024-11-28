<?php

namespace App\Backend\Model\Dealer;

use Yiisoft\Translator\TranslatorInterface;

enum DealerStatus: string
{
    case New = "new";
    case Active = "active";
    case Disabled = "disabled";
    case Deleted = "deleted";

    public function title(TranslatorInterface $translator): string
    {
        $title = match ($this) {
            self::New => 'New',
            self::Active => 'Active',
            self::Disabled => 'Suspended',
            self::Deleted => 'Deleted',
        };

        return $translator->translate($title);
    }
}
