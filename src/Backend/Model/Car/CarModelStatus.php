<?php

namespace App\Backend\Model\Car;

enum CarModelStatus: string
{
    case Active = "active";
    case Disabled = "disabled";
}
