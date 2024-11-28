<?php

namespace App\Frontend\Asset;

use Yiisoft\Assets\AssetBundle;

final class DataTablesAsset extends AssetBundle
{
    public ?string $basePath = "@assets";
    public ?string $baseUrl = "@assetsUrl";

    public array $css = [
        "https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.1.8/datatables.min.css",
        "https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css"
    ];

    public array $js = [
        "https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.1.8/datatables.min.js",
    ];
}
