<?php

/**
* @var Yiisoft\Aliases\Aliases $aliases
* @var Yiisoft\Assets\AssetManager $assetManager
* @var string $content
* @var string|null $csrf
* @var Locale $locale
* @var Yiisoft\View\WebView $this
* @var TranslatorInterface $translator
* @var Yiisoft\Router\CurrentRoute $currentRoute
* @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
* @var Yiisoft\User\CurrentUser $currentUser
*/

?>
<?php if ($displayNotApprovedMessage) { ?>
    <footer class="footer-wrapper footer-dealer footer-dealer-notification">
<?php } else { ?>
    <footer class="footer-wrapper footer-dealer">
<?php } ?>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="copyright-block">
                        <p class="mb-0">
                            ©
                            <?= date("Y") ?>
                            <?= $translator->translate("{appName}, Inc., All Rights Reserved.", ['appName' => $applicationParameters->getName()]) ?>
                        </p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="footer-widget">
                        <ul class="footer-menu">
                            <li>
                                <a href="#"><?= $translator->translate("Terms & Conditions") ?></a>
                            </li>
                            <li>
                                <a href="#"><?= $translator->translate("Privacy Notice") ?></a>
                            </li>
                        </ul>
                        <div class="dropdown flag-dropdown">
                            <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="/images/flags/<?= $this->getLocale() == "fr-FR" ? "FR" : "CAN" ?>.svg">
                            </button>
                            <div class="dropdown-menu">
                                <a href="<?= $urlGenerator->generateFromCurrent(['_language' => "en"]) ?>">
                                    <img src="/images/flags/CAN.svg" title="<?= $translator->translate("Switch to english") ?>">
                                </a>
                                <a href="<?= $urlGenerator->generateFromCurrent(['_language' => "fr"]) ?>">
                                    <img src="/images/flags/FR.svg" title="<?= $translator->translate("Switch to french") ?>">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
