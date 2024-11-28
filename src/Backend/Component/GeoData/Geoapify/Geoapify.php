<?php

namespace App\Backend\Component\GeoData\Geoapify;

use App\Backend\Exception\GeoCodeApiException;
use App\Backend\Component\GeoData\GeoDataInterface;
use App\Backend\Component\GeoData\GeoData;
use PHPCurl\CurlWrapper\CurlInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Log\Logger;
use Yiisoft\Log\Target\File\FileTarget;
use Yiisoft\Translator\TranslatorInterface;

final class Geoapify implements GeoDataInterface
{
    protected $apiForwardUrl = "https://api.geoapify.com/v1/geocode/search";
    protected $apiReverseUrl = "https://api.geoapify.com/v1/geocode/reverse";
    protected $apiKey = "766d731152034e7f8603dddd125efc7a";

    protected Logger $logger;

    public function __construct(
        protected CurlInterface $curl,
        protected ConfigInterface $config,
        protected Aliases $aliases,
        protected TranslatorInterface $translator
    ) {
        $fileTarget = new FileTarget($aliases->get("@runtime/logs/geoapify-api.log"));
        $this->logger = new Logger([$fileTarget]);
    }

    public function getGeoData(string $query, ?string $postalCode = null, string $country = "ca"): GeoData
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

    public function getGeoDataByPostalCode(string $postalCode, string $country = "ca"): GeoData
    {
        $params = [
            'apiKey' => $this->apiKey,
            'postcode' => $postalCode,
            'country' => 'Canada', // without this param country APi can return wrong country despite countrycode param in filter
            'filter=' => "countrycode:{$country}",
            'format' => 'json',
            'limit' => 1,
        ];

        return $this->search($params);
    }


    public function getGeoDataByQuery(string $query, string $country = "ca"): GeoData
    {
        $params = [
            'apiKey' => $this->apiKey,
            'text' => "{$query}",
            'filter=' => "countrycode:{$country}",
            'format' => 'json',
            'limit' => 1
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

        $results = $result->results;

        if ($results) {
            $result = $results[0];

            if (!property_exists($result, 'postcode')) {
                throw new GeoCodeApiException($this->translator->translate('You have entered invalid geo data, postcode cannot be determined'), 422);
            }

            return $result->postcode;
        } else {
            throw new GeoCodeApiException($this->translator->translate('You have entered invalid geo data, location cannot be determined'), 422);
        }
    }





    protected function search(array $params): ?GeoData
    {
        $queryString = http_build_query($params);
        $url = "{$this->apiForwardUrl}?{$queryString}";

        $result = $this->sendRequest($url);

        // Error code example
        // {"results":[],"query":{"text":"Haaghag2T","postcode":"Haaghag2T","parsed":{"postcode":"sadsf sdw","expected_type":"unknown"}}}

        // Success forward request example
        /* {
          "results": [
            {
              "country_code": "ca",
              "name": "H2T",
              "country": "Canada",
              "datasource": {
                "sourcename": "whosonfirst",
                "attribution": "Who's On First",
                "license": "CC0",
                "url": "https://www.whosonfirst.org/docs/licenses/"
              },
              "postcode": "H2T",
              "state": "Quebec",
              "city": "Montreal",
              "state_code": "QC",
              "lon": -73.591126,
              "lat": 45.523191,
              "result_type": "postcode",
              "formatted": "Montreal, QC H2T, Canada",
              "address_line1": "Montreal, QC H2T",
              "address_line2": "Canada",
              "timezone": {
                "name": "America/Toronto",
                "offset_STD": "-05:00",
                "offset_STD_seconds": -18000,
                "offset_DST": "-04:00",
                "offset_DST_seconds": -14400,
                "abbreviation_STD": "EST",
                "abbreviation_DST": "EDT"
              },
              "plus_code": "87Q8GCF5+7G",
              "rank": {
                "popularity": 8.23533480831882,
                "confidence": 1,
                "confidence_city_level": 1,
                "match_type": "full_match"
              },
              "place_id": "512e742502d56552c059e04735ecf7c24640c002079203064832542b6361e2032077686f736f6e66697273743a706f7374616c636f64653a353034383032353637",
              "bbox": {
                "lon1": -73.608823727,
                "lat1": 45.517576802,
                "lon2": -73.582898143,
                "lat2": 45.531016332
              }
            }
          ],
          "bbox": [-73.608823727, 45.517576802, -73.582898143, 45.531016332],
          "query": {
            "text": "H2T",
            "postcode": "H2T",
            "parsed": {
              "postcode": "H2T",
              "expected_type": "unknown"
            }
          }
        } */
        $results = $result->results;

        if ($results) {
            $result = $results[0];

            if (!property_exists($result, 'lat') || !property_exists($result, 'lon')) {
                throw new GeoCodeApiException($this->translator->translate('You have entered invalid geo data, location cannot be determined'), 422);
            }

            $geoData = new GeoData();
            $geoData->latitude = $result?->lat;
            $geoData->longitude = $result?->lon;
            $geoData->postalCode = $result?->postcode ?? null;
            $geoData->province =  $result?->state_code ?? null;
            $geoData->region = $result?->state;
            $geoData->country = $result?->country;

            return $geoData;
        } else {
            throw new GeoCodeApiException($this->translator->translate('You have entered invalid geo data, location cannot be determined'), 422);
        }
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

        $this->logger->info($result);

        if (!$result) {
            $result = new \stdClass();
        } else {
            $result = json_decode($result);
        }

        if ($httpCode != 200) {
            throw new GeoCodeApiException($this->translator->translate("Invalid GEO OSM API URL or no connection"), 404);
        }

        return $result;
    }
}
