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
        <a href="<?= $urlGenerator->generateAbsolute('client.editCar', ['publicId' => $car->publicId], ['checkSession' => 1]) ?>" class="btn btn-outline btn-big"><?= $translator->translate("Edit") ?></a>
        <button
            class="btn btn-primary btn-big"
            <?= !$allowPublish ? "disabled" : "" ?>
            @click.prevent="publishCar()"
        >
            <?= $translator->translate("Publish") ?>
        </button>
    </div>
</div>
