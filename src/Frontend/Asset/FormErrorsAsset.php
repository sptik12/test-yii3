<?php

namespace App\Frontend\Asset;

use Yiisoft\Assets\AssetBundle;

final class FormErrorsAsset extends AssetBundle
{
    public ?string $basePath = "@assets";
    public ?string $baseUrl = "@assetsUrl";
    public ?string $sourcePath = "@resources/assets";

    public array $js = [
        'js/form-errors.js',
    ];
}
