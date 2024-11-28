<?php
use App\Backend\Model\User\Status;

?>

<?php if ($user->id != $currentUser->getId()) { ?>
<div class="text-end">
    <?php if ($user->originalData->status == Status::Active->value) { ?>
        <a
            class="app-send-code-button btn btn-outline btn-tiny mb-1"
            href="<?= $user->sendOneTimeCodeUrl ?>"
            data-email="<?= $user->email ?>"
        >
            <?= $translator->translate("Send One Time Code") ?>
        </a>
        <a
            class="app-suspend-button btn btn-outline btn-tiny"
            href="<?= $user->suspendUserUrl ?>"
            data-user-id="<?= $user->id ?>"
            data-user-name="<?= $user->originalData->username ?>"
        >
            <?= $translator->translate("Suspend") ?>
        </a>
    <?php } ?>
    <?php if ($user->originalData->status == Status::Disabled->value) { ?>
        <a
            class="app-unsuspend-button btn btn-outline btn-tiny"
            href="<?= $user->unsuspendUserUrl ?>"
            data-user-id="<?= $user->id ?>"
            data-user-name="<?= $user->originalData->username ?>"
        >
            <?= $translator->translate("Unsuspend") ?>
        </a>
    <?php } ?>
    <!--a
        class="app-delete-button"
        href="<?= $user->deleteUrl ?>"
        data-user-id="<?= $user->id ?>"
    >
        <svg class="icon" width="20" height="21">
            <use xlink:href="/images/sprites/sprites.svg#icon-trash"></use>
        </svg>
    </a-->
</div>
<?php } ?>
