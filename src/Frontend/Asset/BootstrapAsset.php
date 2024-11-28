<?php

namespace App\Frontend\Asset;

use Yiisoft\Assets\AssetBundle;

final class BootstrapAsset extends AssetBundle
{
    public ?string $basePath = "@assets";
    public ?string $baseUrl = "@assetsUrl";

    public array $css = [
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css",
    ];

    public array $js = [
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js",
    ];
}
