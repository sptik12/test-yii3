<?php

use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;
use App\Frontend\Asset\Admin\UserAsset;

/**
 * @var WebView               $this
 * @var TranslatorInterface   $translator
 * @var UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var Yiisoft\Router\CurrentRoute $currentRoute
 * @var string                $csrf
 */

$assetManager->register(UserAsset::class);
$this->setTitle($translator->translate("Account Managers"));
?>

<div id="vue-users">
    <h1 class="mb-4"><?= $translator->translate("Account Managers") ?></h1>
    <div class="page-top-part">
        <div class="d-flex align-items-center justify-content-start flex-wrap gap-3">
            <div class="filters-block">
                <div class="single-filter">
                    <label for="dateFilterFrom"><?= $translator->translate("Registered") ?></label>
                    <div class="flatpickr input-group" ref="dateFilterFromFlatpickr">
                        <input
                            type="text"
                            class="form-control app-table-filter app-date-from-filter"
                            readonly
                            placeholder="<?= $translator->translate("From") ?>"
                            id="dateFilterFrom"
                            ref="dateFilterFrom"
                            data-input
                        >
                        <a class="input-button input-group-tex clear-filter-button" title="clear" data-clear>
                            <svg class="icon" width="24" height="25">
                                <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                            </svg>
                        </a>
                    </div>
                    <span>-</span>
                    <div class="flatpickr input-group" ref="dateFilterToFlatpickr">
                        <input
                            type="text"
                            class="form-control app-table-filter app-date-to-filter"
                            readonly
                            placeholder="<?= $translator->translate("To") ?>"
                            id="dateFilterTo"
                            ref="dateFilterTo"
                            data-input
                        >
                        <a class="input-button input-group-tex clear-filter-button" title="clear" data-clear>
                            <svg class="icon" width="24" height="25">
                                <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                            </svg>
                        </a>
                    </div>
                </div>

                <a href="#" @click.prevent="clearFilters()"><?= $translator->translate("Clear filters") ?></a>
            </div>
        </div>
    </div>

    <div class="table-wrapper">
        <table
            class="stripe"
            ref="dataTable"
            v-init:data-table-ajax-url="'<?= $urlGenerator->generate("admin.searchAccountManagersAjax") ?>'"
        >
            <thead>
                <tr>
                    <th data-name="id" class="dt-body-left"><?= $translator->translate("Id") ?></th>
                    <th data-name="username"><?= $translator->translate("Name") ?></th>
                    <th data-name="email"><?= $translator->translate("Email") ?></th>
                    <th data-name="customComission"><?= $translator->translate("Comission") ?></th>
                    <th data-name="dealersCount" data-dt-order="disable" class="dt-body-left"><?= $translator->translate("Dealers") ?></th>
                    <th data-name="created"><?= $translator->translate("Registered") ?></th>
                    <th data-name="status"><?= $translator->translate("Status") ?></th>
                    <th data-name="action" data-dt-order="disable"></th>
                </tr>
            </thead>
        </table>
    </div>
</div>
