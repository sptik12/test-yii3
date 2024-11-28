<?php

use App\Frontend\Asset\Common\EditCarAsset;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;
use App\Frontend\Helper\Ancillary;

/**
 * @var WebView               $this
 * @var TranslatorInterface   $translator
 * @var UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var string                $csrf
 */

$assetManager->register(EditCarAsset::class);
$this->setTitle($translator->translate('Edit Car'));

$submitButtons = $this->render('_edit-car-submit-buttons', ["car" => $car, "withPreviewButton" => true])

?>

<div class="add-car-page-wrapper eddit-car-page-wrapper" id="vue-edit-car">
    <div class="container"
         v-init:car="<?= Ancillary::forJs($car ?? []) ?>"
         v-init:models ="<?= Ancillary::forJs($models ?? []) ?>"
         v-init:get-models-ajax-url="'<?= $urlGenerator->generate('client.getModelsForEditAjax') ?>'"
         v-init:get-vin-code-data-ajax-url="'<?= $urlGenerator->generate('client.getVinCodeDataAjax') ?>'"
         v-init:delete-media-ajax-url="'<?= $urlGenerator->generate('client.deleteMediaAjax') ?>'"
         v-init:set-media-main-ajax-url="'<?= $urlGenerator->generate('client.setMediaMainAjax') ?>'"
         v-init:sort-media-ajax-url="'<?= $urlGenerator->generate('client.sortMediaAjax') ?>'"
         v-init:upload-files-ajax-url="'<?= $urlGenerator->generate('client.uploadMediaAjax') ?>'"
         v-init:publish-car-ajax-url="'<?= $urlGenerator->generate('client.doPublishCarAjax') ?>'"
         v-init:preview-car-url="'<?= $urlGenerator->generateAbsolute('client.previewCar', ['publicId' => $car->publicId]) ?>'"
         v-init:update-preview-car-session-ajax-url="'<?= $urlGenerator->generate('client.updatePreviewCarSessionAjax') ?>'"
         v-init:restore-preview-car-session-ajax-url="'<?= $urlGenerator->generate('client.restorePreviewCarSessionAjax') ?>'"
         v-init:save-draft-car-url="'<?= $urlGenerator->generate('client.doSaveDraftCar') ?>'"
         v-init:search-cars-url = "'<?= $lastSearchCarUrl ?>'"
         v-init:max-number-of-uploaded-files="<?= $maxNumberOfUploadedFiles ?>"
         v-init:message-max-number-of-uploaded-files="'<?= $translator->translate("Total number of uploaded files cannot be more than {maxNumberOfUploadedFiles}", ["maxNumberOfUploadedFiles" => $maxNumberOfUploadedFiles]) ?>'"
         v-init:max-upload-file-size="<?= $maxUploadFileSize ?>"
         v-init:message-max-upload-file-size="'<?= $translator->translate("Size of each file should be less than {maxUploadFileSize} Mb", ["maxUploadFileSize" => $maxUploadFileSize]) ?>'"
         v-init:allowed-mime-types="<?= Ancillary::forJs($allowedMimeTypes ?? []) ?>"
         v-init:allowed-mime-types-images="<?= Ancillary::forJs($allowedMimeTypesImages ?? []) ?>"
         v-init:allowed-mime-types-videos="<?= Ancillary::forJs($allowedMimeTypesVideos ?? []) ?>"
         v-init:message-allowed-mime-types="'<?= $translator->translate("Media files only allowed for uploading") ?>'"
         v-init:message-allowed-mime-types-images="'<?= $translator->translate("Images only allowed for uploading") ?>'"
         v-init:message-allowed-mime-types-videos="'<?= $translator->translate("Videos only allowed for uploading") ?>'"
         v-init:max-number-of-assigned-files="<?= $maxNumberOfAssignedFiles ?>"
         v-init:message-max-number-of-assigned-files="'<?= $translator->translate("Total number of assigned files cannot be more than {maxNumberOfAssignedFiles}", ["maxNumberOfAssignedFiles" => $maxNumberOfAssignedFiles]) ?>'"
         v-init:message-dont-leave-page = "'<?= $translator->translate("Don\'t leave this page until medias are uploaded") ?>'"
         v-init:message-leave-page-confirm-media-processed = "'<?= $translator->translate("You are living this page, but your media files are still uploaded") ?>'"
         v-init:message-leave-page-confirm-unsaved-changes = "'<?= $translator->translate("You are living this page, but you have unsaved changes") ?>'"
         v-init:message-drag-main-media = "'<?= $translator->translate("Main media is always displayed at start of the list, it cannot moved") ?>'"
         v-init:unsaved-changes-exists = "<?= $unsavedChangesExists ?>"
         v-init:label-car-set-draft-from-published = "'<?= $translator->translate("You car is published. Are you sure to change car status to draft and remove it from catalog?") ?>'"
         v-init:label-confirm = "'<?= $translator->translate("Confirm") ?>'"
         v-init:label-cancel = "'<?= $translator->translate("Cancel") ?>'"
         v-init:message-vin-code-data-updated = "'<?= $translator->translate("Car properties were updated") ?>'"
    >
        <div class="row flex-column-reverse flex-xl-row">
            <?= $this->render('//common/car/_edit-car-preview-link', compact("lastSearchCarUrl", "submitButtons")) ?>
            <div class="col-xl-2">
                <div class="edit-items-list" ref="leftMenu">
                    <?= $this->render('//common/car/_edit-car-fill-indicators', ["car" => $car]) ?>
                    <?= $submitButtons ?>
                </div>
            </div>
            <div class="col-xl-8">
                <?= $this->render('//common/car/_edit-car-form', compact("car", "makes", "submitButtons")) ?>
            </div>
            <div class="col-xl-2"></div>
        </div>
    </div>
    <?= $this->render('//common/car/_edit-car-mobile-panel') ?>
</div>
