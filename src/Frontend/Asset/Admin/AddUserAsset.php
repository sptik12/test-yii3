<?php

namespace App\Frontend\Asset\Admin;

use Yiisoft\Assets\AssetBundle;

final class AddUserAsset extends AssetBundle
{
    public ?string $basePath = "@assets";
    public ?string $baseUrl = "@assetsUrl";
    public ?string $sourcePath = "@resources/assets";

    public array $depends = [
        "App\Frontend\Asset\AppAsset",
    ];

    public array $js = [
        'js/admin/add-user.js',
    ];
}
