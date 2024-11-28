<?php

declare(strict_types=1);

/**
 * @var WebView $this
 * @var TranslatorInterface $translator
 * @var ApplicationParameters $applicationParameters
 */

use App\Frontend\ApplicationParameters;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;

$this->setTitle($applicationParameters->getName());
?>

<div class="text-center">
    <h1>Test</h1>
</div>
