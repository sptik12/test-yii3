<?php

declare(strict_types=1);

namespace App\Backend\Component\GeoData;

class GeoData
{
    public ?float $latitude;
    public ?float $longitude;
    public ?string $postalCode;
    public ?string $region;
    public ?string $province;
    public ?string $country;
}
