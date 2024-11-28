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

<?php $this->head() ?>
<?= $this->render("{$layoutPath}/_header_client") ?>

<main>
    <?= $content ?>
</main>

<footer>
    <p class="mb-0">© <?= date("Y") ?>  <?= Html::encode($applicationParameters->getName()) ?></p>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
