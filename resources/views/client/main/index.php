<?php

/**
 * @var WebView               $this
 * @var TranslatorInterface   $translator
 * @var UrlGeneratorInterface $urlGenerator
 * @var string                $csrf
 * @var Yiisoft\User\CurrentUser $currentUser
 */

$this->setTitle($translator->translate('Home page'));

?>


<div class="page-tmp">
    <div class="main-block mt-5 p-3">
        <?= "Current User: {$currentUserData}" ?>
        <br>
        Entity: Client. Controller: MainController. Action: index.
        <br>
        <?php if (!$isGuest) { ?>
            <a href="<?= $urlGenerator->generateAbsolute("client.logout") ?>">Logout</a> |
            <a href="<?= $urlGenerator->generateAbsolute("client.profile") ?>">Profile</a> |
        <?php } else { ?>
            <a href="<?= $urlGenerator->generateAbsolute("client.signIn") ?>">Login</a> |
        <?php } ?>
        <a href="<?= $urlGenerator->generateAbsolute("client.searchCar") ?>">Search Cars</a>
    </div>
</div>
