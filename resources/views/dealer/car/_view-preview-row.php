<?php
use App\Backend\Model\Car\CarStatus;
use App\Backend\Model\Dealer\DealerStatus;

?>

<div class="preview-mode">
    <div class="left-part">
        <svg class="icon" width="37" height="37">
            <use xlink:href="/images/sprites/sprites.svg#icon-warning"></use>
        </svg>
        <div class="preview-mode-text">
            <h3><?= $translator->translate("Preview mode") ?></h3>
            <span class="text-tiny"><?= $translator->translate("You are in demo mode. This data is not visible to other users yet and will only become available after publication.") ?></span>
        </div>
    </div>
    <div class="right-part">
        <button class="btn btn-outline btn-big" @click.prevent="saveDraft()"><?= $translator->translate("Save Draft") ?></button>
        <a href="<?= $urlGenerator->generateAbsolute('dealer.editCar', ['publicId' => $car->publicId], ['checkSession' => 1]) ?>" class="btn btn-outline btn-big"><?= $translator->translate("Edit") ?></a>
        <button
            class="btn btn-big <?= $car->dealerInfo->status != DealerStatus::Active->value ? 'btn-disabled btn-grey' : 'btn-primary' ?>"
            <?= !$allowPublish ? "disabled" : "" ?>
            @click.prevent="publishCar()"
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
</div>
