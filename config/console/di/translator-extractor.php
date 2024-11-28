<?php

use Yiisoft\Translator\Message\Php\MessageSource;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Definitions\DynamicReference;
use Yiisoft\TranslatorExtractor\Extractor;
use Yiisoft\TranslatorExtractor\CategorySource;

return [
    Extractor::class => [
        '__construct()' => [
            [
                DynamicReference::to([
                    'class' => CategorySource::class,
                    '__construct()' => [
                        'name' => "app",
                        'reader' => DynamicReference::to(static fn(Aliases $aliases) => new MessageSource($aliases->get("@messages"))),
                        'writer' => DynamicReference::to(static fn(Aliases $aliases) => new MessageSource($aliases->get("@messages"))),
                    ],
                ]),
            ],
        ],
    ],
];
