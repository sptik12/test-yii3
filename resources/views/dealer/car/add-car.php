<?php

use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

/**
* @var WebView               $this
* @var TranslatorInterface   $translator
* @var UrlGeneratorInterface $urlGenerator
* @var Yiisoft\Assets\AssetManager $assetManager
* @var string                $csrf
* @var string                $filled
 */

$this->setTitle($translator->translate('Add Car'));
$doAddCarUrl = $urlGenerator->generate('dealer.doAddCar');
$doAddEmptyCarUrl = $urlGenerator->generate('dealer.doAddEmptyCar');
?>

<?= $this->render(
    "//common/car/add-car",
    compact(
        "lastSearchCarUrl",
        "doAddCarUrl",
        "doAddEmptyCarUrl",
    )
);

?>
