<?php

declare(strict_types=1);

use App\Frontend\Asset\AppAsset;
use Yiisoft\Html\Html;
use Yiisoft\I18n\Locale;

/**
 * @var App\Frontend\ApplicationParameters $applicationParameters
 * @var Yiisoft\Aliases\Aliases $aliases
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var string $content
 * @var string|null $csrf
 * @var Locale $locale
 * @var Yiisoft\View\WebView $this
 * @var Yiisoft\Router\CurrentRoute $currentRoute
 * @var Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\User\CurrentUser $currentUser
 */

$assetManager->register(AppAsset::class);

$this->addCssFiles($assetManager->getCssFiles());
$this->addCssStrings($assetManager->getCssStrings());
$this->addJsFiles($assetManager->getJsFiles());
$this->addJsStrings($assetManager->getJsStrings());
$this->addJsVars($assetManager->getJsVars());

$layoutPath = $aliases->get("@layout");

$this->beginPage()
?><!DOCTYPE html>
<html lang="<?= Html::encode($locale->language()) ?>">
<head>
    <meta charset="<?= Html::encode($applicationParameters->getCharset()) ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-param" content="_csrf">
    <meta name="csrf-token" content="<?= $csrf ?>">
    <title><?= Html::encode($this->getTitle()) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<?= $this->render("./_header_dealer")?>

<main>
    <?php if ($displayNotApprovedMessage) { ?>
        <div class="flash-notification">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xl-7 col-12">
                        <div class="flash-notification-for-dealer">
                            <svg class="icon" width="37" height="37">
                                <use xlink:href="/images/sprites/sprites.svg#icon-warning"></use>
                            </svg>
                            <div class="note-text">
                                <h3><?= $translator->translate("We're reviewing your application!") ?></h3>
                                <p class="text-tiny"><?= $translator->translate("You can access the Dealership Panel, but your cars will only appear in the catalogue once your application is approved.") ?></p>
                            </div>     
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    <?= $content ?>
</main>

<?= $this->render("./_footer_dealer")?>

<?php $this->endBody() ?>
<?= $this->render("./_noty") ?>
<?= $this->render("./_response-errors", compact("responseErrors")) ?>
</body>
</html>
<?php $this->endPage() ?>
