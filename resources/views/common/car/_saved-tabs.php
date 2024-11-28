<div class="left-part">
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a
                href = "<?= $urlGenerator->generateAbsolute("client.wishlist") ?>"
                class="nav-link <?= $active == "savedCars" ? 'active' : '' ?>"
                id="saved-cars-tab"
            >
                <?= $translator->translate("Saved Cars") ?>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a
                href = "<?= $urlGenerator->generateAbsolute("client.carSearchUrls") ?>"
                class="nav-link <?= $active == "savedUrls" ? 'active' : '' ?>"
                id="saved-searches-tab"
            >
                <?= $translator->translate("Saved Searches") ?>
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a
                href = "#"
                class="nav-link <?= $active == "savedQuestions" ? 'active' : '' ?>"
                id="saved-questions-tab"
            >
                <?= $translator->translate("Saved Questions") ?>
            </a>
        </li>
    </ul>
</div>
