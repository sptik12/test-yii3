<?php

use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;
use App\Frontend\Asset\Admin\AddUserAsset;
use App\Frontend\Helper\Ancillary;
use App\Backend\Model\Province;
use App\Backend\Model\User\Role;

/**
 * @var WebView $this
 * @var TranslatorInterface $translator
 * @var UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var string $csrf
 * @var Yiisoft\User\CurrentUser $currentUser
 * @var Yiisoft\Aliases\Aliases $aliases
 */

$assetManager->register(AddUserAsset::class);
$this->setTitle($translator->translate('Add User'));
?>

<div class="page-user" id="vue-add-user">
    <div
        class="page-user-main-block"
    >
        <h2 class="mb-3 text-bold"><?= $translator->translate("Add User") ?></h2>
        <div class="page-dealer-box">
            <form action="<?= $urlGenerator->generate('admin.doAddUser') ?>" method="post">
                <input type="hidden" name="_csrf" value="<?= $csrf ?>">

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
                                    value="<?= $filled->username ?? "" ?>"
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
                                    value="<?= $filled->email ?? "" ?>"
                                    required
                                >
                            </div>
                        </div>

                        <div class="inline-form-group">
                            <div class="form-group w-100">
                                <label for="role"><?= $translator->translate("Role") ?></label>
                                <select
                                    class="form-select default-tom-select"
                                    name="role"
                                    id="role"
                                    ref="role"
                                    title="<?= $translator->translate("Select role") ?>"
                                    value="<?= $filled->role ?? "" ?>"
                                    required
                                    @change.prevent="checkRole()"
                                >
                                    <option value=""><?= $translator->translate("Select role") ?></option>
                                    <?php foreach (Role::cases() as $role) { ?>
                                        <option value="<?= $role->value ?>" <?= Ancillary::selectedIf($filled, "role", $role->value) ?>><?= $role->title($translator) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="inline-form-group mt-4" v-show="isDealerRole" style="display:none">
                            <div class="form-group w-100">
                                <label for="dealer"><?= $translator->translate("Dealer") ?></label>
                                <select
                                    class="form-select default-tom-select-search"
                                    id="dealer"
                                    name="dealerId"
                                    ref="dealer"
                                    value="<?= $filled->dealerId ?? "" ?>"
                                    placeholder="<?= $translator->translate("Search dealer") ?>"
                                    :required="isDealerRole"
                                >
                                    <option value=""><?= $translator->translate("Select dealer") ?></option>
                                    <?php foreach ($dealers as $dealer) { ?>
                                        <option value="<?= $dealer->id ?>" <?= Ancillary::selectedIf($filled, "dealerId", $dealer->id) ?>><?= $dealer->name ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="inline-form-group mt-4" v-show="isDealerRole" style="display:none">
                            <label for="licenseNumber"><?= $translator->translate("License Number") ?></label>
                            <input
                                type="text"
                                class="form-control"
                                placeholder="<?= $translator->translate("License Number") ?>"
                                id="licenseNumber"
                                name="licenseNumber"
                                pattern="[0-9A-Z\s]{5,20}"
                                title="<?= $translator->translate("The license number must contain at least 5 and at most 20 symbols. Only uppercase Latin letters, spaces, and digits are allowed") ?>"
                                value="<?= $filled->licenseNumber ?? "" ?>"
                                :required="isDealerRole"
                            >
                        </div>

                        <div class="inline-form-group mt-4" v-show="isDealerRole" style="display:none">
                            <label for="phone"><?= $translator->translate("Phone Number") ?></label>
                            <input
                                type="tel"
                                class="form-control"
                                placeholder="<?= $translator->translate("Phone Number") ?>"
                                id="phone"
                                name="phone"
                                pattern="[0-9 +.\(\)\-]{2,20}"
                                title="<?= $translator->translate("The phone number can contain only numbers, parentheses, dots, spaces, the + and the -. Length: 2-20") ?>"
                                value="<?= $filled->phone ?? "" ?>"
                                :required="isDealerRole"
                            >
                        </div>

                        <div class="inline-form-group mt-4" v-show="isDealerRole" style="display:none">
                            <label for="address"><?= $translator->translate("Address") ?></label>
                            <input
                                type="text"
                                class="form-control"
                                placeholder="<?= $translator->translate("Address") ?>"
                                id="address"
                                name="address"
                                title="<?= $translator->translate("Only Latin symbols, apostrophes, hyphens, spaces, the comma, the ampersand, and numbers are allowed. Length: 2-64") ?>"
                                pattern="[A-Za-z\s'\-&,0-9]{2,64}"
                                value="<?= $filled->address ?? "" ?>"
                                :required="isDealerRole"
                            >
                        </div>

                        <div class="inline-form-group mt-4" v-show="isDealerRole" style="display:none">
                            <label for="postalCode"><?= $translator->translate("Postal Code") ?></label>
                            <input
                                type="text"
                                class="form-control"
                                placeholder="<?= $translator->translate("Postal Code") ?>"
                                id="postalCode"
                                name="postalCode"
                                pattern="(?:[A-Z]\d[A-Z]|\b[A-Z]\d[A-Z][ ]?\d[A-Z]\d\b)"
                                title = "<?= $translator->translate("Please enter a valid postal code of 3 or 6 alphanumeric characters (e.g. A1B or A1B 2C3)") ?>"
                                value="<?= $filled->postalCode ?? "" ?>"
                                :required="isDealerRole"
                            >
                        </div>

                        <div class="inline-form-group mt-4" v-show="isDealerRole" style="display:none">
                            <label for="province"><?= $translator->translate("Province") ?></label>
                            <select
                                id="province"
                                name="province"
                                class="form-select default-tom-select app-user-province"
                                :required="isDealerRole"
                            >
                                <option value=""><?= $translator->translate("Select Province") ?></option>
                                <?php foreach (Province::cases() as $province) { ?>
                                    <option value="<?= $province->name ?>" <?= Ancillary::selectedIf($filled, "province", $province->name) ?>>
                                        <?= $province->title($translator) ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="inline-form-group mt-4" v-show="isDealerRole" style="display:none">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="receiveEmails"
                                name="receiveEmails"
                                value="1"
                            >
                            <label class="form-check-label" for="receiveEmails">
                                <?= $translator->translate("Receive emails from {appName}", ['appName' => $applicationParameters->getName()]) ?>
                            </label>
                        </div>

                        <div class="inline-form-group mt-4" v-show="isAccountManagerRole" style="display:none">
                            <div class="form-group w-100">
                                <label for="customComission"><?= $translator->translate("Comission") ?>(%)</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    id="customComission"
                                    name="customComission"
                                    value="<?= $filled->customComission ?? "" ?>"
                                    :required="isAccountManagerRole"
                                    max="99"
                                    min="0"
                                    step="0.05"
                                    onkeydown="return event.keyCode !== 69"
                                >
                            </div>
                        </div>

                    </div>
                </div>

                <div class="form-footer d-flex align-items-center justify-content-center gap-3 flex-wrap">
                    <a href="<?= $urlGenerator->generate('admin.users') ?>" class="btn btn-outline btn-big"><?= $translator->translate("Back") ?></a>
                    <button type="submit" class="btn btn-primary btn-big"><?= $translator->translate("Add") ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
