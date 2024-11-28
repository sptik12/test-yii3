<?php

namespace App\Frontend\Asset\Client;

use Yiisoft\Assets\AssetBundle;

final class SearchCarUrlAsset extends AssetBundle
{
    public ?string $basePath = "@assets";
    public ?string $baseUrl = "@assetsUrl";
    public ?string $sourcePath = "@resources/assets";

    public array $depends = [
        "App\Frontend\Asset\AppAsset",
        "App\Frontend\Asset\VueSelectAsset",
    ];

    public array $js = [
        'js/common/search-car.js',
    ];
}
