<?php

declare(strict_types=1);

use App\Backend\Component\Curl\Curl;
use Yiisoft\Definitions\Reference;

/** @var array $params */

return [
    PHPCurl\CurlWrapper\CurlInterface::class => Reference::to(Curl::class)
];
