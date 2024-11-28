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
 */
?>

<a href="#" class="prev-link" @click.prevent = "goPrevPage()">
    <img src="/images/theme/icon-chevron-left.svg" v-show="showPrevPage" alt="<?= $translator->translate("Previous page") ?>">
</a>
<span><?= $translator->translate("Page {{currentPage}} of {{totalPages}}") ?></span>
<a href="#" class="next-link" @click.prevent = "goNextPage()">
    <img src="/images/theme/icon-chevron-right.svg" v-show="showNextPage" alt="<?= $translator->translate("Next page") ?>">
</a>
