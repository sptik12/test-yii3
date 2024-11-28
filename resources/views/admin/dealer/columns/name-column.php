<?php if ($dealer->canUpdateDealer) { ?>
    <a href="<?= $dealer->editDealerUrl ?>"><?= $dealer->originalData->name ?></a>
<?php } else { ?>
<?= $dealer->originalData->name ?>
<?php } ?>
