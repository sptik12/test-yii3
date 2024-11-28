<?php

use App\Frontend\Asset\Common\AddCarAsset;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

/**
* @var WebView               $this
* @var TranslatorInterface   $translator
* @var UrlGeneratorInterface $urlGenerator
* @var Yiisoft\Assets\AssetManager $assetManager
* @var string                $csrf
* @var string                $filled
 */

$assetManager->register(AddCarAsset::class);
?>

<div class="add-car-page-wrapper" id="vue-add-car">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="cancel-button-block">
                    <a href="<?= $lastSearchCarUrl ?>">
                        <svg class="icon" width="24" height="24">
                            <use xlink:href="/images/sprites/sprites.svg#icon-back-arrow"></use>
                        </svg>
                        <?= $translator->translate("Cancel") ?>
                    </a>
                </div>
            </div>
            <div class="col-xl-2"></div>
            <div class="col-xl-8">
                <div class="box-wrapper">
                    <h1><?= $translator->translate("Add Car") ?></h1>
                    <div class="form-wrapper">
                        <form action="<?= $doAddCarUrl ?>" method="post" ref="addCarForm">
                            <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                            <div class="inline-form-group">
                                <div class="form-group">
                                    <label for="vinCode"><?= $translator->translate("Vin Code") ?></label>
                                    <input
                                        type="text"
                                        maxlength="17"
                                        class="form-control form-control-lg"
                                        id="vinCode"
                                        name="vinCode"
                                        pattern="[0-9A-Z]{17}"
                                        placeholder="<?= $translator->translate("Enter VIN") ?>"
                                        title="<?= $translator->translate("The VIN number contains 17 characters, including digits and capital letters") ?>"
                                        required
                                        value="<?= $filled->vinCode ?? "" ?>"
                                    >
                                    <div class="app-error-container invalid-tooltip" for="vinCode"></div>
                                </div>
                                <div class="form-group">
                                    <input
                                        @click.prevent="submitForm($event)"
                                        type="submit"
                                        class="submit-btn btn btn-primary btn-big w-100"
                                        value="<?= $translator->translate("Continue") ?>"
                                    >
                                </div>
                            </div>
                        </form>
                        <form action="<?= $doAddEmptyCarUrl ?>" method="post">
                            <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                            <div class="w-100 text-center text-lg-start">
                                <button class="custom-link" type="submit">
                                    <?= $translator->translate("I'll fill it out manually") ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-xl-2"></div>
        </div>
    </div>
</div>
