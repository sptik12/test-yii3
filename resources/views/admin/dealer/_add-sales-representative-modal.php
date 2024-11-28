<?php
use App\Frontend\Helper\Ancillary;
use App\Backend\Model\Province;
use App\Backend\Model\User\Role;

?>

<div class="modal fade app-add-user-modal" id="add-sales-representative" ref="userModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="mb-0 text-bold" v-show="mode=='new'"><?= $translator->translate("Add Sales Representative") ?></h3>
                <h3 class="mb-0 text-bold" v-show="mode=='edit'"><?= $translator->translate("Edit Sales Representative") ?></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="<?= $urlGenerator->generate("admin.addUserToDealerAjax") ?>" method="post" ref="userForm">
                    <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                    <input type="hidden" name="dealerId" value="<?= $dealer->id ?>">
                    <input type="hidden" name="id" v-model="user.id">
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
                                        required
                                    >
                                </div>
                                <div class="form-group w-100">
                                    <label for="role"><?= $translator->translate("Role") ?></label>
                                    <select
                                        id="role"
                                        name="role"
                                        class="form-select default-tom-select app-user-role"
                                        v-model="user.role"
                                        required
                                    >
                                        <option value=""><?= $translator->translate("Select Role") ?></option>
                                        <?php foreach (Role::cases() as $role) { ?>
                                            <?php if ($role->isDealerRole()) { ?>
                                                <option value="<?= $role->value ?>">
                                                    <?= $role->title($translator) ?>
                                                </option>
                                            <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
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
                                        title = "<?= $translator->translate("Please enter a valid postal code of 3 or 6 alphanumeric characters (e.g. A1B or A1B 2C3)") ?>"
                                        v-model="user.postalCode"
                                        required
                                    >
                                </div>
                                <div class="form-group">
                                    <label for="province"><?= $translator->translate("Province") ?></label>
                                    <select
                                        id="province"
                                        name="province"
                                        class="form-select default-tom-select app-user-province"
                                        v-model="user.province"
                                        required
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
                            <div class="form-check w-100">
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
                    </div>
                    <div class="form-footer d-flex align-items-center justify-content-center gap-3 flex-wrap">
                        <input
                            type="button"
                            class="btn btn-outline"
                            value="<?= $translator->translate("Cancel") ?>"
                            @click.prevent="closeUserModal()"
                        >
                        <input
                            type="button"
                            class="btn btn-primary"
                            value="<?= $translator->translate("Add User") ?>"
                            @click.prevent="addUser()"
                            v-show="mode=='new'"
                        >
                        <input
                            type="button"
                            class="btn btn-primary"
                            value="<?= $translator->translate("Save") ?>"
                            @click.prevent="updateUser()"
                            v-show="mode=='edit'"
                        >
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
