<div class="filters-list" v-show="filtersCount > 0">
    <span class="single-filter" v-for="displayedFilter in allDisplayedFilters">
        {{ displayedFilter.text }}
        <a href="#" @click.prevent="clearFilterItem(displayedFilter)">
            <svg class="icon" width="13" height="13">
                <use xlink:href="/images/sprites/sprites.svg#icon-clear"></use>
            </svg>
        </a>
    </span>

    <a
        href="#"
        class="clear-all-filters-button"
        @click.stop.prevent="clearAllFilters()"
    >
        <?= $translator->translate("Clear all") ?>
    </a>
</div>

