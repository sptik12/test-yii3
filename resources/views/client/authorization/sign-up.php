<?php

declare(strict_types=1);

use App\Frontend\Helper\Ancillary;
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

$this->setTitle($translator->translate("Client Registration"));

?>

<div class="page-authorization page-registration">
    <div class="authorization-main-block">
        <h1><?= $translator->translate("Let's create your account") ?></h1>
        <div class="authorization-box">
            <form action="<?= $urlGenerator->generate('client.doSignUp') ?>" method="post">
                <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                <div class="form-group">
                    <label for="email"><?= $translator->translate("Email address") ?></label>
                    <div class="input-group">
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
                    <div class="app-error-container invalid-tooltip" for="email"></div>
                </div>
                <div class="form-group">
                    <label for="username"><?= $translator->translate("Username") ?></label>
                    <div class="input-group">
                        <input
                            type="text"
                            autocomplete="off"
                            data-lpignore="true"
                            class="form-control"
                            placeholder="<?= $translator->translate("Your Username") ?>"
                            id="username"
                            name="username"
                            value="<?= $filled->username ?? "" ?>"
                            pattern="[0-9A-Za-z '\-]{4,32}"
                            title="<?= $translator->translate("Only Latin symbols, digits, apostrophes, hyphens, and spaces are allowed. Length: 4-32") ?>"
                        >
                    </div>
                    <div class="app-error-container invalid-tooltip" for="username"></div>
                </div>
                <div class="form-group">
                    <label for="password"><?= $translator->translate("Password") ?></label>
                    <div class="input-group mb-2">
                        <input
                            type="password"
                            class="form-control"
                            id="password"
                            name="password"
                            placeholder="******"
                            autocomplete="off"
                            data-lpignore="true"
                            pattern="^(?=.*[a-z])(?=.*[A-Z])\S{5,}$"
                            title="<?= $translator->translate("Password must contain minimum 5 symbols, one of them must be uppercase and another one lowecase, and it must not contain  spaces.") ?>"
                        >
                    </div>
                    <div class="app-error-container invalid-tooltip" for="password"></div>
                </div>
                <div class="form-footer">
                    <input type="submit" class="btn btn-primary btn-big w-100 mb-4" value="<?= $translator->translate("Continue") ?>">
                    <p class="text-small mb-4">
                        <?= $translator->translate(
                            "By clicking 'Continue' or signing in with the tools below, you accept our {termsOfUse} and have read and understood the {privacyStatement}.",
                            ["termsOfUse" => Ancillary::getTermsOfUseLink($translator), "privacyStatement" => Ancillary::getPrivacyStatementLink($translator)]
                        )
?>
                    </p>
                </div>
            </form>
        </div>
        <div class="authorization-footer">
            <p class="text-small mb-0">
                <?= $translator->translate("Already have an account?")?> <a href="/sign-in"><?= $translator->translate("Sign in") ?></a>
            </p>
        </div>
    </div>
    <div class="dealer-block">
        <div class="dealer-info">
            <h3><?= $translator->translate("Dealer profile") ?></h3>
            <p><?= $translator->translate(
                "To get started for free or to learn about our premium packages, call {phone} or use this form and we'll reach out.",
                ['phone' => $applicationParameters->getPhone()]
            ) ?></p>
        </div>
        <a href="/sign-up-dealership" class="btn btn-outline btn-big"><?= $translator->translate("Dealer signup") ?></a>
    </div>
</div>
