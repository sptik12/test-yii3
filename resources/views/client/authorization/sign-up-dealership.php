<?php

declare(strict_types=1);

use App\Frontend\Helper\Ancillary;
use App\Backend\Model\Province;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

/**
 * @var WebView               $this
 * @var TranslatorInterface   $translator
 * @var UrlGeneratorInterface $urlGenerator
 * @var string                $csrf
 * @var string $filled
 */

$this->setTitle($translator->translate('Dealer Registration'));

?>

<script>
    document.documentElement.style.scrollBehavior = 'auto'
</script>
<div class="page-authorization page-dealer-authorization">
    <div class="authorization-main-block">
        <h1 class="mb-4"><?= $translator->translate("Want to see how {appName} can help you?", ['appName' => $applicationParameters->getName()]) ?></h1>
        <p class="text-small"><?= $translator->translate("Call {phone} or use this form and we'll be in touch soon.", ['phone' => '']) ?></p>
        <div class="authorization-box">
            <form action="<?= $urlGenerator->generate('client.doSignUpDealership') ?>" method="post">
                <input type="hidden" name="_csrf" value="<?= $csrf ?>">

                <!-- Dealer section -->
                <div class="form-title">
                    <h3 class="mb-0"><?= $translator->translate("Dealership") ?></h3>
                </div>
                <div class="form-group">
                    <label for="dealershipName"><?= $translator->translate("Dealership Name") ?></label>
                    <input
                        type="text"
                        class="form-control"
                        placeholder="<?= $translator->translate("Dealership Name") ?>"
                        id="dealershipName"
                        name="dealershipName"
                        pattern="[A-Za-z\s'\-&0-9]{2,64}"
                        title="<?= $translator->translate("Only Latin symbols, apostrophes, hyphens, spaces, the ampersand, and numbers are allowed. Length: 2-64") ?>"
                        value="<?= $filled->dealershipName ?? "" ?>"
                        required
                    >
                </div>
                <div class="inline-form-group">
                    <div class="form-group w-100">
                        <label for="businessNumber"><?= $translator->translate("Dealer License") ?></label>
                        <input
                            type="text"
                            class="form-control"
                            placeholder="<?= $translator->translate("Dealer License") ?>"
                            id="businessNumber"
                            name="businessNumber"
                            value="<?= $filled->businessNumber ?? "" ?>"
                            pattern="[0-9A-Z\s]{5,20}"
                            title="<?= $translator->translate("The dealer license must contain at least 5 and at most 20 symbols. Only uppercase Latin letters, spaces and digits are allowed") ?>"
                            required
                        >
                    </div>
                </div>
                <div class="inline-form-group">
                    <div class="form-group">
                        <label for="dealershipPhone"><?= $translator->translate("Phone Number") ?></label>
                        <input
                            type="tel"
                            class="form-control"
                            placeholder="<?= $translator->translate("Phone Number") ?>"
                            id="dealershipPhone"
                            name="dealershipPhone"
                            value="<?= $filled->dealershipPhone ?? "" ?>"
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
                            value="<?= $filled->website ?? "" ?>"
                        >
                    </div>
                </div>
                <div class="form-group">
                    <label for="dealershipAddress"><?= $translator->translate("Address") ?></label>
                    <input
                        type="text"
                        class="form-control"
                        placeholder="<?= $translator->translate("Address") ?>"
                        id="dealershipAddress"
                        name="dealershipAddress"
                        value="<?= $filled->dealershipAddress ?? "" ?>"
                        title="<?= $translator->translate("Only Latin symbols, apostrophes, hyphens, spaces, the comma, the ampersand, and numbers are allowed. Length: 2-64") ?>"
                        pattern="[A-Za-z\s'\-&,0-9]{2,64}"
                        required
                    >
                </div>
                <div class="inline-form-group">
                    <div class="form-group">
                        <label for="dealershipPostalCode"><?= $translator->translate("Postal Code") ?></label>
                        <input
                            type="text"
                            class="form-control"
                            placeholder="<?= $translator->translate("Postal Code") ?>"
                            id="dealershipPostalCode"
                            name="dealershipPostalCode"
                            pattern="(?:[A-Z]\d[A-Z]|\b[A-Z]\d[A-Z][ ]?\d[A-Z]\d\b)"
                            value="<?= $filled->dealershipPostalCode ?? "" ?>"
                            title = "<?= $translator->translate("Please enter a valid postal code of 3 or 6 alphanumeric characters (e.g. A1B or A1B 2C3)") ?>"
                            required
                        >
                    </div>
                    <div class="form-group">
                        <label for="dealershipProvince"><?= $translator->translate("Province") ?></label>
                        <select
                            id="dealershipProvince"
                            name="dealershipProvince"
                            class="form-select default-tom-select"
                            required
                        >
                            <option value=""><?= $translator->translate("Select Province") ?></option>
                            <?php foreach (Province::cases() as $province) { ?>
                                <option value="<?= $province->name ?>" <?= Ancillary::selectedIf($filled, "dealershipProvince", $province->name) ?>>
                                    <?= $province->title($translator) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <!-- User section -->
                <div class="form-title">
                    <h3 class="mb-0"><?= $translator->translate("Sales Representative") ?></h3>
                </div>
                <div class="inline-form-group">
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
                        <label for="licenseNumber"><?= $translator->translate("License Number") ?></label>
                        <input
                            type="text"
                            class="form-control"
                            placeholder="<?= $translator->translate("License Number") ?>"
                            id="licenseNumber"
                            name="licenseNumber"
                            value="<?= $filled->licenseNumber ?? "" ?>"
                            pattern="[0-9A-Z\s]{5,20}"
                            title="<?= $translator->translate("The license number must contain at least 5 and at most 20 symbols. Only uppercase Latin letters, spaces, and digits are allowed") ?>"
                            required
                        >
                    </div>
                </div>
                <div class="inline-form-group">
                    <div class="form-group">
                        <label for="phone"><?= $translator->translate("Phone Number") ?></label>
                        <input
                            type="tel"
                            class="form-control"
                            placeholder="<?= $translator->translate("Phone Number") ?>"
                            id="phone"
                            name="phone"
                            value="<?= $filled->phone ?? "" ?>"
                            pattern="[0-9 +.\(\)\-]{2,20}"
                            title="<?= $translator->translate("The phone number can contain only numbers, parentheses, dots, spaces, the + and the -. Length: 2-20") ?>"
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
                <div class="form-group">
                    <label for="representativeAddress"><?= $translator->translate("Address") ?></label>
                    <input
                        type="text"
                        class="form-control"
                        placeholder="<?= $translator->translate("Address") ?>"
                        id="representativeAddress"
                        name="representativeAddress"
                        value="<?= $filled->representativeAddress ?? "" ?>"
                        title="<?= $translator->translate("Only Latin symbols, apostrophes, hyphens, spaces, the comma, the ampersand, and numbers are allowed. Length: 2-64") ?>"
                        pattern="[A-Za-z\s'\-&,0-9]{2,64}"
                        required
                    >
                </div>
                <div class="inline-form-group">
                    <div class="form-group">
                        <label for="representativePostalCode"><?= $translator->translate("Postal Code") ?></label>
                        <input
                            type="text"
                            class="form-control"
                            placeholder="<?= $translator->translate("Postal Code") ?>"
                            id="representativePostalCode"
                            name="representativePostalCode"
                            pattern="(?:[A-Z]\d[A-Z]|\b[A-Z]\d[A-Z][ ]?\d[A-Z]\d\b)"
                            value="<?= $filled->representativePostalCode ?? "" ?>"
                            title = "<?= $translator->translate("Please enter a valid postal code of 3 or 6 alphanumeric characters (e.g. A1B or A1B 2C3)") ?>"
                            required
                        >
                    </div>
                    <div class="form-group">
                        <label for="representativeProvince"><?= $translator->translate("Province") ?></label>
                        <select
                            id="representativeProvince"
                            name="representativeProvince"
                            class="form-select default-tom-select"
                            required
                        >
                            <option value=""><?= $translator->translate("Select Province") ?></option>
                            <?php foreach (Province::cases() as $province) { ?>
                                <option value="<?= $province->name ?>" <?= Ancillary::selectedIf($filled, "representativeProvince", $province->name) ?>>
                                    <?= $province->title($translator) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="form-check mb-4">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="receiveEmails"
                        name="receiveEmails"
                        value="1"
                        <?= Ancillary::checkedIf($filled, "receiveEmails") ?>
                    >
                    <label class="form-check-label" for="receiveEmails">
                        <?= $translator->translate("I would like to receive emails from {appName}", ['appName' => $applicationParameters->getName()]) ?>
                    </label>
                </div>

                <div class="form-footer">
                    <input type="submit" class="btn btn-primary btn-big w-100 mb-4" value="<?= $translator->translate("Continue") ?>">
                    <p class="text-small text-center mb-0">
                        <?= $translator->translate(
                            "By clicking 'Continue' or signing in with the tools below, you accept our {termsOfUse} and have read and understood the {privacyStatement}.",
                            ["termsOfUse" => Ancillary::getTermsOfUseLink($translator), "privacyStatement" => Ancillary::getPrivacyStatementLink($translator)]
                        )
?>
                    </p>
                </div>
            </form>
        </div>
    </div>
    <div class="dealer-block">
        <div class="dealer-info">
            <h3 class="mb-0"><?= $translator->translate("User profile") ?></h3>
        </div>
        <a href="/sign-in" class="btn btn-outline btn-big"><?= $translator->translate("Sign in") ?></a>
    </div>
</div>
