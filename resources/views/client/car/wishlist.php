<?php

use App\Frontend\Asset\Common\SearchCarAsset;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;
use App\Frontend\Helper\Ancillary;

/**
 * @var WebView $this
 * @var TranslatorInterface $translator
 * @var UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var string $csrf
 * @var Yiisoft\User\CurrentUser $currentUser
 */

$assetManager->register(SearchCarAsset::class);
$this->setTitle($translator->translate('Wishlist'));
?>

<div class="wishlist-page" id="vue-search-car">
    <div
        class="container"
        v-init:items="<?= Ancillary::forJs($items ?? []) ?>"
        v-init:totalCount="<?= $totalCount ?>"
        v-init:filters="<?= Ancillary::forJs($filters ?? []) ?>"
        v-init:filters-items-with-counts="<?= Ancillary::forJs($filtersItemsWithCounts ?? []) ?>"
        v-init:makes="<?= Ancillary::forJs($makes ?? []) ?>"
        v-init:models="<?= Ancillary::forJs($models ?? []) ?>"
        v-init:make-model-pairs-selects="<?= Ancillary::forJs($makeModelPairsSelects ?? []) ?>"
        v-init:url-search="'<?= $urlGenerator->generate('client.wishlistAjax') ?>'"
        v-init:url-push="'<?= $urlGenerator->generate('client.wishlist') ?>'"
        v-init:route-name="'client.wishlist'"
        v-init:get-models-ajax-url="'<?= $urlGenerator->generate('client.getModelsForViewAjax') ?>'"
        v-init:add-car-to-wishlist-ajax-url="'<?= $urlGenerator->generate('client.addCarToWishlistAjax') ?>'"
        v-init:remove-car-from-wishlist-ajax-url="'<?= $urlGenerator->generate('client.removeCarFromWishlistAjax') ?>'"
        v-init:message-car-saved-to-wishlist="'<?= $translator->translate("Car was added ro your wishlist") ?>'"
        v-init:message-car-removed-from-wishlist="'<?= $translator->translate("Car was removed from your wishlist") ?>'"
    >
        <div class="row">
            <div class="col-12">
                <div class="catalog-block">
                    <h1><?= $translator->translate("Wishlist") ?></h1>
                    <div class="sort-block">
                        <?= $this->render('//common/car/_saved-tabs', ['active' => 'savedCars']) ?>
                        <div class="right-part app-sticky-filters" v-show="items.length > 0">
                            <select
                                ref="sort"
                                name="sort"
                                id="sort"
                                class="sort-select default-tom-select-no-empty"
                                v-init:sort="'<?= $sort ?>'"
                                v-init:sort-order="'<?= $sortOrder ?>'"
                                v-model="sortValue"
                                @change.prevent="changeSort($event)"
                            >
                                <option value="-car.published"><?= $translator->translate("Newest listings first") ?></option>
                                <option value="car.published"><?= $translator->translate("Oldest listings first") ?></option>
                                <option value="car.price"><?= $translator->translate("Lowest price first") ?></option>
                                <option value="-car.price"><?= $translator->translate("Highest price first") ?></option>
                                <option value="car.mileage"><?= $translator->translate("Lowest mileage first") ?></option>
                                <option value="-car.mileage"><?= $translator->translate("Highest mileage first") ?></option>
                                <option value="-car.year"><?= $translator->translate("Newest first (by car year)") ?></option>
                                <option value="car.year"><?= $translator->translate("Oldest first (by car year)") ?></option>
                            </select>
                            <div class="results-count">
                                <span>{{ totalCount }} <?= $translator->translate("results") ?></span>
                            </div>

                            <div class="mobile-buttons d-lg-none">
                                <button
                                    class="secondary-filter-button app-secondary-filter-button d-lg-none"
                                    :class="{ hasfilters: filtersCount > 0 }"
                                >
                                    <svg class="icon" width="25" height="24">
                                        <use xlink:href="/images/sprites/sprites.svg#icon-filter"></use>
                                    </svg>
                                    <span v-show="filtersCount > 0">{{ filtersCount }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="saved-cars-tab-pane" role="tabpanel" aria-labelledby="saved-cars-tab" tabindex="0">

                            <div class="card-items-list" ref="cardItemsList">
                                <div class="single-card" v-for="car in items">
                                    <a :href="car.clientViewUrl">
                                        <?= $this->render('//common/car/_car-catalog-card') ?>
                                    </a>
                                    <a href="#"
                                       v-if="car.canSaveCarToWishlist"
                                       class="add-to-wishlist"
                                       :class="{active: car.isCarSaved == 1}"
                                       @click.prevent="toggleSavedCar(car.id, true)"
                                    >
                                        <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M9.965 16.5581C9.71 16.6481 9.29 16.6481 9.035 16.5581C6.86 15.8156 2 12.7181 2 7.46813C2 5.15063 3.8675 3.27563 6.17 3.27563C7.535 3.27563 8.7425 3.93563 9.5 4.95563C10.2575 3.93563 11.4725 3.27563 12.83 3.27563C15.1325 3.27563 17 5.15063 17 7.46813C17 12.7181 12.14 15.8156 9.965 16.5581Z" fill="#FB4A4A" stroke="#FB4A4A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                </div>

                                <div class="no-items w-100" v-show="items.length==0">
                                    <?= $translator->translate("Cars list is empty") ?>
                                </div>
                                <?= $this->render('//common/car/_car-catalog-banners') ?>
                            </div>
                            <div
                                class="catalog-pagination"
                                v-init:current-page="'<?= $page ?>'"
                                v-init:per-page="'<?= $perPage ?>'"
                                v-init:total-count="'<?= $totalCount ?>'"
                                v-show="totalPages>1"
                            >
                                <?= $this->render('//common/_pages') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
