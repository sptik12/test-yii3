<?php
use App\Backend\Model\Province;
use App\Frontend\Helper\Ancillary;

?>

<div class="box-wrapper app-step" v-show="isStepDisplayed('location')" data-step="location">
    <div class="form-wrapper">
        <div class="inline-form-group">
            <h3 class="label-with-border w-100 mt-0"><?= $translator->translate("Location") ?></h3>
            <div class="w-100">
                <p>{{ car.displayedAddress ? car.displayedAddress : "<?= $translator->translate("Car location is not set") ?>" }}</p>
                <a href="#" @click.prevent="isLocationFormDisplayed = true" v-show="isLocationFormDisplayed === false"><?= $translator->translate("Change") ?></a>
            </div>
            <div v-show="isLocationFormDisplayed == true" class="w-100">
                <div class="inline-form-group w-100">
                    <div class="form-group w-100">
                        <label for="address">
                            <?= $translator->translate("Address") ?>
                        </label>
                        <input
                            type="text"
                            id="address"
                            name="address"
                            class="form-control"
                            placeholder="<?= $translator->translate("Address") ?>"
                            v-model="car.address"
                            title="<?= $translator->translate("Only Latin symbols, apostrophes, hyphens, spaces, the comma, the ampersand, and numbers are allowed. Length: 2-64") ?>"
                            pattern="[A-Za-z\s'\-&,0-9]{2,64}"
                            @change.prevent="updatePreviewSession($event, 'address')"
                            @focus = "setCurrentMobileStepOnFocus($event)"
                        >
                    </div>
                    <div class="form-group w-100">
                        <label for="postalCode">
                            <?= $translator->translate("Postal Code") ?>
                        </label>
                        <input
                            type="text"
                            id="postalCode"
                            name="postalCode"
                            class="form-control"
                            placeholder="<?= $translator->translate("Postal Code") ?>"
                            pattern="(?:[A-Z]\d[A-Z]|\b[A-Z]\d[A-Z][ ]?\d[A-Z]\d\b)"
                            v-model="car.postalCode"
                            title="<?= $translator->translate("Please enter a valid postal code of 3 or 6 alphanumeric characters (e.g. A1B or A1B 2C3)") ?>"
                            @change.prevent="updatePreviewSession($event, 'postalCode')"
                            @focus = "setCurrentMobileStepOnFocus($event)"
                        >
                    </div>
                    <div class="form-group w-100">
                        <label for="province">
                            <?= $translator->translate("Province") ?>
                        </label>
                        <select
                            id="province"
                            name="province"
                            class="form-select default-tom-select"
                            v-model="car.province"
                            @change.prevent="updatePreviewSession($event, 'province')"
                            @focus = "setCurrentMobileStepOnFocus($event)"
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
            </div>
            <div class="w-100">
                <a href="#" @click.prevent="isLocationFormDisplayed = false" v-show="isLocationFormDisplayed === true"><?= $translator->translate("Hide") ?></a>
            </div>
            <div class="form-check w-100" >
                <input
                    class="form-check-input"
                    type="checkbox"
                    id="keepLocationPrivate"
                    name="keepLocationPrivate"
                    value="1"
                    v-model="car.keepLocationPrivate"
                    :checked="car.keepLocationPrivate"
                    @change.prevent="updatePreviewSession($event, 'keepLocationPrivate')"
                    @focus = "setCurrentMobileStepOnFocus($event)"
                >
                <label class="form-check-label" for="keepLocationPrivate">
                    <?= $translator->translate("Keep location private") ?>
                   <div class="text-tiny w-100"><?= $translator->translate("Province and Postal Code only will be shown on your listing") ?></div>
                </label>
            </div>
        </div>
    </div>
</div>
