<?php

declare(strict_types=1);

use Yiisoft\Html\Html;

/**
 * @var Yiisoft\View\WebView $this
 * @var App\Frontend\ApplicationParameters $applicationParameters
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\Router\CurrentRoute $currentRoute
 * @var \Yiisoft\Translator\TranslatorInterface $translator
 */

$title = $translator->translate("Not Found");
$this->setTitle($title);
?>

<div class="error-page">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-1"></div>
            <div class="col-lg-5">
                <div class="error-info">
                    <h1><?= $title ?></h1>
                    <h2><?= $translator->translate("The page {url} not found.", ['url' => "<b>" . Html::encode($currentRoute->getUri()->getPath()) . "</b>"]) ?></h2>
                    <p><?= $translator->translate("The above error occurred while the Web server was processing your request. Please contact us if you think this is a server error. Thank you.") ?></p>
                    <a href="/" class="btn btn-primary btn-big"><?= $translator->translate("Go Home") ?></a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="error-image">
                    <img src="/images/theme/404.svg" alt="">
                </div>
            </div>
            <div class="col-lg-1"></div>
        </div>
    </div>
</div>
