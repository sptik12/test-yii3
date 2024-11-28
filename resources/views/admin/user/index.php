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
$this->setTitle($translator->translate('Users'));
?>

<div id="vue-users">
    <h1 class="mb-4"><?= $translator->translate("Users") ?></h1>
    <div class="page-top-part">
        <div class="d-flex align-items-center justify-content-start flex-wrap gap-3">
            <a class="btn btn-primary" href="<?= $urlGenerator->generate("admin.addUser") ?>">
                <svg class="icon" width="18" height="18">
                    <use xlink:href="/images/sprites/sprites.svg#icon-circle-plus"></use>
                </svg>
                <?= $translator->translate("Add User") ?>
            </a>

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
                <div class="single-filter">
                    <label for="usersRoles"><?= $translator->translate("Roles") ?></label>
                    <select class="form-select default-tom-select app-table-filter app-roles-filter" id="usersRoles" title="<?= $translator->translate("Select role") ?>" ref="roleFilter">
                        <option value=""><?= $translator->translate("Select role") ?></option>
                        <?php foreach ($possibleRoles as $role) { ?>
                            <option value="<?= $role->value ?>"><?= $role->title($translator) ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="single-filter">
                    <label for="dealerStatus"><?= $translator->translate("Status") ?></label>
                    <select class="form-select default-tom-select app-table-filter app-status-filter" id="dealerStatus" title="<?= $translator->translate("Select status") ?>" ref="statusFilter">
                        <option value=""><?= $translator->translate("Select status") ?></option>
                        <?php foreach ($possibleStatuses as $status) { ?>
                            <option value="<?= $status->value ?>"><?= $status->title($translator) ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="single-filter">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="deletionDate"
                            name="deletionDate"
                            ref="deletionDate"
                            value="1"
                        >
                        <label class="form-check-label" for="deletionDate">
                            <?= $translator->translate("To be deleted only") ?>
                        </label>
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
            v-init:data-table-ajax-url="'<?= $urlGenerator->generate("admin.searchUsersAjax") ?>'"
            v-init:label-are-you-sure-delete ="'<?= $translator->translate("Are you sure you want to delete this user?") ?>'"
            v-init:label-are-you-sure-refuse-deletion="'<?= $translator->translate("Are you sure you want refuse deletion of this user?") ?>'"
            v-init:label-are-you-sure-delete ="'<?= $translator->translate("Are you sure you want to delete this user?") ?>'"
            v-init:label-confirm-suspend-user = "'<?= $translator->translate("You are about to suspend user {userName}. Please, confirm this action") ?>'"
            v-init:label-notify-user="'<?= $translator->translate("Notify user about suspension") ?>'"
            v-init:label-yes="'<?= $translator->translate("Yes") ?>'"
            v-init:label-no="'<?= $translator->translate("No") ?>'"
            v-init:label-confirm="'<?= $translator->translate("Confirm") ?>'"
            v-init:label-cancel="'<?= $translator->translate("Cancel") ?>'"
            v-init:message-user-suspended = "'<?= $translator->translate("User {userName} has been suspended") ?>'"
            v-init:message-user-unsuspended = "'<?= $translator->translate("User {userName} has been unsuspended") ?>'"
            v-init:message-success-send-code="'<?= $translator->translate("One time login code was successfully sent") ?>'"
        >
            <thead>
                <tr>
                    <th data-name="id"><?= $translator->translate("Id") ?></th>
                    <th data-name="username"><?= $translator->translate("Name") ?></th>
                    <th data-name="email"><?= $translator->translate("Email") ?></th>
                    <th data-name="rolesList" data-dt-order="disable"><?= $translator->translate("Roles") ?></th>
                    <th data-name="dealers" data-dt-order="disable"><?= $translator->translate("Dealers") ?></th>
                    <th data-name="created"><?= $translator->translate("Registered") ?></th>
                    <th data-name="status"><?= $translator->translate("Status") ?></th>
                    <th data-name="comments" data-dt-order="disable"><?= $translator->translate("Comments") ?></th>
                    <th data-name="action" data-dt-order="disable"></th>
                </tr>
            </thead>
        </table>
    </div>
</div>
