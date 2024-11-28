<?php

namespace App\Backend\Component\CarData\MarketCheck;

class VinDecoderApi extends AbstractApi
{
    public function getCarDataByVinCode(string $vinCode): object
    {
        return $this->sendGetRequest("/v2/decode/car/{$vinCode}/specs");
    }
}
