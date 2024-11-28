<?php

declare(strict_types=1);

use App\Frontend\Helper\Ancillary;
use App\Frontend\Asset\Client\AuthAsset;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 * @var TranslatorInterface $translator
 * @var UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var string $csrf
 * @var string $filled
 * @var string $returnUrl
 */

$assetManager->register(AuthAsset::class);
$this->setTitle($translator->translate('Login'));

?>

<div class="page-authorization">
    <div class="authorization-main-block" id="vue-auth">
        <h1><?= $translator->translate("Sign in") ?></h1>
        <div class="login-tabs">
            <a href="#" @click.prevent="setActive('sign-in-password')" :class="{ active: isActive('sign-in-password') }"><?= $translator->translate("With password") ?></a>
            <a href="#" @click.prevent="setActive('sign-in-code')" :class="{ active: isActive('sign-in-code') }"><?= $translator->translate("With one time code") ?></a>
        </div>

        <div class="authorization-box" :class="{ 'd-none': !isActive('sign-in-password') }" id="sign-in-password">
            <form action="<?= $urlGenerator->generate('client.doSignIn') ?>" method="post">
                <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                <div class="form-group">
                    <label for="email"><?= $translator->translate("Email address") ?></label>
                    <input
                        type="email"
                        class="form-control"
                        id="email-sign-in"
                        name="email"
                        value="<?= $filled->email ?? "" ?>"
                        required
                        placeholder="mail@example.com"
                        v-model="email"
                    >
                    <div class="app-error-container invalid-tooltip" for="email"></div>
                </div>
                <div class="form-group">
                    <label for="password"><?= $translator->translate("Password") ?></label>
                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        <?php /*pattern="^(?=.*[a-z])(?=.*[A-Z])\S{5,}$"
                        title="<?= $translator->translate("Password must contain minimum 5 symbols, one of them must be uppercase and another one lowercase, and no spaces.") ?>"*/ ?>
                        value=""
                        required
                        placeholder="******"
                    >
                    <div class="app-error-container invalid-tooltip" for="password"></div>
                </div>
                <div class="form-group">
                    <input type="hidden" name="returnUrl" value="<?= $returnUrl ?>">
                    <input type="submit" class="submit-btn btn btn-primary btn-big w-100" value="<?= $translator->translate("Sign in") ?>">
                </div>
            </form>
        </div>

        <div class="authorization-box" :class="{ 'd-none': !isActive('sign-in-code') }" id="sign-in-code">
            <form
                method="post"
                v-bind:action="
                    isCodeSent
                      ? '<?= $urlGenerator->generate('client.signInByCodeAjax') ?>'
                      : '<?= $urlGenerator->generate('client.sendCodeAjax') ?>'
                "
                ref="formAuthByCode"
            >
                <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                <div class="form-group">
                    <label for="email"><?= $translator->translate("Email address") ?></label>
                    <div class="input-group">
                        <input
                            type="email"
                            class="form-control"
                            id="email-sign-in-by-code"
                            name="email"
                            value="<?= $filled->email ?? "" ?>"
                            required
                            placeholder="mail@example.com"
                            v-model="email"
                        >
                    </div>
                    <div class="app-error-container invalid-tooltip" for="email"></div>
                </div>

                <template v-if="isCodeSent">
                    <div class="form-group">
                        <label for="email"><?= $translator->translate("Code") ?></label>
                        <div class="input-group">
                            <input
                                class="form-control"
                                id="code"
                                name="code"
                                pattern="[0-9]{6}"
                                title="<?= $translator->translate("Please enter 6 digits") ?>"
                                required
                                placeholder="Code"
                                ref="code"
                                @click.prevent="sendCode()"
                            >
                        </div>
                        <div class="app-error-container invalid-tooltip" for="code"></div>
                    </div>
                </template>

                <div class="form-group">
                    <button
                        type="submit"
                        class="btn btn-primary btn-big w-100"
                        ref="btnSendOrSubmitCode"
                        @click.prevent="sendOrSubmitCode()"
                        data-text-send-code = "<?= $translator->translate("Send Code") ?>"
                        data-text-sign-in = "<?= $translator->translate("Sign In") ?>"
                    >
                        {{ buttonText }}
                    </button>
                </div>
            </form>
        </div>

        <div class="form-footer">
            <p class="text-small mb-4">
                <?= $translator->translate(
                    "By clicking 'Sign In' you accept our {termsOfUse} and have read and understood the {privacyStatement}.",
                    ["termsOfUse" => Ancillary::getTermsOfUseLink($translator), "privacyStatement" => Ancillary::getPrivacyStatementLink($translator)]
                )
?>
            </p>
            <div class="authorization-buttons-wrapper mb-4">
                <span><?= $translator->translate("or") ?></span>
                <div class="authorization-buttons">
                    <a href="/sign-in-social?socialProvider=facebook" class="btn btn-outline btn-big d-none"><?= $translator->translate("Sign in with Facebook") ?></a>
                    <a href="/sign-in-social?socialProvider=google" class="btn btn-outline btn-big"><?= $translator->translate("Sign in with Google") ?></a>
                </div>
            </div>
        </div>
        <div class="authorization-footer">
            <p class="text-small mb-0">
                <?= $translator->translate("Don't have an account?") ?> <a href="/sign-up"><?= $translator->translate("Sign up here") ?></a>
            </p>
        </div>
    </div>

    <div class="dealer-block">
        <div class="dealer-info">
            <h3><?= $translator->translate("Dealer profile") ?></h3>
            <p><?= $translator->translate(
                "To get started for free or to learn about our premium packages, call {phone} or use this form and we'll reach out.",
                ['phone' => $applicationParameters->getPhone()]
            )
?>
            </p>
        </div>
        <a href="/sign-up-dealership" class="btn btn-outline btn-big"><?= $translator->translate("Dealer signup") ?></a>
    </div>
</div>
