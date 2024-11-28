<?php

namespace App\Backend\Model\User;

enum Provider: string
{
    case Google = "google";
    case Facebook = "facebook";
}
