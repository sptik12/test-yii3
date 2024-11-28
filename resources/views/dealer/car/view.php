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
 * @var Yiisoft\User\CurrentUser $currentUser
 * @var Yiisoft\Aliases\Aliases $aliases
 *
 */

$this->setTitle($translator->translate("View Car"));
$preview = false;
?>

<?= $this->render(
    "_view",
    compact(
        "car",
        "lastSearchCarUrl",
        "carNextPublicId",
        "carPrevPublicId",
        "preview"
    )
);
