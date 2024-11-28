<?php

use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;
use App\Frontend\Asset\Admin\DealerAsset;

/**
 * @var WebView               $this
 * @var TranslatorInterface   $translator
 * @var UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var Yiisoft\Router\CurrentRoute $currentRoute
 * @var string                $csrf
 */

$assetManager->register(DealerAsset::class);
$this->setTitle($translator->translate("Dealers"));
?>

<div id="vue-dealers">
    <h1 class="mb-4"><?= $translator->translate("Dealers") ?></h1>
    <div class="page-top-part">
        <div class="d-flex align-items-center justify-content-start flex-wrap gap-3">
            <?php if ($canAddDealer) { ?>
            <a class="btn btn-primary" href="<?= $urlGenerator->generate("admin.addDealer") ?>">
                <svg class="icon" width="18" height="18">
                    <use xlink:href="/images/sprites/sprites.svg#icon-circle-plus"></use>
                </svg>
                <?= $translator->translate("Add Dealer") ?>
            </a>
            <?php } ?>
            <div class="filters-block">
                <div class="single-filter">
                    <label for="dateFilterFrom"><?= $translator->translate("Registered") ?></label>
                    <div class="flatpickr input-group" ref="dateFilterFromFlatpickr">
                        <input
                            type="text"
                            class="form-control app-table-filter"
                            readonly
                            placeholder="<?= $translator->translate("From") ?>"
                            id="dateFilterFrom"
                            ref="dateFilterFrom"
                            data-input
                        >
                        <a class="input-button input-group-tex clear-filter-button" title="<?= $translator->translate("Clear") ?>" data-clear>
                            <svg class="icon" width="24" height="25">
                                <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                            </svg>
                        </a>
                    </div>
                    <span>-</span>
                    <div class="flatpickr input-group" ref="dateFilterToFlatpickr">
                        <input
                            type="text"
                            class="form-control app-table-filter"
                            readonly
                            placeholder="<?= $translator->translate("To") ?>"
                            id="dateFilterTo"
                            ref="dateFilterTo"
                            data-input
                        >
                        <a class="input-button input-group-tex clear-filter-button" title="<?= $translator->translate("Clear") ?>" data-clear>
                            <svg class="icon" width="24" height="25">
                                <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="single-filter">
                    <label for="dealerStatusFilter"><?= $translator->translate("Status") ?></label>
                    <select
                        class="form-select default-tom-select app-table-filter"
                        id="dealerStatusFilter"
                        title="<?= $translator->translate("Select status") ?>"
                        ref="statusFilter"
                        placeholder="<?= $translator->translate("Select status") ?>"
                    >
                        <option value=""><?= $translator->translate("Select status") ?></option>
                        <?php foreach ($possibleStatuses as $status) { ?>
                            <option value="<?= $status->value ?>"><?= $status->title($translator) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <?php if ($isSuperAdmin) { ?>
                <div class="single-filter">
                    <label for="dealerAccountManagerFilter"><?= $translator->translate("Account Manager") ?></label>
                    <select
                            class="form-select default-tom-select app-table-filter"
                            id="dealerAccountManagerFilter"
                            title="<?= $translator->translate("Select Manager") ?>"
                            ref="accountManagerFilter"
                            placeholder="<?= $translator->translate("Select Manager") ?>"
                    >
                        <option value=""><?= $translator->translate("Select Manager") ?></option>
                        <?php foreach ($accountManagers as $accountManager) { ?>
                            <option value="<?= $accountManager->id ?>"><?= $accountManager->username ?></option>
                        <?php } ?>
                    </select>
                </div>
                <?php } ?>
                <a href="#" @click.prevent="clearFilters()"><?= $translator->translate("Clear filters") ?></a>
            </div>
        </div>

        <?php if ($isSuperAdmin) { ?>
            <div class="d-flex align-items-center justify-content-start flex-wrap gap-3 mt-3">
                <div class="filters-block ">
                    <div class="single-filter">
                        <label for="accountManagerAssign"><?= $translator->translate("Account Manager") ?></label>
                        <select
                                class="form-select app-assign-account-manager"
                                title="<?= $translator->translate("Select Manager") ?>"
                                ref="accountManagerAssign"
                                placeholder="<?= $translator->translate("Select Manager") ?>"
                        >
                            <option value=""><?= $translator->translate("Assign To") ?></option>
                            <?php foreach ($accountManagers as $accountManager) { ?>
                                <option value="<?= $accountManager->id ?>" disabled><?= $accountManager->username ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div>
                        <button
                            class="btn btn-primary app-assign-account-manager-btn"
                            disabled
                            @click.prevent="assignAccountManager"
                            ref="accountManagerAssignButton"
                        >
                            <?= $translator->translate("Assign") ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php } ?>

    </div>

    <div class="table-wrapper">
        <table
            class="stripe"
            ref="dataTable"
            v-init:assign-account-managers-url-ajax="'<?= $urlGenerator->generate("admin.assignAccountManagersAjax") ?>'"
            v-init:data-table-ajax-url="'<?= $urlGenerator->generate("admin.searchDealersAjax") ?>'"
            v-init:label-confirm-suspend-dealer = "'<?= $translator->translate("You are about to suspend dealer {dealerName}. Please, confirm this action") ?>'"
            v-init:label-confirm-unsuspend-dealer = "'<?= $translator->translate("You are about to unsuspend dealer {dealerName}. Please, confirm this action") ?>'"
            v-init:label-confirm-approve-dealer = "'<?= $translator->translate("You are about to approve the following dealer: {dealerName}. Click Approve to confirm the action") ?>'"
            v-init:label-yes="'<?= $translator->translate("Yes") ?>'"
            v-init:label-no="'<?= $translator->translate("No") ?>'"
            v-init:label-confirm="'<?= $translator->translate("Confirm") ?>'"
            v-init:label-approve="'<?= $translator->translate("Approve") ?>'"
            v-init:label-cancel="'<?= $translator->translate("Cancel") ?>'"
            v-init:message-dealer-approved = "'<?= $translator->translate("Dealer {dealerName} has been approved") ?>'"
            v-init:message-dealer-suspended = "'<?= $translator->translate("Dealer {dealerName} has been suspended") ?>'"
            v-init:message-dealer-unsuspended = "'<?= $translator->translate("Dealer {dealerName} has been unsuspended") ?>'"
            v-init:has-checkbox-column = <?= $isSuperAdmin ? true : false ?>
        >
            <thead>
                <tr>
                    <?php if ($isSuperAdmin) { ?>
                        <th data-name="id"><input type="checkbox" name="selectAll" value="1" id="selectAll" class="app-select-all-checkboxes"></th>
                    <?php } ?>
                    <th data-name="id" class="dt-body-left"><?= $translator->translate("Id") ?></th>
                    <th data-name="name" class="dt-body-left"><?= $translator->translate("Name") ?></th>
                    <th data-name="businessNumber"><?= $translator->translate("Business Number") ?></th>
                    <th data-name="province"><?= $translator->translate("Province") ?></th>
                    <th data-name="address"><?= $translator->translate("Address") ?></th>
                    <th data-name="postalCode"><?= $translator->translate("Postal Code") ?></th>
                    <th data-name="geo"><?= $translator->translate("Geo") ?></th>
                    <th data-name="website"><?= $translator->translate("Website") ?></th>
                    <th data-name="created"><?= $translator->translate("Registered") ?></th>
                    <th data-name="status"><?= $translator->translate("Status") ?></th>
                    <?php if ($isSuperAdmin) { ?>
                        <th data-name="accountManagerName" data-dt-order="disable"><?= $translator->translate("Account Manager") ?></th>
                    <?php } ?>
                    <th data-name="action" data-dt-order="disable"></th>
                </tr>
            </thead>
        </table>
    </div>
</div>
