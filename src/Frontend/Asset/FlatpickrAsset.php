<?php

namespace App\Frontend\Asset;

use Yiisoft\Assets\AssetBundle;

final class FlatpickrAsset extends AssetBundle
{
    public ?string $basePath = "@assets";
    public ?string $baseUrl = "@assetsUrl";

    public array $css = [
        'https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css',
    ];

    public array $js = [
        'https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js',
    ];
}
