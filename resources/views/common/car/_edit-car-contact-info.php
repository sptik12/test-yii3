<div class="box-wrapper app-step" v-show="isStepDisplayed('contact-info')" data-step="contact-info">
    <div class="form-wrapper">
        <div class="inline-form-group">
            <h3 class="label-with-border w-100 mt-0"><?= $translator->translate("Contact Information") ?></h3>
            <div class="form-group w-100">
                <label for="contactName" class=""><?= $translator->translate("Contact Name") ?></label>
                <input
                    type="text"
                    id="contactName"
                    name="contactName"
                    class="form-control"
                    ref="contactName"
                    v-model="car.contactName"
                    @change.prevent="updatePreviewSession($event, 'contactName')"
                    required
                    pattern="[A-Za-z\s']{4,64}"
                    title="<?= $translator->translate("Only Latin symbols, apostrophes, and spaces are allowed. Length: 4-64") ?>"
                    @focus = "setCurrentMobileStepOnFocus($event)"
                >
            </div>
            <div class="form-group w-100">
                <label for="phone">
                    <?= $translator->translate("Phone number (optional)") ?>
                    <div class="text-tiny w-100">(<?= $translator->translate("Your phone number will shown on your Ad") ?>)</div>
                </label>
                <input
                    type="text"
                    id="phone"
                    name="phone"
                    class="form-control"
                    ref="phone"
                    v-model="car.phone"
                    @change.prevent="updatePreviewSession($event, 'phone')"
                    pattern="[0-9 +.\(\)\-]{2,20}"
                    title="<?= $translator->translate("The phone number can contain only numbers, parentheses, dots, spaces, the + and the -. Length: 2-20") ?>"
                    @focus = "setCurrentMobileStepOnFocus($event)"
                >
            </div>
            <div class="form-group w-100">
                <label for="email">
                    <?= $translator->translate("Email") ?>
                    <div class="text-tiny w-100">(<?= $translator->translate("Your email address will not be shared with others") ?>)</div>
                </label>
                <input
                    type="text"
                    readonly
                    id="email"
                    name="email"
                    class="form-control"
                    ref="email"
                    v-model="car.email"
                >
            </div>
        </div>
    </div>
</div>
