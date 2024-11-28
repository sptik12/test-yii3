<?php
use App\Backend\Model\Car\CarStatus;

?>

<div class="form-buttons">
    <button
        type="button"
        class="btn btn-outline btn-big app-btn"
        @click.prevent="saveDraft()"
    >
        <?= $translator->translate("Save Draft") ?>
    </button>
    <?php if ($withPreviewButton) { ?>
        <a class="btn btn-outline btn-big app-btn" href="#" ref="btnPreview" @click.prevent="openPreview()">
            <?= $translator->translate("Preview") ?>
        </a>
    <?php } ?>
    <button
        type="button"
        class="app-btn btn btn-big btn-primary"
        @click.prevent="publishCar()"
        ref="btnPublishInMenu"
    >
        <?= $translator->translate("Publish") ?>
    </button>
</div>
