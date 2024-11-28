<?php
$name = $currentRoute->getName();

if ($name == 'admin.editUser') {
    $name = $session->get("lastSearchUserRouteName", "admin.users");
}
?>

<div class="dashboard-wrapper">
    <ul>
        <?php if ($currentUser->can('searchUser')) { ?>
        <li class="<?= in_array($name, ['admin.users', 'admin.addUser', 'admin.editUser']) ? 'active' : '' ?>">
            <a href="<?= $urlGenerator->generateAbsolute("admin.users") ?>">
                <svg class="icon" width="16" height="16">
                    <use xlink:href="/images/sprites/sprites.svg#icon-users"></use>
                </svg>
                <?= $translator->translate("Users") ?>
            </a>
        </li>
            <li class="<?= in_array($name, ['admin.accountManagers']) ? 'active' : '' ?>">
                <a href="<?= $urlGenerator->generateAbsolute("admin.accountManagers") ?>">
                    <svg class="icon" width="16" height="16">
                        <use xlink:href="/images/sprites/sprites.svg#icon-users"></use>
                    </svg>
                    <?= $translator->translate("Account Managers") ?>
                </a>
            </li>
        <?php } ?>
        <?php if ($currentUser->can('searchDealer')) { ?>
        <li class="<?= in_array($name, ['admin.dealers', 'admin.addDealer', 'admin.editDealer', 'admin.approveDealer']) ? 'active' : '' ?>">
            <a href="<?= $urlGenerator->generateAbsolute("admin.dealers") ?>">
                <svg class="icon" width="16" height="16">
                    <use xlink:href="/images/sprites/sprites.svg#icon-users"></use>
                </svg>
                <?= $translator->translate("Dealers") ?>
            </a>
        </li>
        <?php } ?>
        <li>&nbsp;</li>
        <li>
            <a href="<?= $urlGenerator->generateAbsolute("client.searchCar") ?>" target="_blank">
                <svg class="icon" width="16" height="16">
                    <use xlink:href="/images/sprites/sprites.svg#icon-users"></use>
                </svg>
                <?= $translator->translate("Client view") ?>
            </a>
        </li>
    </ul>
</div>
