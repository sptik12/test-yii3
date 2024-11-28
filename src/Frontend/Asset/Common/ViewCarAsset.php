<?php

namespace App\Frontend\Asset\Common;

use Yiisoft\Assets\AssetBundle;

final class ViewCarAsset extends AssetBundle
{
    public ?string $basePath = "@assets";
    public ?string $baseUrl = "@assetsUrl";
    public ?string $sourcePath = "@resources/assets";

    public array $depends = [
        "App\Frontend\Asset\LightGalleryAsset",
        "App\Frontend\Asset\AppAsset",
    ];

    public array $css = [
        // 'scss/pages/card.scss',
    ];

    public array $js = [
        'js/common/view-car.js',
    ];
}
