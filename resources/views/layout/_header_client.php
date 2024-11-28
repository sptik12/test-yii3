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
    <div class="top-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-2"></div>
                <div class="col-lg-8">
                    <div class="top-menu-block">
                        <div class="mobile-top-menu-close-button app-mobile-top-menu-close-button">
                            <a href="#" class="d-flex align-items-center justify-content-start">
                                <svg class="icon" width="18" height="18">
                                    <use xlink:href="/images/sprites/sprites.svg#icon-arrow-square"></use>
                                </svg>
                                Back
                            </a>
                        </div>
                        <ul>
                            <li>
                                <a href="<?= $urlGenerator->generateAbsolute("client.searchCar") ?>">
                                    <?= $translator->translate("Cars") ?>
                                </a>
                            </li>
                            <li><a href="#">Trucks</a></li>
                            <li><a href="#">Trailers</a></li>
                            <li class="item-with-children">
                                <a href="#">Bikes & ATVs</a>
                                <ul class="sub-menu">
                                    <li>
                                        <a href="#">Boats</a>
                                    </li>
                                    <li>
                                        <a href="#">Watercraft</a>
                                    </li>
                                    <li>
                                        <a href="#">Bikes & ATVs</a>
                                    </li>
                                </ul>
                            </li>
                            <li><a href="#">RVs</a></li>
                            <li class="item-with-children">
                                <a href="#">Heavy Equipment</a>
                                <ul class="sub-menu">
                                    <li>
                                        <a href="#">Heavy Equipment</a>
                                    </li>
                                    <li>
                                        <a href="#">Farm</a>
                                    </li>
                                </ul>
                            </li>
                            <li><a href="#">Snowmobiles</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-2">
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
                        <div class="mobile-top-menu-button app-mobile-top-menu-button">
                            <a href="#">
                                <svg class="icon" width="18" height="18">
                                    <use xlink:href="/images/sprites/sprites.svg#icon-arrow-square"></use>
                                </svg>
                                Car
                            </a>
                        </div>
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
                                <a href="#"><?= $translator->translate("Sell My Car") ?></a>
                                <ul class="sub-menu">
                                    <li>
                                        <a href="<?= $urlGenerator->generateAbsolute("client.myCars") ?>"><?= $translator->translate("My selling cars") ?></a>
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
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="profile-block">
                        <?php if (!$userId) { ?>
                            <div class="user-block">
                                <a href="/sign-in" class="btn btn-outline btn-small">
                                    <?= $translator->translate("Sign in") ?>
                                    <svg class="icon" width="18" height="19">
                                        <use xlink:href="/images/sprites/sprites.svg#icon-user"></use>
                                    </svg>
                                </a>
                            </div>
                        <?php } else { ?>
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
                            <div class="wishlist-block">
                                <div class="dropdown">
                                    <a
                                        href="<?= $urlGenerator->generateAbsolute("client.wishlist") ?>"
                                        class="dropdown-toggle"
                                        type="button"
                                        title="<?= $translator->translate("Saved cars and searches") ?>"
                                    >
                                        <svg class="icon" width="18" height="19">
                                            <use xlink:href="/images/sprites/sprites.svg#icon-heart"></use>
                                        </svg>
                                    </a>
                                    <div class="dropdown-menu">
                                        <p class="text-center"></p>
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
                                                <?= $translator->translate("My dealership") ?>
                                            </a>
                                        </li>
                                        <?php } ?>
                                        <li>
                                            <a href="<?= $urlGenerator->generateAbsolute("client.myCars") ?>">
                                                <?= $translator->translate("My selling cars") ?>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?= $urlGenerator->generateAbsolute("client.carSearchUrls") ?>">
                                                <?= $translator->translate("Saved searches") ?>
                                                <span class="badge rounded-pill bg-primary app-car-search-urls-count"><?= $carSearchUrlsCount ? $carSearchUrlsCount : '' ?></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?= $urlGenerator->generateAbsolute("client.wishlist") ?>">
                                                <?= $translator->translate("Saved cars") ?>
                                                <span class="badge rounded-pill bg-primary app-car-users-count"><?= $carUsersCount ? $carUsersCount : '' ?></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#">Recommended cars</a>
                                        </li>
                                        <li>
                                            <a href="#">Browsing history</a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <a href="<?= $urlGenerator->generateAbsolute("client.logout") ?>"><?= $translator->translate("Sign out") ?></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
