<?php

declare(strict_types=1);

namespace App\Backend\Component\CarData;

class CarData
{
    public string $vinCode;
    public ?string $makeId;
    public ?string $modelId;
    public ?string $trim;
    public ?int $year;
    public ?string $bodyType;
    public ?string $mileage;
    public ?string $vehicleType;
    public ?string $transmission;
    public ?string $drivetrain;
    public ?string $fuelType;
    public ?float $engine;
    public ?string $engineType;
    public ?int $cylinders;
    public ?int $doors;
    public ?string $madeIn;
    public ?string $condition;
    public ?float $fuelEconomy;
    public ?int $co2;
    public ?int $evBatteryRange;
    public ?int $evBatteryTime;
    public ?int $seats;
    public ?string $cabinSize;
    public ?string $bedSize;
    public ?string $extColor;
    public ?string $intColor;
    public ?string $safetyRating;
    public ?int $certifiedPreOwned;
    public ?array $features;
}
