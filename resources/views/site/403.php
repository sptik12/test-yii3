<?php

declare(strict_types=1);

/**
 * @var Yiisoft\View\WebView $this
 * @var App\Frontend\ApplicationParameters $applicationParameters
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var \Yiisoft\Translator\TranslatorInterface $translator
 * @var Yiisoft\Router\CurrentRoute $currentRoute
 */

$title = $translator->translate("Forbidden");
$this->setTitle($title);
?>
<div class="error-page">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-1"></div>
            <div class="col-lg-5">
                <div class="error-info">
                    <h1><?= $title ?></h1>
                    <p><?= $translator->translate("Oops, looks like you cannot access this page. If this seems like an error on our part, please let us know.") ?></p>
                    <a href="<?= $urlGenerator->generateAbsolute("client.home") ?>" class="btn btn-primary btn-big">
                        <?= $translator->translate("Go Home") ?>
                    </a>
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