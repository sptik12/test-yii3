<?php

namespace App\Backend\Component\GeoData\PositionStack;

use App\Backend\Exception\GeoCodeApiException;
use App\Backend\Component\GeoData\GeoDataInterface;
use App\Backend\Component\GeoData\GeoData;
use PHPCurl\CurlWrapper\CurlInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Target\File\FileTarget;
use Yiisoft\Translator\TranslatorInterface;

final class PositionStack implements GeoDataInterface
{
    protected $apiForwardUrl = "https://api.positionstack.com/v1/forward";
    protected $apiReverseUrl = "https://nominatim.openstreetmap.org/reverse";
    protected $apiKey = "9696b27dd95c07c29e94f97a0c04bf85";

    protected Logger $logger;

    public function __construct(
        protected CurlInterface $curl,
        protected ConfigInterface $config,
        protected Aliases $aliases,
        protected TranslatorInterface $translator
    ) {
        $fileTarget = new FileTarget($aliases->get("@runtime/logs/positionstack-api.log"));
        $this->logger = new Logger([$fileTarget]);
    }


    public function getGeoDataByPostalCode(string $postalCode, string $country = "CA"): ?GeoData
    {
        return $this->getGeoData($postalCode, $country);
    }

    public function getGeoData(string $query, string $postalCode, string $country = "CA"): ?GeoData
    {
        $params = [
            'access_key' => $this->apiKey,
            'query' => $query,
            "country" => $country,
            'fields' => 'results.latitude,results.longitude',
            'output' => 'json',
            'limit' => 1,
        ];

        $queryString = http_build_query($params);
        $url =  "{$this->apiForwardUrl}?{$queryString}";

        $this->logger->info($url);

        $this->curl->init($url);
        $this->curl->setOpt(CURLOPT_HTTPHEADER, $this->getHeaders());
        $this->curl->setOpt(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOpt(CURLOPT_CONNECTTIMEOUT, 10);
        $this->curl->setOpt(CURLOPT_TIMEOUT, 10);
        $result = $this->curl->exec();
        $httpCode = $this->curl->getInfo(CURLINFO_HTTP_CODE);

        if (!$result) {
            $result = new \stdClass();
        } else {
            $this->logger->info($result);
            $result = json_decode($result);
        }
        $result->httpCode = $httpCode;

        if ($result->httpCode != 200) {
            if ($result->httpCode == 422) {
                throw new GeoCodeApiException($this->translator->translate($result->error->message), 422);
            } else {
                throw new GeoCodeApiException($this->translator->translate("Invalid GEO PositionStack API URL or no connection"), 404);
            }
        }

        if (!$result->data) {
            throw new GeoCodeApiException($this->translator->translate("Invalid geodata request for {query}", ['query' => $query]), 422);
        }

        // Ex:
        /*
             stdClass Object(
                [data] => Array
                    (
                        [0] => stdClass Object
                            (
                                [latitude] => 45.673868
                                [longitude] => -73.504242
                                [type] => postalcode
                                [name] => H1A
                                [number] =>
                                [postal_code] => H1A
                                [street] =>
                                [confidence] => 1
                                [region] => Quebec
                                [region_code] => QC
                                [county] =>
                                [locality] => Montreal
                                [administrative_area] =>
                                [neighbourhood] =>
                                [country] => Canada
                                [country_code] => CAN
                                [continent] => North America
                                [label] => H1A, Montreal, QC, Canada
                            )
                    )
                [httpCode] => 200
            )
        */

        $data = $result->data[0];

        $geoData = new GeoData();
        $geoData->latitude = $data->latitude;
        $geoData->longitude = $data->longitude;
        $geoData->postalCode = $data->postal_code;
        $geoData->region =  $data?->region ?? null;
        $geoData->province =  $data?->region_code ?? null;
        $geoData->label = $data->label;

        return $geoData;
    }

    public function getPostalCode(string $latitude, string $longitude): ?string
    {
        $params = [
            'format' => 'json',
            'lat' => $latitude,
            'lon' => $longitude,
        ];

        $queryString = http_build_query($params);
        $url =  "{$this->apiReverseUrl}?{$queryString}";

        $this->logger->info($url);

        $this->curl->init($url);
        $this->curl->setOpt(CURLOPT_HTTPHEADER, $this->getHeaders());
        $this->curl->setOpt(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOpt(CURLOPT_CONNECTTIMEOUT, 10);
        $this->curl->setOpt(CURLOPT_TIMEOUT, 10);
        $result = $this->curl->exec();
        $httpCode = $this->curl->getInfo(CURLINFO_HTTP_CODE);

        if (!$result) {
            $result = new \stdClass();
        } else {
            $this->logger->info($result);
            $result = json_decode($result);
        }
        $result->httpCode = $httpCode;

        if ($result->httpCode != 200) {
            if ($result->httpCode == 400) {
                throw new GeoCodeApiException($this->translator->translate($result->error->message), 422);
            } else {
                throw new GeoCodeApiException($this->translator->translate("Invalid GEO OSM API URL or no connection"), 404);
            }
        } else {
            if (property_exists($result, 'error') && is_string($result->error)) {
                throw new GeoCodeApiException($result->error, 422);
            }

            return $result->address->postcode;
        }
    }





    protected function getHeaders()
    {
        $headers = ["Content-Type: application/json"];
        $headers[] = "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36";

        return $headers;
    }
}
