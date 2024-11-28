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
        v-init:publish-car-ajax-url = "'<?= $urlGenerator->generateAbsolute('dealer.doPublishCarFromPreviewAjax') ?>'"
        v-init:save-draft-car-ajax-url = "'<?= $urlGenerator->generateAbsolute('dealer.doSaveDraftCarFromPreviewAjax') ?>'"
        v-init:search-cars-url = "'<?= $lastSearchCarUrl ?>'"
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
