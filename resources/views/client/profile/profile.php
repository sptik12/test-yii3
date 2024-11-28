<?php

/**
* @var WebView               $this
* @var TranslatorInterface   $translator
* @var UrlGeneratorInterface $urlGenerator
* @var string                $csrf
* @var Yiisoft\User\CurrentUser $currentUser
*/

$this->setTitle($translator->translate('Profile'));

?>
<div class="page-tmp">
    <div class="profile-main-block mt-5 p-3">
        <?= "Current User: {$currentUserData}" ?>
        <br>
        Entity: Client. Controller: ProfileController. Action: profile.
        <br>
        <a href="<?= $urlGenerator->generateAbsolute("client.logout") ?>">Logout</a>
        <?php if ($isClient) { ?>
            <br>
            <a href="<?= $clientHomeUrl?>">Client Panel</a>
        <?php } ?>
        <?php if ($isAdmin) { ?>
            <br>
            <a href="<?= $adminHomeUrl?>">Admin Panel</a>
        <?php } ?>
        <?php if ($isDealer) { ?>
            <br>
            <a href="<?= $dealerHomeUrl?>">Dealer Panel</a>
        <?php } ?>
    </div>
</div>
