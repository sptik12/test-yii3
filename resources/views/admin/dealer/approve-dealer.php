<?php

use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;
use App\Frontend\Asset\Admin\ApproveDealerAsset;

/**
 * @var WebView $this
 * @var TranslatorInterface $translator
 * @var UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var string $csrf
 * @var Yiisoft\User\CurrentUser $currentUser
 */

$assetManager->register(ApproveDealerAsset::class);
$this->setTitle($translator->translate('Approve Dealer'));
?>

<div id="vue-approve-dealer">
    <h1 class="mb-4"><?= $translator->translate("Approve dealer") ?></h1>
    <div class="mb-1">
        <?= $translator->translate("You are about to approve the following dealer: {dealerName}", ['dealerName' => $dealer->name]) ?><br>
        <?= $translator->translate("Click 'Approve' to confirm the action.") ?>
    </div>
    <div
        class="mb-1"
        v-init:search-dealer-ajax-url="'<?= $urlGenerator->generate("admin.dealers") ?>'"
        v-init:success-message ="'<?= $translator->translate("Dealer {dealerName} has been approved", ['dealerName' => $dealer->name]) ?>'"
        v-init:cancel-message ="'<?= $translator->translate("Dealer {dealerName} remains unapproved", ['dealerName' => $dealer->name]) ?>'"
    >
        <a
            href="<?= $urlGenerator->generate("admin.doApproveDealerAjax") ?>"
            @click.stop.prevent="doApproveDealer($event, <?= $dealer->id ?>)"
        >
            <?= $translator->translate("Approve") ?>
        </a> |
        <a
            href="#"
            @click.stop.prevent="cancelApproveDealer($event, <?= $dealer->id ?>)"
        >
            <?= $translator->translate("Cancel") ?>
        </a>
    </div>
</div>
