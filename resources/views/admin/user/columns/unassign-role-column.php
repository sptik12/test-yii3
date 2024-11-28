<?php if ($user->canUnassignFromRole) { ?>
<div class="text-end">
    <a
        class="app-unassign-user-button btn btn-primary btn-tiny"
        href="<?= $user->unassignFromRoleUrl ?>"
        data-user-id="<?= $user->id ?>"
        data-dealer-id="<?= $user->dealerId ?>"
        data-role="<?= $user->role ?>"
    >
        <?= $translator->translate("Unassign") ?>
    </a>
</div>
<?php } ?>
