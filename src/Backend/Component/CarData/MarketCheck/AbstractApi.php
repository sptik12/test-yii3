<?php

namespace App\Backend\Component\CarData\MarketCheck;

use Yiisoft\Log\Logger;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Log\Target\File\FileTarget;
use Yiisoft\Config\ConfigInterface;
use PHPCurl\CurlWrapper\CurlInterface;

class AbstractApi
{
    // Todo: move this to general config
    protected $apiUrl = "https://mc-api.marketcheck.com";
    protected $apiKey = "TikalFC1YIPUy0b7wQ6Pwv1IoSQrWBGz";
    protected $apiSecret = "X2CS711rK47XVMeR";

    protected Logger $logger;

    public function __construct(
        protected CurlInterface $curl,
        protected ConfigInterface $config,
        protected Aliases $aliases
    ) {
        $fileTarget = new FileTarget($aliases->get("@runtime/logs/marketcheck-api.log"));
        $this->logger = new Logger([$fileTarget]);
    }

    public function sendGetRequest(string $url, array $params = []): object
    {
        $params = array_merge(['api_key' => $this->apiKey], $params);
        $queryString = http_build_query($params);
        $url =  "{$this->apiUrl}{$url}?{$queryString}";
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

        return $result;
    }





    protected function getHeaders()
    {
        $headers = ["Content-Type: application/json"];

        return $headers;
    }
}
