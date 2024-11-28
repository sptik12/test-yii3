<?php

use App\Frontend\Asset\Client\SearchCarUrlAsset;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;
use App\Frontend\Helper\Ancillary;
use App\Backend\Model\Car\CarSearchUrlStatus;

/**
 * @var WebView $this
 * @var TranslatorInterface $translator
 * @var UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var string $csrf
 * @var Yiisoft\User\CurrentUser $currentUser
 */

$assetManager->register(SearchCarUrlAsset::class);
$this->setTitle($translator->translate('Wishlist'));
?>

<div class="wishlist-page" id="vue-search-car">
    <div
        class="container"
        v-init:items="<?= Ancillary::forJs($items ?? []) ?>"
        v-init:totalCount="<?= $totalCount ?>"
        v-init:url-search="'<?= $urlGenerator->generate('client.carSearchUrlsAjax') ?>'"
        v-init:url-push="'<?= $urlGenerator->generate('client.carSearchUrls') ?>'"
        v-init:delete-car-search-url-ajax-url="'<?= $urlGenerator->generate('client.deleteCarSearchUrlAjax') ?>'"
        v-init:restore-car-search-url-ajax-url="'<?= $urlGenerator->generate('client.restoreCarSearchUrlAjax') ?>'"
        v-init:message-car-search-url-removed="'<?= $translator->translate("Search was removed from your wishlist") ?>'"
        v-init:message-car-search-url-restored="'<?= $translator->translate("Search was restored to your wishlist") ?>'"
        v-init:label-car-search-url-deleted="'<?= $translator->translate("Search was removed from your wishlist. Do you want to restore it?") ?>'"
        v-init:label-yes="'<?= $translator->translate("Yes") ?>'"
        v-init:label-no="'<?= $translator->translate("No") ?>'"

    >

        <div class="row">
            <div class="col-xl-12 col-lg-12">
                <div class="catalog-block">
                    <h1><?= $translator->translate("Wishlist") ?></h1>

                    <div class="sort-block">
                        <?= $this->render('//common/car/_saved-tabs', ['active' => 'savedUrls']) ?>
                        <div class="right-part app-sticky-filters">
                            <select
                                class="sort-select app-tom-select default-tom-select-no-empty"
                                ref="sort"
                                name="sort"
                                id="sort"
                                v-init:sort="'<?= $sort ?>'"
                                v-init:sort-order="'<?= $sortOrder ?>'"
                                v-model="sortValue"
                                @change.prevent="changeSort($event)"
                            >
                                <option value="-carSearchUrl.created"><?= $translator->translate("Newest first") ?></option>
                                <option value="carSearchUrl.created"><?= $translator->translate("Oldest first") ?></option>
                                <option value="carSearchUrl.title"><?= $translator->translate("By Title ascending") ?></option>
                                <option value="-carSearchUrl.title"><?= $translator->translate("By Title descending") ?></option>
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
                        <div class="tab-pane fade show active" id="saved-cars-tab-pane" role="tabpanel"  tabindex="0">
                            <div class="" ref="cardItemsList">
                                <div class="saved-searches-block" v-for="carSearchUrl in items" data->
                                    <div class="search-container app-search-container" :data-id="carSearchUrl.id">
                                        <div class="search-title">
                                            <div class="text-medium">{{ carSearchUrl.title }}</div>
                                            <a href="#" @click.prevent="deleteCarSearchUrl($event, carSearchUrl.id)">
                                                <svg class="icon" width="20" height="20">
                                                    <use xlink:href="/images/sprites/sprites.svg#close-circle"></use>
                                                </svg>
                                            </a>
                                        </div>
                                        <div class="search-tags">
                                            <div class="search-tag" v-for="displayedFilter in carSearchUrl.filters">{{ displayedFilter.text }}</div>
                                        </div>
                                        <a :href="carSearchUrl.url" target="_blank" class="view-link">
                                            <div class="text-medium"><?= $translator->translate("View") ?></div>
                                            <svg class="icon" width="18" height="18"><use xlink:href="/images/sprites/sprites.svg#icon-arrow-square"></use></svg>
                                        </a>
                                    </div>
                                    <div class="deleted-item-block d-none app-deleted-item-block" :data-id="carSearchUrl.id">
                                        <div class="search-container">
                                            <div class="deleted-img">
                                                <img src="/images/theme/objects.png" alt="">
                                            </div>
                                            <div class="deleted-title">
                                                <h3 class="text-medium"><?= $translator->translate("Deleted item") ?></h3>
                                                <div class="deleted-title-tags">
                                                    <?= $translator->translate("This item has been deleted") ?>
                                                    <a href="#" class="view-link" @click.prevent="restoreCarSearchUrl($event, carSearchUrl.id)">
                                                        <div class="text-medium"><?= $translator->translate("Undo") ?></div>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="no-items" v-show="items.length==0">
                                    <?= $translator->translate("Search list is empty") ?>
                                </div>
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
