<span class="text-tiny"><?= $translator->translate("Seller") ?></span>
<div class="dealer-row">
    <div class="dealer-name">
        <span>
            <?= $car->contactName ?>
            <span><?= $car->displayedPublicAddress ?></span>
            <?php if ($car->phone) { ?>
                <span><?= $car->phone ?></span>
            <?php } ?>
        </span>
    </div>
</div>

