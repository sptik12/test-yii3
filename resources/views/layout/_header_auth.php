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

$isDealerSignUp = $currentRoute->getName() == "client.signUpDealership";
$isClientSignUp = $currentRoute->getName() == "client.signUp";
?>

<header class="header-wrapper <?= (!$isDealerSignUp) ? "header-auth" : "" ?>">
    <div class="bottom-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-2 col-md-5 col-5">
                    <div class="logo-block">
                        <a href="/">
                            <img src="/images/theme/<?= (!$isDealerSignUp) ? "logo-2.svg" : "logo.svg" ?>" alt="">
                        </a>
                    </div>
                </div>
                <div class="col-lg-7 col-md-1 col-1">
                </div>
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="profile-block">
                        <div class="user-block">
                            <a href="<?= $isClientSignUp || $isDealerSignUp ? "/sign-in" : "/sign-up" ?>" class="btn btn-outline btn-small">
                                <?= $isClientSignUp  || $isDealerSignUp ? $translator->translate("Sign in") : $translator->translate("Sign up") ?>
                                <svg class="icon" width="18" height="19">
                                    <use xlink:href="/images/sprites/sprites.svg#icon-user"></use>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
