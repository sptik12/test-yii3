<div class="text-center d-flex align-items-center justify-content-center gap-2">
    <?php if ($recordsTotal > 1) { ?>
    <a
        class="app-unassign-user-button btn btn-outline btn-tiny"
        href="<?= $user->unassignFromDealerUrl ?>"
        data-user-id="<?= $user->id ?>"
        data-dealer-id="<?= $user->dealerId ?>"
    >
        <?= $translator->translate("Unassign") ?>
    </a>
    <?php } ?>
    <?php if (!$user->isPrimaryDealer) { ?>
        <a
            class="app-set-as-primary-button btn btn-outline btn-tiny"
            href="<?= $user->setUserAsPrimaryInDealerUrl ?>"
            data-user-id="<?= $user->id ?>"
            data-dealer-id="<?= $user->dealerId ?>"
        >
            <?= $translator->translate("Set as Primary Dealer") ?>
        </a>
    <?php } ?>
</div>
