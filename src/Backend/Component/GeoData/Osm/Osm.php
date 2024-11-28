<?php

namespace App\Backend\Component\GeoData\Osm;

use App\Backend\Exception\GeoCodeApiException;
use App\Backend\Component\GeoData\GeoDataInterface;
use App\Backend\Component\GeoData\GeoData;
use PHPCurl\CurlWrapper\CurlInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Json\Json;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Target\File\FileTarget;
use Yiisoft\Translator\TranslatorInterface;

final class Osm implements GeoDataInterface
{
    protected $apiForwardUrl = "https://nominatim.openstreetmap.org/search";
    protected $apiReverseUrl = "https://nominatim.openstreetmap.org/reverse";

    protected Logger $logger;

    public function __construct(
        protected CurlInterface $curl,
        protected ConfigInterface $config,
        protected Aliases $aliases,
        protected TranslatorInterface $translator
    ) {
        $fileTarget = new FileTarget($aliases->get("@runtime/logs/osm-api.log"));
        $this->logger = new Logger([$fileTarget]);
    }

    public function getGeoData(string $query, ?string $postalCode = null, string $country = "Canada"): GeoData
    {
        if (!$postalCode) {
            return $this->getGeoDataByQuery($query, $country);
        }

        try {
            return $this->getGeoDataByQuery($query, $country);
        } catch (GeoCodeApiException $e) {
            if ($e->getCode() == 422) {
                return $this->getGeoDataByPostalCode($postalCode, $country);
            } else {
                throw new GeoCodeApiException($e->getMessage(), $e->getCode());
            }
        }
    }

    public function getGeoDataByPostalCode(string $postalCode, string $country = "Canada"): GeoData
    {
        $params = [
            'postalcode' => $postalCode,
            'country' => $country,
            'format' => 'json',
            'limit' => 1,
            'addressdetails' => 1
        ];

        return $this->search($params);
    }


    public function getGeoDataByQuery(string $query, string $country = "Canada"): GeoData
    {
        $params = [
            'q' => "{$query},{$country}",
            'format' => 'json',
            'limit' => 1,
            'addressdetails' => 1
        ];

        return $this->search($params);
    }


    public function getPostalCode(string $latitude, string $longitude): ?string
    {
        $params = [
            'format' => 'json',
            'lat' => $latitude,
            'lon' => $longitude,
        ];

        $queryString = http_build_query($params);
        $url = "{$this->apiReverseUrl}?{$queryString}";

        $result = $this->sendRequest($url);

        return $result->address->postcode;
    }





    protected function search(array $params): ?GeoData
    {
        $queryString = http_build_query($params);
        $url = "{$this->apiForwardUrl}?{$queryString}";

        $result = $this->sendRequest($url);

        if (is_array($result)) {
            $result = $result[0];
        }

        if (!property_exists($result, 'lat') || !property_exists($result, 'lon')) {
            throw new GeoCodeApiException($this->translator->translate('You have entered invalid geo data, location cannot be determined'), 422);
        }

        $geoData = new GeoData();
        $geoData->latitude = $result?->lat;
        $geoData->longitude = $result?->lon;
        $geoData->region =  $data?->region ?? null;
        $geoData->province =  $data?->region_code ?? null;
        $geoData->country =  $data?->region_code ?? null;

        return $geoData;
    }

    protected function getHeaders()
    {
        $headers = ["Content-Type: application/json"];
        $headers[] = "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36";

        return $headers;
    }

    protected function sendRequest($url): mixed
    {
        $this->logger->info($url);

        $this->curl->init($url);
        $this->curl->setOpt(CURLOPT_HTTPHEADER, $this->getHeaders());
        $this->curl->setOpt(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOpt(CURLOPT_CONNECTTIMEOUT, 10);
        $this->curl->setOpt(CURLOPT_TIMEOUT, 10);
        $result = $this->curl->exec();
        $httpCode = $this->curl->getInfo(CURLINFO_HTTP_CODE);

        // Ex. reverse requests
        // https://nominatim.openstreetmap.org/reverse?format=json&lat=54.9824031826&lon=9.2833114795
        // {"place_id":132168217,"licence":"Data © OpenStreetMap contributors, ODbL 1.0. http://osm.org/copyright","osm_type":"node","osm_id":965993073,"lat":"54.982375","lon":"9.283268","class":"place","type":"house","place_rank":30,"importance":9.99999999995449e-06,"addresstype":"place","name":"","display_name":"6, Bredhøjvej, Bolderslev, Aabenraa Municipality, Южная Дания, 6392, Дания","address":{"house_number":"6","road":"Bredhøjvej","village":"Bolderslev","municipality":"Aabenraa Municipality","state":"Южная Дания","ISO3166-2-lvl4":"DK-83","postcode":"6392","country":"Дания","country_code":"dk"},"boundingbox":["54.9823250","54.9824250","9.2832180","9.2833180"]}
        // https://nominatim.openstreetmap.org/reverse?format=json&lat=54.9824031826
        // {"error":{"code":400,"message":"Parameter 'lon' missing."}}
        // https://nominatim.openstreetmap.org/reverse?format=json&lat=2154.9824031826&lon=9.2833114795
        // {"error":"Unable to geocode"}

        // Ex. forward requests
        // https://nominatim.openstreetmap.org/search?postalCode=R7C&country=Canada&format=json&limit=1&addressdetails=1
        // [{"place_id":29879602,"licence":"Data © OpenStreetMap contributors, ODbL 1.0. http://osm.org/copyright","osm_type":"relation","osm_id":1428125,"lat":"61.0666922","lon":"-107.991707","class":"boundary","type":"administrative","place_rank":4,"importance":0.9110642342490372,"addresstype":"country","name":"Канада","display_name":"Канада","address":{"country":"Канада","country_code":"ca"},"boundingbox":["41.6765597","83.3362128","-141.0027500","-52.3237664"]}]
        // https://nominatim.openstreetmap.org/search?q=fwefwefeeererformat=json&limit=1&addressdetails=1
        // []

        $this->logger->info($result);

        if (!$result || $result == "[]") {
            $result = new \stdClass();
        } else {
            $result = json_decode($result);
        }

        if ($httpCode != 200) {
            if ($httpCode == 400) {
                throw new GeoCodeApiException($this->translator->translate($result->error->message), 422);
            } else {
                throw new GeoCodeApiException($this->translator->translate("Invalid GEO OSM API URL or no connection"), 404);
            }
        } else {
            if (is_object($result) && property_exists($result, 'error') && is_string($result->error)) {
                throw new GeoCodeApiException($result->error, 422);
            }
        }

        return $result;
    }
}
