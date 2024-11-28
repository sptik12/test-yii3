<?php

namespace App\Frontend\Asset\Admin;

use Yiisoft\Assets\AssetBundle;

final class EditDealerAsset extends AssetBundle
{
    public ?string $basePath = "@assets";
    public ?string $baseUrl = "@assetsUrl";
    public ?string $sourcePath = "@resources/assets";

    public array $depends = [
        "App\Frontend\Asset\AppAsset",
        "App\Frontend\Asset\DataTablesAsset",
    ];

    public array $js = [
        'js/admin/mixin-datatable.js',
        'js/admin/edit-dealer.js',
    ];
}
