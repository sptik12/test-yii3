<?php

namespace App\Frontend\Asset\Client;

use Yiisoft\Assets\AssetBundle;

final class AuthAsset extends AssetBundle
{
    public ?string $basePath = "@assets";
    public ?string $baseUrl = "@assetsUrl";
    public ?string $sourcePath = "@resources/assets";

    public array $depends = [
        "App\Frontend\Asset\VueAsset",
    ];

    public array $js = [
        'js/client/auth.js',
    ];

    public array $css = [
        //        'scss/pages/auth.scss',
    ];
}
