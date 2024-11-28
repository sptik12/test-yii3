<?php

namespace App\Backend\Model\User;

use Yiisoft\Translator\TranslatorInterface;

enum Status: string
{
    case Active = "active";
    case Disabled = "disabled";
    case Deleted = "deleted";

    public function title(TranslatorInterface $translator): string
    {
        $title = match ($this) {
            self::Active => 'Active',
            self::Disabled => 'Suspended',
            self::Deleted => 'Deleted',
        };

        return $translator->translate($title);
    }
}
