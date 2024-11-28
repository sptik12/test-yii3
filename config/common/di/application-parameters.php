<?php

declare(strict_types=1);

use App\Frontend\ApplicationParameters;

/** @var array $params */

return [
    ApplicationParameters::class => [
        'class' => ApplicationParameters::class,
        'charset()' => [$params['app']['charset']],
        'name()' => [$params['app']['name']],
        'phone()' => [$params['app']['phone']],
        'url()' => [$params['app']['url']],
        'supportEmail()' => [$params['app']['supportEmail']],
    ],
];
