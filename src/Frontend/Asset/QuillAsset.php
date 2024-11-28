<?php

namespace App\Frontend\Asset;

use Yiisoft\Assets\AssetBundle;

final class QuillAsset extends AssetBundle
{
    public ?string $basePath = "@assets";
    public ?string $baseUrl = "@assetsUrl";
    public ?string $sourcePath = "@resources/assets";

    public array $css = [
        'https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css', // 'snow' theme
        'css/quill/quill.css',
    ];

    public array $js = [
        'https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js',
        'https://unpkg.com/quill-paste-smart@latest/dist/quill-paste-smart.js',
        'js/quill-helper.js',
    ];
}
