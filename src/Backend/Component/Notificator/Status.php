<?php

namespace App\Backend\Component\Notificator;

enum Status: string
{
    case New = "new";
    case Done = "done";
    case Failed = "failed";
}
