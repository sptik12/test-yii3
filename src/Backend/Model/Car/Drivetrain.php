<?php

namespace App\Backend\Model\Car;

use Yiisoft\Translator\TranslatorInterface;

enum Drivetrain: string
{
    case RWD4WD = "rwd-4wd";
    case FourWD = "4wd";
    case FWD = "fwd";
    case RWD = "rwd";
    case FourxTwo = "4x2";

    public function title(TranslatorInterface $translator): string
    {
        $title = match ($this) {
            self::RWD4WD => 'All-Wheel Drive',
            self::FourWD => 'Four-Wheel Drive',
            self::FWD => 'Front-Wheel Drive',
            self::RWD => 'Rear-Wheel Drive',
            self::FourxTwo => '4x2',
        };

        return $translator->translate($title);
    }
}
