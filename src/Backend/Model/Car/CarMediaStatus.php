<?php

namespace App\Backend\Model\Car;

use Yiisoft\Translator\TranslatorInterface;

enum CarMediaStatus: string
{
    case Active = "active";
    case ToConvert = "toconvert";
    case Processing = "processing";
    case Failed = "failed";
}
