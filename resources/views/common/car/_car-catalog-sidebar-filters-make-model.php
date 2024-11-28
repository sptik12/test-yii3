<div class="by-car-block">
    <div class="inline-form-group by-car">
        <v-select
            name="make"
            class="form-select"
            @option:selected="getModelsAndAppyFilters($event, -1)"
            @clear="getModelsAndAppyFilters({id: ''}, -1)"
            :options="makes"
            label="name"
            :reduce="make => make.id"
            v-model="filters.make"
            :placeholder="'<?= $translator->translate("Any Make") ?>'"
        >
            <option value=""><?= $translator->translate("Any Make") ?></option>
            <template #selected-option>
                {{ getSelectedMake() }}
            </template>
            <template #no-options="{ search, searching, loading }">
                <span v-if="search.length==0"><?= $translator->translate("Makes list is empty") ?></span>
                <span v-else><?= $translator->translate("Sorry, no matching makes") ?></span>
            </template>
        </v-select>
        <v-select
            name="model"
            class="form-select"
            ref="model"
            @option:selected="applyFilters()"
            @clear="applyFilters()"
            :options="models"
            label="name"
            :reduce="model => model.id"
            v-model="filters.model"
            :placeholder="'<?= $translator->translate("Any Model") ?>'"
        >
            <option value=""><?= $translator->translate("Any Model") ?></option>
            <template #selected-option>
                {{ getSelectedModel() }}
            </template>
            <template #no-options="{ search, searching, loading }">
                <span v-if="search.length==0"><?= $translator->translate("Select make ar first") ?></span>
                <span v-else><?= $translator->translate("Sorry, no matching models") ?></span>
            </template>
        </v-select>
    </div>
    <button
        class="add-car mb-3"
        ref="addMakeModelButton"
        @click.prevent="addMakeModelPairSelect()"
        v-show="filters.make || filters.model"
    >
        <?= $translator->translate("One more model") ?>
    </button>
    <div class="inline-form-group by-car-row" v-for="makeModelPairSelect in makeModelPairsSelects">
        <v-select
            class="form-select"
            @option:selected="getModelsAndAppyFilters($event, makeModelPairSelect.index)"
            @clear="getModelsAndAppyFilters({id: ''}, makeModelPairSelect.index)"
            data-url="<?= $urlGenerator->generate('client.getModelsForViewAjax') ?>"
            :options="makes"
            label="name"
            :reduce="make => make.id"
            v-model="makeModelPairSelect.makeId"
            :placeholder="'<?= $translator->translate("Any Make") ?>'"
        >
            <option value="" :selected="makeModelPairSelect.makeId==''"><?= $translator->translate("Any Make") ?></option>
            <template #selected-option>
                {{ getSelectedMake(makeModelPairSelect.makeId) }}
            </template>
            <template #no-options="{ search, searching, loading }">
                <span v-if="search.length==0"><?= $translator->translate("Makes list is empty") ?></span>
                <span v-else><?= $translator->translate("Sorry, no matching makes") ?></span>
            </template>
        </v-select>
        <v-select
            class="form-select"
            @option:selected="(selected) => {updateMakeModelPairSelect(selected, makeModelPairSelect.index), buildMakeModelPairsFilterAndAppyFilters()}"
            @clear="() => {updateMakeModelPairSelect({id: ''}, makeModelPairSelect.index), buildMakeModelPairsFilterAndAppyFilters()}"
            :options="makeModelPairSelect.models"
            label="name"
            :reduce="model => model.id"
            v-model="makeModelPairSelect.modelId"
            :placeholder="'<?= $translator->translate("Any Model") ?>'"
        >
            <option value="" :selected="makeModelPairSelect.modelId==''"><?= $translator->translate("Any Model") ?></option>
            <template #selected-option>
                {{ getSelectedModel(makeModelPairSelect.modelId, makeModelPairSelect.models) }}
            </template>
            <template #no-options="{ search, searching, loading }">
                <span v-if="search.length==0"><?= $translator->translate("Select make ar first") ?></span>
                <span v-else><?= $translator->translate("Sorry, no matching models") ?></span>
            </template>
        </v-select>
        <a href="#" class="delete-button" @click.prevent="deleteMakeModelPairSelect(makeModelPairSelect.index)">
            <svg class="icon" width="20" height="21">
                <use xlink:href="/images/sprites/sprites.svg#icon-trash"></use>
            </svg>
        </a>
    </div>
</div>
