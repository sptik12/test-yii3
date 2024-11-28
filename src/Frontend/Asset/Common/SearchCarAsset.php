<?php

namespace App\Frontend\Asset\Common;

use Yiisoft\Assets\AssetBundle;

final class SearchCarAsset extends AssetBundle
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
