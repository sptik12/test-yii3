<?= $translator->translate("Deletion date: ") . $user->deletionDate ?>
<br>
<a
    class="app-refuse-deletion-user-button btn btn-primary btn-tiny"
    href="<?= $user->clearUserDeletionDateUrl ?>"
    data-user-id="<?= $user->id ?>"
>
    <?= $translator->translate("Refuse Deletion") ?>
</a>
