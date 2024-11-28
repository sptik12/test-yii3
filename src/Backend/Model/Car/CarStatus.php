<?php

namespace App\Backend\Model\Car;

use Yiisoft\Translator\TranslatorInterface;

enum CarStatus: string
{
    case Draft = "draft";
    case Considered = "considered";
    case Published = "published";
    case Rejected = "rejected";
    case Suspended = "suspended";
    case Selled = "selled";
    case Deleted = "deleted";

    public function title(TranslatorInterface $translator): string
    {
        $title = match ($this) {
            self::Draft => 'Draft',
            self::Considered => 'Considered',
            self::Published => 'Published',
            self::Rejected => 'Rejected',
            self::Suspended => 'Suspended',
            self::Selled => 'Selled',
            self::Deleted => 'Deleted',
        };

        return $translator->translate($title);
    }
}
