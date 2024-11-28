<?php

namespace App\Backend\Model\Car;

use Yiisoft\Translator\TranslatorInterface;

enum CarMediaType: string
{
    case Image = "image";
    case Video = "video";
    case Document = "document";
}
