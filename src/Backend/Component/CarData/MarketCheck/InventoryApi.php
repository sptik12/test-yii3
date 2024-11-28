<?php

namespace App\Backend\Component\CarData\MarketCheck;

class InventoryApi extends AbstractApi
{
    public function getMakes(string $country = "US"): object
    {
        return $this->sendGetRequest(
            "/v2/search/car/active",
            [
                "start" => 0,
                "rows" => 0,
                "country" => $country,
                "facets" => "make|0|1000"
            ]
        );
    }

    public function getModels(string $make): object
    {
        return $this->sendGetRequest(
            "/v2/search/car/active",
            [
                "start" => 0,
                "rows" => 0,
                "make" => $make,
                "facets" => "model|0|1000"
            ]
        );
    }
}
