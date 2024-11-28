<?php

namespace App\Frontend\Asset;

use Yiisoft\Assets\AssetBundle;

final class LightGalleryAsset extends AssetBundle
{
    public ?string $basePath = "@assets";
    public ?string $baseUrl = "@assetsUrl";
    public ?string $sourcePath = "@resources/assets";

    public array $css = [
        'libs/lightgallery/lightgallery-bundle.css',
    ];

    public array $js = [
        'libs/lightgallery/lightgallery.umd.js',
        'libs/lightgallery/lg-thumbnail.umd.js',
        'libs/lightgallery/lg-zoom.umd.js',
        'libs/lightgallery/lg-video.umd.js',
    ];
}
