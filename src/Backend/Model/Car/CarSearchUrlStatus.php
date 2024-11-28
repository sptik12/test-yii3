<?php

namespace App\Backend\Model\Car;

enum CarSearchUrlStatus: string
{
    case Active = "active";
    case Deleted = "deleted";
}
