<?php

namespace App\Frontend\Asset;

use Yiisoft\Assets\AssetBundle;

final class AppAsset extends AssetBundle
{
    public ?string $basePath = "@assets";
    public ?string $baseUrl = "@assetsUrl";
    public ?string $sourcePath = "@resources/assets";

    public array $depends = [
        "App\Frontend\Asset\NotyAsset",
        "App\Frontend\Asset\BootstrapAsset",
        "App\Frontend\Asset\FormErrorsAsset",
        "App\Frontend\Asset\QuillAsset",
        "App\Frontend\Asset\VueAsset",
        "App\Frontend\Asset\AjaxAsset",
        "App\Frontend\Asset\TomSelectAsset",
    ];

    public array $css = [
        'scss/site.scss',
        'css/dev.css'
    ];

    public array $js = [
        'js/app.js',
    ];
}
