<?php

declare(strict_types=1);

/**
 * @var App\Frontend\ApplicationParameters $applicationParameters
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

<header class="header-wrapper">
    <div class="bottom-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-2 col-md-5 col-5">
                    <div class="logo-block">
                        <div class="toggle-button">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                        <a href="<?= $urlGenerator->generateAbsolute("client.searchCar") ?>">
                            <img src="/images/theme/logo.svg" alt="">
                        </a>
                    </div>
                </div>
                <div class="col-lg-7 col-md-1 col-1">
                    <div class="menu-block">
                        <ul>
                            <li class="item-with-children">
                                <a href="#">
                                    Buy My Car
                                </a>
                                <ul class="sub-menu">
                                    <li>
                                        <a href="#">Shop used cars</a>
                                    </li>
                                    <li>
                                        <a href="#">Shop new cars</a>
                                    </li>
                                    <li>
                                        <a href="#">Start your purchase online</a>
                                    </li>
                                    <li>
                                        <a href="#">Buy 100% online</a>
                                    </li>
                                    <li>
                                        <a href="#">Shop certified used cars</a>
                                    </li>
                                    <li>
                                        <a href="#">Dealerships near me</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="item-with-children">
                                <a href="#">Finance</a>
                                <ul class="sub-menu">
                                    <li>
                                        <a href="#">Test</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="item-with-children">
                                <a href="#">Research</a>
                                <ul class="sub-menu">
                                    <li>
                                        <a href="#">Test</a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="#">Prequalify for a Loan</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="profile-block">
                        <div class="notification-block">
                            <div class="dropdown">
                                <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <svg class="icon" width="18" height="19">
                                        <use xlink:href="/images/sprites/sprites.svg#icon-notifications"></use>
                                    </svg>
                                </button>
                                <div class="dropdown-menu">
                                    <p class="text-center">Nothing found</p>
                                </div>
                            </div>
                        </div>
                        <div class="user-block">
                            <div class="btn-group">
                                <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <svg class="icon" width="18" height="19">
                                        <use xlink:href="/images/sprites/sprites.svg#icon-user"></use>
                                    </svg>
                                    <?= $currentUser->getIdentity()->username ?>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><h6><?= $translator->translate("User profile") ?></h6></li>
                                    <li>
                                        <a href="<?= $urlGenerator->generateAbsolute("client.profile") ?>">
                                            <?= $translator->translate("My account") ?>
                                        </a>
                                    </li>
                                    <?php if ($currentDealerId) { ?>
                                    <li>
                                        <a href="<?= $urlGenerator->generateAbsolute("dealer.searchCar") ?>">
                                            <?= $translator->translate("My Dealership") ?>
                                        </a>
                                    </li>
                                    <?php } ?>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a href="<?= $urlGenerator->generateAbsolute("client.logout") ?>"><?= $translator->translate("Sign out") ?></a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
