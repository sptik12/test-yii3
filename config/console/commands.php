<?php

declare(strict_types=1);

use App\Backend\Command\Dealer\ParseCarsCommand;
use App\Backend\Command\Notificator\DispatchCommand;
use App\Backend\Command\Make\FillCommand;
use App\Backend\Command\Geo\DealersCommand;
use App\Backend\Command\Car\ConvertVideoCommand;
use App\Backend\Command\Car\SetMainMediaCommand;
use App\Backend\Command\User\DeleteCommand;
use App\Backend\Command\ClearDbCommand;

return [
    'notificator:dispatch' => DispatchCommand::class,
    'make:fill' => FillCommand::class,
    'geo:dealers' => DealersCommand::class,
    'geo:users' => UsersCommand::class,
    'dealer:parse-cars' => ParseCarsCommand::class,
    'car:convert-video' => ConvertVideoCommand::class,
    'car:set-main-media' => SetMainMediaCommand::class,
    'user:delete' => DeleteCommand::class,
    'db:clear' => ClearDbCommand::class,
];
