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
$this->setTitle($translator->translate('Dealer Cars'));
?>

<div class="catalog-page" id="vue-search-car">
     <?= $this->render('//common/car/_car-catalog-header-dealer', ['dealer' => $dealer]) ?>

    <div
        class="container"
        v-init:items="<?= Ancillary::forJs($items ?? []) ?>"
        v-init:totalCount="<?= $totalCount ?>"
        v-init:filters="<?= Ancillary::forJs($filters ?? []) ?>"
        v-init:filters-items-with-counts="<?= Ancillary::forJs($filtersItemsWithCounts ?? []) ?>"
        v-init:makes="<?= Ancillary::forJs($makes ?? []) ?>"
        v-init:models="<?= Ancillary::forJs($models ?? []) ?>"
        v-init:make-model-pairs-selects="<?= Ancillary::forJs($makeModelPairsSelects ?? []) ?>"
        v-init:url-search="'<?= $urlGenerator->generate('dealer.searchCarAjax') ?>'"
        v-init:url-push="'<?= $urlGenerator->generate('dealer.searchCar') ?>'"
        v-init:route-name="'dealer.searchCar'"
        v-init:get-models-ajax-url="'<?= $urlGenerator->generate('dealer.getModelsForViewAjax') ?>'"
    >
        <div class="row">

            <div class="col-xl-3 col-lg-4">
                <div class="filter-block app-filter-block">
                    <?= $this->render('//common/car/_car-catalog-sidebar-filters-title') ?>
                    <div class="all-filters app-all-filters">
                        <?= $this->render('_search-filters') ?>
                    </div>
                </div>
            </div>

            <div class="col-xl-9 col-lg-8">
                <div class="catalog-block">
                    <h1><?= $translator->translate("My Dealership") ?></h1>
                    <a href="<?= $urlGenerator->generateAbsolute("dealer.addCar") ?>" class="btn btn-primary"><?= $translator->translate("Add New Car") ?></a>
                    <div class="sort-block" v-show="items.length > 0">
                        <div class="left-part">
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
                                <option value="-car.created"><?= $translator->translate("Newest created listings first") ?></option>
                                <option value="car.created"><?= $translator->translate("Oldest created listings first") ?></option>
                                <option value="-car.published"><?= $translator->translate("Newest published listings first") ?></option>
                                <option value="car.published"><?= $translator->translate("Oldest published listings first") ?></option>
                                <option value="car.price"><?= $translator->translate("Lowest price first") ?></option>
                                <option value="-car.price"><?= $translator->translate("Highest price first") ?></option>
                            </select>
                            <div class="results-count">
                                <span>{{ totalCount }} <?= $translator->translate("results") ?></span>
                            </div>
                        </div>
                        <div class="right-part app-sticky-filters">
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

                    <?= $this->render('//common/car/_car-catalog-filters-row') ?>

                    <div class="card-items-list" ref="cardItemsList">

                        <div class="single-card" v-for="car in items">
                            <a :href="car.dealerEditUrl">
                                <?= $this->render('//common/car/_car-catalog-card') ?>
                            </a>
                            <a
                                :href="car.dealerEditUrl"
                                class="add-to-wishlist"
                                title="<?= $translator->translate("Edit car") ?>"
                            >
                                <svg class="icon" width="18" height="19">
                                    <use xlink:href="/images/sprites/sprites.svg#icon-pencil"></use>
                                </svg>
                            </a>
                        </div>

                        <div class="no-items" v-show="items.length==0">
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
