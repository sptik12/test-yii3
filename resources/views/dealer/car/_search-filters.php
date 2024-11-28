<?php
use App\Backend\Model\Car\CarStatus;

?>

<div class="main-filters">
    <div class="filter-part">
        <div class="status-block flex-nowrap">
            <label for="status"><?= $translator->translate("Status") ?></label>
            <div>
                <div class="card card-body" v-for="status in filtersItemsWithCounts.status">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="status"
                            :value="status.id"
                            :id="status.id"
                            v-model="filters.status"
                            @change.prevent="applyFilters()"
                            :disabled="status.countCars == 0"
                        >
                        <label class="form-check-label" :class="{'disabled': status.countCars == 0}" :for="status.id">
                            {{ status.name }}
                            <span v-html="displayCount(status.countCars, status.id, 'status')"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
