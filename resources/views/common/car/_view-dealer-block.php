<span class="text-tiny"><?= $translator->translate("Dealer") ?></span>
<div class="dealer-row">
    <div class="dealer-name">
        <span>
            <?= $car->dealerInfo->name ?>
            <span><?= $car->dealerInfo->displayedAddress ?></span>
            <span><?= $car->dealerInfo->phone ?></span>
            <?php if ($car->dealerInfo->website) { ?>
                <a href="<?= $car->dealerInfo->website ?>" target="_blank"><?= $translator->translate("Web site") ?></a>
            <?php } ?>
        </span>
        <a href="<?= $preview ? '#' : $urlGenerator->generate(name: "client.searchCar", queryParameters: ["dealer" => $car->dealerId]) ?>">
            <?= $translator->translate("View stock") ?>
            <svg class="icon" width="18" height="19">
                <use xlink:href="/images/sprites/sprites.svg#icon-arrow-square"></use>
            </svg>
        </a>
    </div>
    <div class="dealer-logo">
        <img src="<?= $car->dealerInfo->logo ?>" alt="">
    </div>
</div>

