<div class="mobile-flying-panel">
    <div class="panel-top-part">
        <a href="#" class="edit-items-list-nav-btn" @click.prevent="toggleMobileMenu()">
            <svg class="icon" width="24" height="24">
                <use xlink:href="/images/sprites/sprites.svg#icon-menu-mobile"></use>
            </svg>
            <?= $translator->translate("Menu") ?>
        </a>
        <span class="step-title" v-html="currentMobileStepTitle"></span>
    </div>
    <div class="panel-buttons">
        <button class="btn btn-outline btn-big" v-show="!isCurrentMobileStepFirst()" @click.prevent = "setPreviousCurrentMobileStep()"><?= $translator->translate("Back") ?></button>
        <button class="btn btn-primary btn-big" v-show="!isCurrentMobileStepLast()" @click.prevent = "setNextCurrentMobileStep()"><?= $translator->translate("Next") ?></button>
    </div>
</div>
