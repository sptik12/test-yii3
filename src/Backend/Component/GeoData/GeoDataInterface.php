<?php

declare(strict_types=1);

namespace App\Backend\Component\GeoData;

interface GeoDataInterface
{
    public function getGeoDataByPostalCode(string $postalCode, string $country): ?GeoData;

    public function getGeoData(string $query, string $postalCode, string $country): ?GeoData;

    public function getPostalCode(string $latitude, string $longitude): ?string;
}
