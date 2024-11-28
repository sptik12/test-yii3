<?php

namespace App\Frontend\Asset;

use Yiisoft\Assets\AssetBundle;

final class NotyAsset extends AssetBundle
{
    public ?string $basePath = "@assets";
    public ?string $baseUrl = "@assetsUrl";

    public array $css = [
        'https://cdnjs.cloudflare.com/ajax/libs/noty/3.1.4/noty.min.css',
    ];

    public array $js = [
        'https://cdnjs.cloudflare.com/ajax/libs/noty/3.1.4/noty.min.js',
    ];
}
