<?php

namespace App\Frontend\Asset\Common;

use Yiisoft\Assets\AssetBundle;

final class EditCarAsset extends AssetBundle
{
    public ?string $basePath = "@assets";
    public ?string $baseUrl = "@assetsUrl";
    public ?string $sourcePath = "@resources/assets";

    public array $depends = [
        "App\Frontend\Asset\QuillAsset",
        "App\Frontend\Asset\AppAsset",
    ];

    public array $js = [
        'js/common/edit-car.js',
    ];
}
