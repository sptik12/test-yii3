<?php

declare(strict_types=1);

use App\Frontend\ViewInjection\CommonViewInjection;
use App\Frontend\ViewInjection\LayoutViewInjection;
use App\Frontend\ViewInjection\ResponseErrorsViewInjection;
use App\Frontend\ViewInjection\SavedRequestBodyViewInjection;
use App\Frontend\ViewInjection\TranslatorViewInjection;
use Yiisoft\Assets\AssetManager;
use Yiisoft\Definitions\Reference;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Yii\View\Renderer\CsrfViewInjection;

return [
    'app' => [
        'charset' => "UTF-8",
        'locale' => "en",
        'name' => $_ENV['APP_NAME'],
        'url' => $_ENV['APP_URL'],
        'phone' => $_ENV['APP_PHONE'],
        'supportEmail' => $_ENV['SUPPORT_EMAIL'],
        'defaultFromEmail' => $_ENV['DEFAULT_FROM_EMAIL'],
    ],

    'yiisoft/db-mysql' => [
        'dsn' => $_ENV['DBDSN'],
        'username' => $_ENV['DBUSERNAME'],
        'password' => $_ENV['DBPASSWORD'],
    ],

    'yiisoft/aliases' => [
        'aliases' => require __DIR__ . "/aliases.php",
    ],

    'yiisoft/translator' => [
        'locale' => 'en',
        'fallbackLocale' => 'en',
        'defaultCategory' => 'app',
    ],

    'yiisoft/view' => [
        'basePath' => '@views',
        'parameters' => [
            'assetManager' => Reference::to(AssetManager::class),
            'urlGenerator' => Reference::to(UrlGeneratorInterface::class),
            'currentRoute' => Reference::to(CurrentRoute::class),
            'translator' => Reference::to(TranslatorInterface::class),
        ],
    ],

    // view-renderer 6.0, remove this after testing
    'yiisoft/yii-view' => [
        'injections' => [
            Reference::to(CommonViewInjection::class),
            Reference::to(CsrfViewInjection::class),
            Reference::to(LayoutViewInjection::class),
            Reference::to(TranslatorViewInjection::class),
            Reference::to(SavedRequestBodyViewInjection::class),
            Reference::to(ResponseErrorsViewInjection::class),
        ],
    ],

    // view-renderer 10.0
    'yiisoft/yii-view-renderer' => [
        'viewPath' => '@views',
        'layout' => '@layout/main',
        'injections' => [
            Reference::to(CommonViewInjection::class),
            Reference::to(CsrfViewInjection::class),
            Reference::to(LayoutViewInjection::class),
            Reference::to(TranslatorViewInjection::class),
            Reference::to(SavedRequestBodyViewInjection::class),
            Reference::to(ResponseErrorsViewInjection::class),
        ],
    ],

    'yiisoft/db-migration' => [
        'newMigrationNamespace' => "App\\Backend\\Migration",
        'sourceNamespaces' => ["App\\Backend\\Migration"],
    ],

    'yiisoft/mailer' => [
        'useSendmail' => false,
        'writeToFiles' => $_ENV['USEFILETRANSPORT'],
    ],

    'yiisoft/csrf' => [
        'hmacToken' => [
            'secretKey' => '',
            'algorithm' => 'sha256',
            'lifetime' => 24 * 60 * 60, // 24 hours
        ],
    ],

    'symfony/mailer' => [
        'esmtpTransport' => [
            'scheme' => $_ENV['SMTPSCHEME'], // "smtps": using TLS, "smtp": without using TLS.
            'host' => $_ENV['SMTPHOST'],
            'port' => (int)$_ENV['SMTPPORT'],
            'username' => $_ENV['SMTPUSERNAME'],
            'password' => $_ENV['SMTPPASSWORD'],
            'options' => ['verify_peer' => 0], // See: https://symfony.com/doc/current/mailer.html#tls-peer-verification
        ],
    ],

    'google/mailer' => [
        'emailSender' => $_ENV["EMAIL_SENDER"],
        'tokenInfoFile' => __DIR__ . "/../../runtime/gmail-api-token-info.json"
    ],

    'googleService' => [
        'configFile' => __DIR__ . "/google_service_account_credentials.json"
    ],

    'oauth' => [
        'google' => [
            'clientId' =>  $_ENV['GOOGLECLIENTID'],
            'clientSecret' => $_ENV['GOOGLECLIENTSECRET'],
            'redirectUri' =>  $_ENV['GOOGLEREDIRECTURI'],
        ],
        'facebook' => [
            'clientId' =>  $_ENV['FACEBOOKCLIENTID'],
            'clientSecret' => $_ENV['FACEBOOKCLIENTSECRET'],
            'redirectUri' =>  $_ENV['FACEBOOKREDIRECTURI'],
        ]
    ],

    'uploadedFiles' => [
        'car' => [
            'maxNumberOfUploadedFiles' => 5,
            'maxNumberOfAssignedDealerFiles' => 50,
            'maxNumberOfAssignedClientFiles' => 10,
            'maxUploadFileSize' => 50, // 50Mb,  1024 - 1Gb
            'allowedMimeTypesImages' => [
                "image/jpeg", // .jpeg
                "image/png", // .png
                "image/gif",
            ],
            'allowedMimeTypesVideos' => [
                "video/mp4",  // MP4 (MPEG-4)
                "video/webm", // WebM
                "video/x-msvideo", // AVI (Audio Video Interleave)
                "video/avi",
                "video/divx",
                "video/x-f4v",
                "video/x-m4v",
                "video/quicktime", // MOV (QuickTime Movie)
                "video/x-ms-wmv", // WMV (Windows Media Video)
                "video/x-flv", // FLV (Flash Video)
                "video/x-matroska", // MKV (Matroska Video)
                "video/ogg",  // OGG (Ogg Video)
                "application/octet-stream"
            ],
            'allowedMimeTypes' => [
                "image/jpeg", // .jpeg
                "image/png", // .png
                "image/gif",

                "video/mp4",  // MP4 (MPEG-4)
                "video/webm", // WebM
                "video/x-msvideo", // AVI (Audio Video Interleave)
                "video/avi",
                "video/divx",
                "video/x-f4v",
                "video/x-m4v",
                "video/quicktime", // MOV (QuickTime Movie)
                "video/x-ms-wmv", // WMV (Windows Media Video)
                "video/x-flv", // FLV (Flash Video)
                "video/x-matroska", // MKV (Matroska Video)
                "video/ogg",  // OGG (Ogg Video)
                "application/octet-stream"
            ],
            'catalogThumbnailWidth' => "250",
            'catalogThumbnailHeight' => "200",
            'galleryThumbnailWidth' => "160",
            'galleryThumbnailHeight' => "80",
        ],

        'logo' => [
            'maxUploadFileSize' => 1, // 1Mb
            'allowedMimeTypes' => [
                "image/jpeg", // .jpeg
                "image/png",  // .png
                "image/gif"
            ]
        ]
    ],

    'formats' => [
        'dateFormat' => "m/d/Y", // 01/01/2011
        'dateFormatShort' => "j M Y", // Jan 1, 2011
        'dateFormatLong' => "l, M j, Y", // Sunday, Jan 1, 2011
        'dateFormatJoined' => "F Y", // January, 2011
        'timeFormat' => "g:i A",   // 6:12 pm
        'currentCurrency' => "$"
    ],

    'settings' => [
        'maxNumberOfClientCars' => 10,
        'deferredUserDeletionDays' => 5,
    ],
];
