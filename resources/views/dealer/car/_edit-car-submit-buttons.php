<?php
use App\Backend\Model\Car\CarStatus;
use App\Backend\Model\Dealer\DealerStatus;

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
        class="app-btn btn btn-big <?= $car->dealerInfo->status != DealerStatus::Active->value ? 'btn-disabled btn-grey' : 'btn-primary' ?>"
        @click.prevent="publishCar()"
        ref="btnPublishInMenu"
        data-bs-toggle="<?= $car->dealerInfo->status != DealerStatus::Active->value ? 'tooltip' : '' ?>"
        title="<?= $car->dealerInfo->status != DealerStatus::Active->value
            ?
                $translator->translate("We're reviewing your application!") .
                $translator->translate("You can access the Dealership Panel, but your cars will only appear in the catalogue once your application is approved.")
            :
                ''
?>"
    >
        <?= $translator->translate("Publish") ?>
    </button>
</div>
