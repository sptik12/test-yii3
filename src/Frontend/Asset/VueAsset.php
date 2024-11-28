<?php

namespace App\Frontend\Asset;

use Yiisoft\Assets\AssetBundle;

final class VueAsset extends AssetBundle
{
    public ?string $basePath = "@assets";
    public ?string $baseUrl = "@assetsUrl";
    public ?string $sourcePath = "@resources/assets";

    public array $js = [
        'js/vue.js',
        'js/mixin-general.js',
        'js/mixin-table.js',
    ];

    public function __construct()
    {
        switch ($_ENV['YII_ENV']) {
            case "dev":
                $this->js = array_merge($this->js, ["https://cdnjs.cloudflare.com/ajax/libs/vue/3.4.30/vue.global.min.js"]);
                break;

            default:
                $this->js = array_merge($this->js, ["https://cdnjs.cloudflare.com/ajax/libs/vue/3.4.30/vue.global.prod.min.js"]);
                break;
        }
    }
}
