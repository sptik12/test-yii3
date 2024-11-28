<?php

declare(strict_types=1);

/**
 * @var Yiisoft\View\WebView $this
 * @var App\Frontend\ApplicationParameters $applicationParameters
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\Router\CurrentRoute $currentRoute
 * @var \Yiisoft\Translator\TranslatorInterface $translator
 */

$title = $translator->translate("Session Expired");
$this->setTitle($title);
?>

<div class="error-page">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-1"></div>
            <div class="col-lg-5">
                <div class="error-info">
                    <h2><?= $title ?></h2>
                    <h3><?= $translator->translate("Your session is expired. Please, click the button below to restore session and go back to the page to fill the form again.") ?></h3>
                    <a href="<?= $returnUrl ?>" class="btn btn-primary btn-big"><?= $translator->translate("Restore session") ?></a>
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
