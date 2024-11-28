<?php

use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;
use App\Frontend\Asset\Admin\EditDealerAsset;
use App\Frontend\Helper\Ancillary;
use App\Backend\Model\Province;

/**
 * @var WebView $this
 * @var TranslatorInterface $translator
 * @var UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var string $csrf
 * @var Yiisoft\User\CurrentUser $currentUser
 * @var Yiisoft\Aliases\Aliases $aliases
 */

$assetManager->register(EditDealerAsset::class);
$this->setTitle($translator->translate('Edit Dealer'));
?>

<div class="page-dealer" id="vue-edit-dealer">
    <div
        class="page-dealer-main-block"
        v-init:search-dealers-url="'<?= $urlGenerator->generate("admin.dealers") ?>'"
        v-init:dealer="<?= Ancillary::forJs($dealer ?? []) ?>"
        v-init:max-upload-file-size="<?= $maxUploadFileSize ?>"
        v-init:upload-logo-ajax-url="'<?= $urlGenerator->generate("admin.uploadDealerLogoAjax") ?>'"
        v-init:delete-logo-ajax-url="'<?= $urlGenerator->generate("admin.deleteDealerLogoAjax", ['id' => $dealer->id]) ?>'"
        v-init:message-max-upload-file-size="'<?= $translator->translate("Size of logo or avatar should be less than {maxUploadFileSize} Mb", ["maxUploadFileSize" => $maxUploadFileSize]) ?>'"
        v-init:allowed-mime-types="<?= Ancillary::forJs($allowedMimeTypes ?? []) ?>"
        v-init:message-allowed-mime-types="'<?= $translator->translate("Images files only allowed for uploading") ?>'"
    >
        <div class="page-dealer-box mb-4">
            <h2 class="mb-3 text-bold"><?= $translator->translate("Edit Dealer") ?></h2>
            <form action="<?= $urlGenerator->generate("admin.doEditDealerAjax") ?>" method="post" ref="updateDealerForm">
                <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                <input type="hidden" name="id" value="<?= $dealer->id ?>">

                <div class="box-wrapper w-75 ms-auto me-auto mb-4">
                    <div class="form-wrapper">

                        <div class="inline-form-group mb-4">
                            <div class="form-group w-100">
                                <label for="name"><?= $translator->translate("Dealership Name") ?></label>
                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="<?= $translator->translate("Dealership Name") ?>"
                                    id="name"
                                    name="name"
                                    pattern="[A-Za-z\s'\-&0-9]{2,64}"
                                    title="<?= $translator->translate("Only Latin symbols, apostrophes, hyphens, spaces, the ampersand, and numbers are allowed. Length: 2-64") ?>"
                                    value="<?= $dealer->name ?? "" ?>"
                                    required
                                >
                            </div>
                            <?php if ($isSuperAdmin) { ?>
                                <div class="form-group w-100">
                                    <label for="accountManagerId"><?= $translator->translate("Account Manager") ?></label>
                                    <select
                                        id="accountManagerId"
                                        name="accountManagerId"
                                        class="form-select default-tom-select"
                                        required
                                    >
                                        <option value=""><?= $translator->translate("Select Manager") ?></option>
                                        <?php foreach ($accountManagers as $accountManager) { ?>
                                            <option value="<?= $accountManager->id ?>" <?= Ancillary::selectedIf($dealer, "accountManagerId", $accountManager->id) ?>>
                                                <?= $accountManager->username ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            <?php } else { ?>
                                <input type="hidden" name="accountManagerId" value="<?= $dealer->accountManagerId ?>">
                            <?php }  ?>

                            <div class="form-group w-100">
                                <label for="businessNumber"><?= $translator->translate("Dealer License") ?></label>
                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="<?= $translator->translate("Dealer License") ?>"
                                    id="businessNumber"
                                    name="businessNumber"
                                    value="<?= $dealer->businessNumber ?? "" ?>"
                                    pattern="[0-9A-Z\s]{5,20}"
                                    title="<?= $translator->translate("The dealer license must contain at least 5 and at most 20 symbols. Only uppercase Latin letters, spaces and digits are allowed") ?>"
                                    required
                                >
                            </div>
                        </div>
                        <div class="inline-form-group mb-4">
                            <div class="form-group">
                                <label for="phone"><?= $translator->translate("Phone Number") ?></label>
                                <input
                                    type="tel"
                                    class="form-control"
                                    placeholder="<?= $translator->translate("Phone Number") ?>"
                                    id="phone"
                                    name="phone"
                                    value="<?= $dealer->phone ?? "" ?>"
                                    pattern="[0-9 +.\(\)\-]{2,20}"
                                    title="<?= $translator->translate("The phone number can contain only numbers, parentheses, dots, spaces, the + and the -. Length: 2-20") ?>"
                                    required
                                >
                            </div>
                            <div class="form-group">
                                <label for="website"><?= $translator->translate("Website (optional)") ?></label>
                                <input
                                    type="url"
                                    class="form-control"
                                    placeholder="http(s)://www.sitename.com"
                                    id="website"
                                    name="website"
                                    value="<?= $dealer->website ?? "" ?>"
                                >
                            </div>
                        </div>
                        <div class="inline-form-group mb-4">
                            <div class="form-group w-100">
                                <label for="address"><?= $translator->translate("Address") ?></label>
                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="<?= $translator->translate("Address") ?>"
                                    id="address"
                                    name="address"
                                    value="<?= $dealer->address ?? "" ?>"
                                    title="<?= $translator->translate("Only Latin symbols, apostrophes, hyphens, spaces, the comma, the ampersand, and numbers are allowed. Length: 2-64") ?>"
                                    pattern="[A-Za-z\s'\-&,0-9]{2,64}"
                                    required
                                >
                            </div>
                            <div class="form-group">
                                <label for="postalCode"><?= $translator->translate("Postal Code") ?></label>
                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="<?= $translator->translate("Postal Code") ?>"
                                    id="postalCode"
                                    name="postalCode"
                                    pattern="(?:[A-Z]\d[A-Z]|\b[A-Z]\d[A-Z][ ]?\d[A-Z]\d\b)"
                                    value="<?= $dealer->postalCode ?? "" ?>"
                                    title="<?= $translator->translate("Please enter a valid postal code of 3 or 6 alphanumeric characters (e.g. A1B or A1B 2C3)") ?>"
                                    required
                                >
                            </div>
                            <div class="form-group">
                                <label for="province"><?= $translator->translate("Province") ?></label>
                                <select
                                    id="province"
                                    name="province"
                                    class="form-select default-tom-select"
                                    required
                                >
                                    <option value=""><?= $translator->translate("Select Province") ?></option>
                                    <?php foreach (Province::cases() as $province) { ?>
                                        <option value="<?= $province->name ?>" <?= Ancillary::selectedIf($dealer, "province", $province->name) ?>>
                                            <?= $province->title($translator) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="googleMapsBusinessUrl"><?= $translator->translate("Google Maps Business Url") ?></label>
                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="<?= $translator->translate("Google Maps Business Url") ?>"
                                    id="googleMapsBusinessUrl"
                                    name="googleMapsBusinessUrl"
                                    pattern="^https?:\/\/(www\.)?google\.com(\.([a-z]+))?\/maps\/place\/.*?\/@[-0-9\.]+,[-0-9\.]+,[-0-9a-z]+\/data=(.*?)$"
                                    value="<?= $dealer->googleMapsBusinessUrl ?? "" ?>"
                                    title="<?= $translator->translate("Please enter a valid google maps business url") ?>"
                                >
                            </div>
                            <div class="form-group">
                                <label for="googleMapsReviewsUrl"><?= $translator->translate("Google Maps Reviews Url") ?></label>
                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="<?= $translator->translate("Google Maps Reviews Url") ?>"
                                    id="googleMapsReviewsUrl"
                                    name="googleMapsReviewsUrl"
                                    pattern="^https?:\/\/(www\.)?google\.com(\.([a-z]+))?\/maps\/place\/.*?\/@[-0-9\.]+,[-0-9\.]+,[-0-9a-z]+\/data=(.*?)$"
                                    value="<?= $dealer->googleMapsReviewsUrl ?? "" ?>"
                                    title="<?= $translator->translate("Please enter a valid google maps reviews url") ?>"
                                >
                            </div>

                            <div class="upload-block">
                                <a href="#" class="upload-link" ref="uploadLink">
                                    <svg class="icon" width="24" height="24">
                                        <use xlink:href="/images/sprites/sprites.svg#icon-image"></use>
                                    </svg>
                                    <?= $translator->translate("Upload logo") ?>
                                </a>
                                <input
                                    type="file"
                                    id="dealerLogo"
                                    name="dealerLogo"
                                    accept="<?= implode(',', $allowedMimeTypes) ?>"
                                    @change="($event) => handleUploadLogo($event)"
                                >
                                <div class="uploaded-photo">
                                    <img :src="dealer.logo" alt="">
                                    <button class="delete-image" v-show="dealer.hasLogo" @click.prevent="deleteDealerLogo()">
                                        <svg class="icon" width="14" height="14">
                                            <use xlink:href="/images/sprites/sprites.svg#icon-cross"></use>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-footer d-flex align-items-center justify-content-center gap-3 flex-wrap">
                    <a href="<?= $urlGenerator->generate('admin.dealers') ?>" class="btn btn-outline btn-big"><?= $translator->translate("Back") ?></a>
                    <input
                        type="button"
                        class="btn btn-primary btn-big"
                        value="<?= $translator->translate("Update") ?>"
                        @click.prevent="updateDealer()"
                    >
                </div>
            </form>
        </div>

        <div class="page-dealer-box">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-4">
                <h2 class="mb-0 text-bold"><?= $translator->translate("Sales Representatives") ?></h2>
                <a class="btn btn-primary" href="#" @click.prevent="openUserModal()">
                    <svg class="icon" width="18" height="18">
                        <use xlink:href="/images/sprites/sprites.svg#icon-circle-plus"></use>
                    </svg>
                    <?= $translator->translate("Add Sales Representative") ?>
                </a>
            </div>
            <table
                ref="dataTable"
                v-init:data-table-ajax-url="'<?= $urlGenerator->generate("admin.searchUsersForDealerAjax", ['id' => $dealer->id]) ?>'"
                v-init:add-user-to-dealer-ajax-url="'<?= $urlGenerator->generate("admin.addUserToDealerAjax") ?>'"
                v-init:update-user-to-dealer-ajax-url="'<?= $urlGenerator->generate("admin.updateUserToDealerAjax") ?>'"
                v-init:are-you-sure-unassign-message="'<?= $translator->translate("Are you sure you want to unassign this user from current dealer?") ?>'"
                class="stripe mt-4"
            >
                <thead>
                <tr>
                    <th data-name="id"><?= $translator->translate("Id") ?></th>
                    <th data-name="username"><?= $translator->translate("Name") ?></th>
                    <th data-name="email"><?= $translator->translate("Email") ?></th>
                    <th data-name="role"><?= $translator->translate("Role") ?></th>
                    <th data-name="licenseNumber" data-dt-order="disable"><?= $translator->translate("License") ?></th>
                    <th data-name="phone" data-dt-order="disable"><?= $translator->translate("Phone") ?></th>
                    <th data-name="fullAddress" data-dt-order="disable"><?= $translator->translate("Address") ?></th>
                    <th data-name="created"><?= $translator->translate("Registered") ?></th>
                    <th data-name="status"><?= $translator->translate("Status") ?></th>
                    <th data-name="action" data-dt-order="disable"></th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    <?= $this->render('_add-sales-representative-modal', ['dealer'  => $dealer]) ?>
</div>

