<?php

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
 */

?>
<div class="main-filters">
    <div class="filter-part">
        <?= $this->render('//common/car/_car-catalog-sidebar-filters-make-model') ?>

        <div class="distance-block postcode-block flex-wrap" v-show="!filters.dealer">
            <div class="postcode-block flex-nowrap">
                <label for="postalCode"><?= $translator->translate("Postal Code") ?></label>
                <input
                    type="text"
                    id="postalCode"
                    name="postalCode"
                    class="form-control"
                    placeholder="<?= $translator->translate("Postal Code") ?>"
                    pattern="(?:[A-Z]\d[A-Z]|\b[A-Z]\d[A-Z][ ]?\d[A-Z]\d\b)"
                    title="<?= $translator->translate("Please enter a valid postal code of 3 or 6 alphanumeric characters (e.g. A1B or A1B 2C3)") ?>"
                    ref="postalCode"
                    v-model="inputValues.postalCode"
                >
                <button type="button" class="geo-location" @click.prevent="getPosition()">
                    <svg class="icon" width="20" height="20">
                        <use xlink:href="/images/sprites/sprites.svg#icon-gps"></use>
                    </svg>
                </button>
            </div>
            <button
                type="button"
                @click.prevent="validatePostalCodeAndApplyFilters()"
                class="btn btn-primary w-100"
                :disabled="!isPostalCodeChanged || isEmpty(inputValues.postalCode)"
            >
                <?= $translator->translate("Ok") ?>
            </button>
            <div class="distance-block flex-nowrap" v-show="!isEmpty(filters.postalCode)">
                <label for="distance"><?= $translator->translate("Distance from me") ?></label>
                <select
                    id="distance"
                    name="distance"
                    class="form-select default-tom-select app-distance"
                    v-model="filters.distance"
                    @change.prevent="applyFilters()"
                >
                    <option value=""><?= $translator->translate("National") ?></option>
                    <option value="25"><?= $translator->translate("25 km") ?></option>
                    <option value="50"><?= $translator->translate("50 km") ?></option>
                    <option value="100"><?= $translator->translate("100 km") ?></option>
                    <option value="250"><?= $translator->translate("250 km") ?></option>
                    <option value="500"><?= $translator->translate("500 km") ?></option>
                    <option value="1000"><?= $translator->translate("1 000 km") ?></option>
                    <option value="provincial"><?= $translator->translate("Provincial") ?></option>
                </select>
            </div>
        </div>
    </div>
</div>
