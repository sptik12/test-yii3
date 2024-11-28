<?php
use App\Frontend\Helper\Ancillary;
use App\Backend\Model\Province;
use App\Backend\Model\User\Role;

?>

<div class="modal fade app-add-user-role-modal" id="add-role" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="mb-0 text-bold"><?= $translator->translate("Add Role") ?></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= $translator->translate("Close") ?>"></button>
            </div>
            <div class="modal-body">
                <form action="<?= $urlGenerator->generate('admin.addRoleToUserAjax') ?>" method="post" ref="roleForm">
                    <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                    <input type="hidden" name="userId" value="<?= $user->id ?>">
                    <div class="box-wrapper w-75 ms-auto me-auto mb-4">
                        <div class="form-wrapper">
                            <div class="inline-form-group">
                                <div class="form-group w-100">
                                    <label for="role"><?= $translator->translate("Role") ?></label>
                                    <select
                                        class="form-select default-tom-select app-user-role"
                                        name="role"
                                        id="role"
                                        ref="role"
                                        title="<?= $translator->translate("Select role") ?>"
                                        required
                                        @change.prevent="checkRole()"
                                    >
                                        <option value=""><?= $translator->translate("Select role") ?></option>
                                        <?php foreach ($possibleRoles as $role) { ?>
                                            <option value="<?= $role->value ?>"><?= $role->title($translator) ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="inline-form-group mt-4" v-show="isDealerRole" style="display:none">
                                <div class="form-group w-100">
                                    <label for="dealer"><?= $translator->translate("Dealer") ?></label>
                                    <select
                                        class="form-select default-tom-select-search app-user-dealer"
                                        id="dealer"
                                        name="dealerId"
                                        ref="dealer"
                                        placeholder="<?= $translator->translate("Search dealer") ?>"
                                        :required="isDealerRole"
                                    >
                                        <option value=""><?= $translator->translate("Select dealer") ?></option>
                                        <?php foreach ($dealers as $dealer) { ?>
                                            <option value="<?= $dealer->id ?>"><?= $dealer->name ?></option>
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
                                    :required="isDealerRole"
                                    v-model="user.licenseNumber"
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
                                    :required="isDealerRole"
                                    v-model="user.phone"
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
                                    :required="isDealerRole"
                                    v-model="user.address"
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
                                    :required="isDealerRole"
                                    v-model="user.postalCode"
                                >
                            </div>

                            <div class="inline-form-group mt-4" v-show="isDealerRole" style="display:none">
                                <label for="province"><?= $translator->translate("Province") ?></label>
                                <select
                                    id="province"
                                    name="province"
                                    class="form-select default-tom-select app-user-province"
                                    :required="isDealerRole"
                                    v-model="user.province"
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
                                    v-model="user.receiveEmails"
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
                                        :required="isAccountManagerRole"
                                        max="99"
                                        min="0"
                                        step="0.05"
                                        onkeydown="return event.keyCode !== 69"
                                        v-model="user.customComission"
                                    >
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="form-footer d-flex align-items-center justify-content-center gap-3 flex-wrap">
                        <input
                            type="button"
                            class="btn btn-outline"
                            value="<?= $translator->translate("Cancel") ?>"
                            @click.prevent="closeUserRoleModal()"
                        >
                        <input
                            type="button"
                            class="btn btn-primary"
                            value="<?= $translator->translate("Add Role") ?>"
                            @click.prevent="addRole()"
                        >
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
