<?php

/**
 * @var WebView $this
 * @var TranslatorInterface $translator
 * @var UrlGeneratorInterface $urlGenerator
 * @var string $csrf
 * @var Yiisoft\User\CurrentUser $currentUser
 */

$this->setTitle($translator->translate('Home page'));

?>

<div>
    <h1 class="mb-4"><?= $translator->translate("Administrator Dashboard") ?></h1>
    <?= $translator->translate("Hello! This is {appName} Administrator dashboard", ['appName' => $applicationParameters->getName()]) ?>
</div>
