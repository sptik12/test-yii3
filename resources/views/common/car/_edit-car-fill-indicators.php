<a href="#" class="edit-items-list-close-button" @click.prevent="toggleMobileMenu()">
    <svg class="icon" width="24" height="24">
        <use xlink:href="/images/sprites/sprites.svg#icon-close"></use>
    </svg>
    <?= $translator->translate("Menu") ?>
</a>
<ul>
    <li :class="{'finished': isFilledStep('vin')}">
        <a href="#step-vin" @click="jumpToMobileStep('vin')">
            <svg class="icon" width="24" height="24">
                <use xlink:href="/images/sprites/sprites.svg#icon-green-check"></use>
            </svg>
            <span class="app-step-vin-title"><?= $translator->translate("VIN") ?></span>
        </a>
    </li>
    <li :class="{'finished': isFilledStep('specifications')}">
        <a href="#step-specifications" @click="openMobileStepFromMenu('specifications')">
            <svg class="icon" width="24" height="24">
                <use xlink:href="/images/sprites/sprites.svg#icon-green-check"></use>
            </svg>
            <span class="app-step-specifications-title"><?= $translator->translate("Specifications") ?></span>
        </a>
    </li>
    <li :class="{'finished': isFilledStep('exterior')}">
        <a href="#step-exterior" @click="openMobileStepFromMenu('exterior')">
            <svg class="icon" width="24" height="24">
                <use xlink:href="/images/sprites/sprites.svg#icon-green-check"></use>
            </svg>
            <span class="app-step-exterior-title"><?= $translator->translate("Exterior") ?></span>
        </a>
    </li>
    <li :class="{'finished': isFilledStep('interior')}">
        <a href="#step-interior" @click="openMobileStepFromMenu('interior')">
            <svg class="icon" width="24" height="24">
                <use xlink:href="/images/sprites/sprites.svg#icon-green-check"></use>
            </svg>
            <span class="app-step-interior-title"><?= $translator->translate("Interior") ?></span>
        </a>
    </li>
    <li :class="{'finished': isFilledStep('features')}">
        <a href="#step-features" @click="openMobileStepFromMenu('features')">
            <svg class="icon" width="24" height="24">
                <use xlink:href="/images/sprites/sprites.svg#icon-green-check"></use>
            </svg>
            <span class="app-step-features-title"><?= $translator->translate("Features") ?></span>
        </a>
    </li>
    <li :class="{'finished': isFilledStep('description')}">
        <a href="#step-description" @click="openMobileStepFromMenu('description')">
            <svg class="icon" width="24" height="24">
                <use xlink:href="/images/sprites/sprites.svg#icon-green-check"></use>
            </svg>
            <span class="app-step-description-title"><?= $translator->translate("Description") ?></span>
        </a>
    </li>
    <li :class="{'finished': isFilledStep('carfax')}">
        <a href="#step-carfax" @click="openMobileStepFromMenu('carfax')">
            <svg class="icon" width="24" height="24">
                <use xlink:href="/images/sprites/sprites.svg#icon-green-check"></use>
            </svg>
            <span class="app-step-carfax-title"><?= $translator->translate("Carfax") ?></span>
        </a>
    </li>
    <li :class="{'finished': isFilledStep('medias')}">
        <a href="#step-medias" @click="openMobileStepFromMenu('medias')">
            <svg class="icon" width="24" height="24">
                <use xlink:href="/images/sprites/sprites.svg#icon-green-check"></use>
            </svg>
            <span class="app-step-medias-title"><?= $translator->translate("Medias") ?></span>
        </a>
    </li>
    <?php if ($car->clientId) { ?>
        <li :class="{'finished': isFilledStep('contact-info')}">
            <a href="#step-contact-info" @click="openMobileStepFromMenu('contact-info')">
                <svg class="icon" width="24" height="24">
                    <use xlink:href="/images/sprites/sprites.svg#icon-green-check"></use>
                </svg>
                <span class="app-step-contact-info-title"><?= $translator->translate("Contact Information") ?></span>
            </a>
        </li>
        <li :class="{'finished': isFilledStep('location')}">
            <a href="#step-location" @click="openMobileStepFromMenu('location')">
                <svg class="icon" width="24" height="24">
                    <use xlink:href="/images/sprites/sprites.svg#icon-green-check"></use>
                </svg>
                <span class="app-step-location-title"><?= $translator->translate("Location") ?></span>
            </a>
        </li>
    <?php } ?>
    <li :class="{'finished': isFilledStep('price')}">
        <a href="#step-price" @click="openMobileStepFromMenu('price')">
            <svg class="icon" width="24" height="24">
                <use xlink:href="/images/sprites/sprites.svg#icon-green-check"></use>
            </svg>
            <span class="app-step-price-title"><?= $translator->translate("Price") ?></span>
        </a>
    </li>
</ul>
<a href="#" class="clear-button" @click.prevent="clearAll()" v-show="!isMobileVersion()">
    <svg class="icon" width="13" height="13">
        <use xlink:href="/images/sprites/sprites.svg#icon-clear"></use>
    </svg>
    <?= $translator->translate("Сlear all") ?>
</a>

