<?php
use App\Backend\Model\Dealer\DealerStatus;

?>

<div class="text-center">
    <?php if ($dealer->originalData->status == DealerStatus::New->value && $dealer->canApproveDealer) { ?>
        <a
            class="app-approve-button btn btn-primary btn-tiny mb-1"
            href="<?= $dealer->approveDealerUrl ?>"
            data-dealer-id="<?= $dealer->id ?>"
            data-dealer-name="<?= $dealer->originalData->name ?>"
        >
            <?= $translator->translate("Approve") ?>
        </a>
    <?php } ?>
    <a
            class="btn btn-outline btn-tiny app-login-as-dealer-button mb-1"
            href="<?= $dealer->loginAsDealerUrl ?>"
    >
        <?= $translator->translate("Login as dealer") ?>
    </a>
    <?php if ($dealer->originalData->status == DealerStatus::Active->value && $dealer->canSuspendDealer) { ?>
        <a
            class="app-suspend-button btn btn-outline btn-tiny"
            href="<?= $dealer->suspendDealerUrl ?>"
            data-dealer-id="<?= $dealer->id ?>"
            data-dealer-name="<?= $dealer->originalData->name ?>"
        >
            <?= $translator->translate("Suspend") ?>
        </a>
    <?php } ?>
    <?php if ($dealer->originalData->status == DealerStatus::Disabled->value &&  $dealer->canUnsuspendDealer) { ?>
        <a
            class="app-unsuspend-button btn btn-outline btn-tiny"
            href="<?= $dealer->unsuspendDealerUrl ?>"
            data-dealer-id="<?= $dealer->id ?>"
            data-dealer-name="<?= $dealer->originalData->name ?>"
        >
            <?= $translator->translate("Unsuspend") ?>
        </a>
    <?php } ?>
</div>
