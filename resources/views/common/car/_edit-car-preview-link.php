<div class="col-12">
    <div class="cancel-button-block justify-content-between">
        <a href="<?= $lastSearchCarUrl ?>" @click.prevent="redirectToUrl($event)">
            <svg class="icon" width="24" height="24">
                <use xlink:href="/images/sprites/sprites.svg#icon-back-arrow"></use>
            </svg>
            <?= $translator->translate("Cancel") ?>
        </a>
        <a href="#" @click.prevent="openPreview()" class="app-preview">
            <?= $translator->translate("Preview") ?>
            <svg class="icon" width="18" height="18">
                <use xlink:href="/images/sprites/sprites.svg#icon-arrow-square"></use>
            </svg>
        </a>
    </div>
    <div class="d-block d-xl-none w-100">
        <?= $submitButtons ?>
    </div>
</div>
