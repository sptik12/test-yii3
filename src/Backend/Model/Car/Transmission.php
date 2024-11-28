<?php

namespace App\Backend\Model\Car;

use Yiisoft\Translator\TranslatorInterface;

enum Transmission: string
{
    case Automatic = "automatic";
    case Variator = "variator";
    case Robot = "robot";
    case Manual = "manual";

    public function title(TranslatorInterface $translator): string
    {
        $title = match ($this) {
            self::Automatic => 'Automatic',
            self::Manual => 'Manual',
            self::Variator => 'Variator',
            self::Robot => 'Robot',
        };

        return $translator->translate($title);
    }
}
