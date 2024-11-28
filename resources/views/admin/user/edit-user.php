<?php

use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;
use App\Frontend\Asset\Admin\EditUserAsset;
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

$assetManager->register(EditUserAsset::class);
$this->setTitle($translator->translate('Edit User'));
?>

<div class="page-user" id="vue-edit-user">
    <div
        class="page-user-main-block"
        v-init:user="<?= Ancillary::forJs($user ?? []) ?>"
        v-init:search-users-url="'<?= $lastSearchUserUrl ?>'"
        v-init:delete-user-ajax-url="'<?= $urlGenerator->generate("admin.deleteUserAjax") ?>'"
        v-init:validate-delete-user-ajax-url="'<?= $urlGenerator->generate("admin.validateDeleteUserAjax") ?>'"
        v-init:set-user-deletion-date-ajax-url="'<?= $urlGenerator->generate("admin.setUserDeletionDateAjax") ?>'"
        v-init:are-you-sure-delete-message="'<?= $translator->translate("Are you sure you want to delete this user?") ?>'"
    >
        <div class="page-user-box mb-4">
            <h2 class="mb-3 text-bold"><?= $translator->translate("Edit User") ?></h2>
            <form action="<?= $urlGenerator->generate("admin.doEditUserAjax") ?>" method="post" ref="updateUserForm">
                <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                <input type="hidden" name="id" value="<?= $user->id ?>">
                <div class="box-wrapper w-75 ms-auto me-auto mb-4">
                    <div class="form-wrapper">
                        <div class="inline-form-group mb-4">
                            <div class="form-group">
                                <label for="username"><?= $translator->translate("Full Name") ?></label>
                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="<?= $translator->translate("Full Name") ?>"
                                    id="username"
                                    name="username"
                                    pattern="[A-Za-z\s']{4,64}"
                                    title="<?= $translator->translate("Only Latin symbols, apostrophes, and spaces are allowed. Length: 4-64") ?>"
                                    v-model="user.username"
                                    required
                                >
                            </div>
                            <div class="form-group">
                                <label for="email"><?= $translator->translate("Email") ?></label>
                                <input
                                    type="email"
                                    class="form-control"
                                    placeholder="mail@example.com"
                                    id="email"
                                    name="email"
                                    v-model="user.email"
                                    required
                                >
                            </div>
                        </div>

                        <div class="inline-form-group mb-4" v-show="user.isDealer" style="display:none">
                            <label for="licenseNumber"><?= $translator->translate("License Number") ?></label>
                            <input
                                    type="text"
                                    class="form-control"
                                    placeholder="<?= $translator->translate("License Number") ?>"
                                    id="licenseNumber"
                                    name="licenseNumber"
                                    pattern="[0-9A-Z\s]{5,20}"
                                    title="<?= $translator->translate("The license number must contain at least 5 and at most 20 symbols. Only uppercase Latin letters, spaces, and digits are allowed") ?>"
                                    v-model="user.licenseNumber"
                                    :required="user.isDealer"
                            >
                        </div>

                        <div class="inline-form-group mb-4" v-show="user.isDealer" style="display:none">
                            <div class="form-group">
                                <label for="phone"><?= $translator->translate("Phone Number") ?></label>
                                <input
                                    type="tel"
                                    class="form-control"
                                    placeholder="<?= $translator->translate("Phone Number") ?>"
                                    id="phone"
                                    name="phone"
                                    pattern="[0-9 +.\(\)\-]{2,20}"
                                    title="<?= $translator->translate("The phone number can contain only numbers, parentheses, dots, spaces, the + and the -. Length: 2-20") ?>"
                                    v-model="user.phone"
                                    :required="user.isDealer"
                                >
                            </div>
                        </div>

                        <div class="inline-form-group mb-4" v-show="user.isDealer" style="display:none">
                            <div class="form-group w-100">
                                <label for="address"><?= $translator->translate("Address") ?></label>
                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="<?= $translator->translate("Address") ?>"
                                    id="address"
                                    name="address"
                                    title="<?= $translator->translate("Only Latin symbols, apostrophes, hyphens, spaces, the comma, the ampersand, and numbers are allowed. Length: 2-64") ?>"
                                    pattern="[A-Za-z\s'\-&,0-9]{2,64}"
                                    v-model="user.address"
                                    :required="user.isDealer"
                                >
                            </div>
                        </div>

                        <div class="inline-form-group" v-show="user.isDealer" style="display:none">
                            <div class="form-group">
                                <label for="postalCode"><?= $translator->translate("Postal Code") ?></label>
                                <input
                                    type="text"
                                    class="form-control"
                                    placeholder="<?= $translator->translate("Postal Code") ?>"
                                    id="postalCode"
                                    name="postalCode"
                                    pattern="(?:[A-Z]\d[A-Z]|\b[A-Z]\d[A-Z][ ]?\d[A-Z]\d\b)"
                                    title = "<?= $translator->translate("Please enter a valid postal code of 3 or 6 alphanumeric characters (e.g. A1B or A1B 2C3)") ?>"
                                    v-model="user.postalCode"
                                    :required="user.isDealer"
                                >
                            </div>

                            <div class="form-group" v-show="user.isDealer" style="display:none">
                                <label for="province"><?= $translator->translate("Province") ?></label>
                                <select
                                    id="province"
                                    name="province"
                                    class="form-select default-tom-select app-user-province"
                                    v-model="user.province"
                                    :required="user.isDealer"
                                >
                                    <option value=""><?= $translator->translate("Select Province") ?></option>
                                    <?php foreach (Province::cases() as $province) { ?>
                                        <option value="<?= $province->name ?>">
                                            <?= $province->title($translator) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="inline-form-group" v-show="user.isDealer" style="display:none">
                            <div class="form-group w-100">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="receiveEmails"
                                    name="receiveEmails"
                                    value="1"
                                    v-model="user.receiveEmails"
                                >
                                <label class="form-check-label" for="receiveEmails">
                                    <?= $translator->translate("Receive emails from {appName}", ['appName' => $applicationParameters->getName()]) ?>
                                </label>
                            </div>
                        </div>

                        <div class="inline-form-group mt-4" v-show="user.isAccountManager" style="display:none">
                            <div class="form-group w-100">
                                <label for="customComission"><?= $translator->translate("Comission") ?>(%)</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    id="customComission"
                                    name="customComission"
                                    :required="user.isAccountManager"
                                    max="99"
                                    min="0"
                                    step="0.05"
                                    v-model="user.customComission"
                                    onkeydown="return event.keyCode !== 69"
                                >
                            </div>
                        </div>

                    </div>
                </div>

                <div class="w-75 ms-auto me-auto mb-4">
                    <div class="form-footer d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <a href="<?= $lastSearchUserUrl ?>" class="btn btn-outline btn-big"><?= $translator->translate("Back") ?></a>
                            <input
                                type="button"
                                class="btn btn-primary btn-big"
                                value="<?= $translator->translate("Update") ?>"
                                @click.prevent="updateUser()"
                            >
                        </div>
                        <?php if ($currentUser->getId() != $user->id && $canDelete) { ?>
                            <a
                                class="app-delete-user-button btn btn-danger btn-big "
                                @click.prevent="deleteUser(<?= $user->id ?>)"
                                ref="deleteUserButton"
                            >
                                <?= $translator->translate("Delete") ?>
                            </a>
                        <?php } ?>
                    </div>
                </div>
            </form>
        </div>

        <div class="page-dealer-box">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-4">
                <h2 class="mb-0 text-bold"><?= $translator->translate("User Roles") ?></h2>
                <a class="btn btn-primary" href="#" @click.prevent="openUserRoleModal()">
                    <svg class="icon" width="18" height="18">
                        <use xlink:href="/images/sprites/sprites.svg#icon-circle-plus"></use>
                    </svg>
                    <?= $translator->translate("Add Role") ?>
                </a>
            </div>

            <table
                ref="dataTable"
                v-init:data-table-ajax-url="'<?= $urlGenerator->generate("admin.searchUserRolesAjax", ['id' => $user->id]) ?>'"
                v-init:are-you-sure-unassign-message="'<?= $translator->translate("Are you sure you want to unassign user from this role?") ?>'"
                class="stripe"
            >
                <thead>
                <tr>
                    <th data-name="roleName"><?= $translator->translate("Role") ?></th>
                    <th data-name="dealer"><?= $translator->translate("Dealer") ?></th>
                    <th class="text-end" data-name="action"></th>
                </tr>
                </thead>
            </table>
        </div>
    </div>

    <?= $this->render('_add-role-modal', compact("user", "possibleRoles", "dealers")) ?>
</div>
