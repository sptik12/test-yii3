<?php

namespace App\Frontend\Asset;

use Yiisoft\Assets\AssetBundle;

final class TomSelectAsset extends AssetBundle
{
    public ?string $basePath = "@assets";
    public ?string $baseUrl = "@assetsUrl";

    public array $css = [
        'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css',
    ];

    public array $js = [
        'https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js',
    ];
}
