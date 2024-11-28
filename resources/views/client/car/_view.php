<?php

use App\Backend\Model\Dealer\DealerStatus;
use App\Frontend\Asset\Common\ViewCarAsset;
use App\Frontend\Helper\Ancillary;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

/**
 * @var WebView               $this
 * @var TranslatorInterface   $translator
 * @var UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var string                $csrf
 * @var Yiisoft\User\CurrentUser $currentUser
 * @var Yiisoft\Aliases\Aliases $aliases
 *
 */

$assetManager->register(ViewCarAsset::class);
?>

<div class="card-page" id="vue-view-car">
    <div
        class="container"
        v-init:car="<?= Ancillary::forJs($car ?? []) ?>"
        v-init:preview="<?= $preview ?>"
        v-init:add-car-to-wishlist-ajax-url="'<?= $urlGenerator->generate('client.addCarToWishlistAjax') ?>'"
        v-init:remove-car-from-wishlist-ajax-url="'<?= $urlGenerator->generate('client.removeCarFromWishlistAjax') ?>'"
        v-init:publish-car-ajax-url = "'<?= $urlGenerator->generateAbsolute('client.doPublishCarFromPreviewAjax') ?>'"
        v-init:save-draft-car-ajax-url = "'<?= $urlGenerator->generateAbsolute('client.doSaveDraftCarFromPreviewAjax') ?>'"
        v-init:search-cars-url = "'<?= $lastSearchCarUrl ?>'"
        v-init:message-car-saved-to-wishlist="'<?= $translator->translate("Car was added to your wishlist") ?>'"
        v-init:message-car-removed-from-wishlist="'<?= $translator->translate("Car was removed from your wishlist") ?>'"
    >
        <div class="row">
            <div class="col-12">
                <?php
                    if ($preview) {
                        echo $this->render('_view-preview-row', compact("car", "allowPublish"));
                    }
echo $this->render('_view-prev-next-row', compact("preview", "lastSearchCarUrl", "carNextPublicId", "carPrevPublicId"));
?>
            </div>
            <?= $this->render('//common/car/_view', compact("preview", "car")); ?>
        </div>
    </div>
</div>
