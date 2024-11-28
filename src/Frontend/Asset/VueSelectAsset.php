<?php

namespace App\Frontend\Asset;

use Yiisoft\Assets\AssetBundle;

final class VueSelectAsset extends AssetBundle
{
    public ?string $basePath = "@assets";
    public ?string $baseUrl = "@assetsUrl";
    public ?string $sourcePath = "@resources/assets";

    public array $css = [
        'https://cdnjs.cloudflare.com/ajax/libs/vue-select/4.0.0-beta.6/vue-select.min.css'
    ];

    public array $js = [
        'https://cdnjs.cloudflare.com/ajax/libs/vue-select/4.0.0-beta.6/vue-select.umd.min.js',
    ];
}
