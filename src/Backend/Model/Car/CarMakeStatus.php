<?php

namespace App\Backend\Model\Car;

enum CarMakeStatus: string
{
    case Active = "active";
    case Disabled = "disabled";
}
