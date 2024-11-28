<div class="card-page-navigation">
    <a href="<?= $preview ? '#' : $lastSearchCarUrl ?>" class="back-button">
        <svg class="icon" width="24" height="24">
            <use xlink:href="/images/sprites/sprites.svg#icon-back-arrow"></use>
        </svg>
        <?= $translator->translate("All results") ?>
    </a>
    <div class="prev-next-buttons">
        <?php if ($carPrevPublicId) { ?>
            <a href="<?= $preview ? '#' : $urlGenerator->generateAbsolute("dealer.viewCar", ["publicId" => $carPrevPublicId]) ?>" class="prev-button">
                <svg class="icon" width="18" height="18">
                    <use xlink:href="/images/sprites/sprites.svg#icon-arrow-square"></use>
                </svg>
                <?= $translator->translate("Previous") ?>
            </a>
        <?php } ?>
        <?php if ($carNextPublicId) { ?>
            <a href="<?= $preview ? '#' : $urlGenerator->generateAbsolute("dealer.viewCar", ["publicId" => $carNextPublicId]) ?>" class="next-button">
                <?= $translator->translate("Next") ?>
                <svg class="icon" width="18" height="18">
                    <use xlink:href="/images/sprites/sprites.svg#icon-arrow-square"></use>
                </svg>
            </a>
        <?php } ?>
    </div>
</div>
