<div class="filters-title">
    <h3>
        <svg class="icon app-filters-back-btn d-lg-none" width="24" height="24">
            <use xlink:href="/images/sprites/sprites.svg#icon-back-arrow"></use>
        </svg>
        <?= $translator->translate("Filter By") ?>
        <span v-show="filtersCount > 0">{{ filtersCount }}</span>
    </h3>
    <a
        href="#"
        class="custom-link"
        v-show="filtersCount > 0"
        @click.stop.prevent="clearAllFilters()"
    >
        <?= $translator->translate("Clear all") ?>
    </a>
</div>
