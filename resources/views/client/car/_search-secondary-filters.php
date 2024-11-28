<?php

use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\View\WebView;
use App\Frontend\Helper\Ancillary;
use App\Backend\Model\Car\BodyType;

/**
 * @var WebView $this
 * @var TranslatorInterface $translator
 * @var UrlGeneratorInterface $urlGenerator
 * @var Yiisoft\Assets\AssetManager $assetManager
 * @var string $csrf
 * @var Yiisoft\User\CurrentUser $currentUser
 */
?>

<div class="secondary-filters app-secondary-filters">
    <div class="all-secondary-filters">

        <!-- BodyTypes -->
        <div class="single-filter-collapse">
            <div class="collapse-top">
                <a href="#"
                    class="custom-link"
                    v-show="!isEmpty(filters.bodyType)"
                    @click.stop.prevent="clearFilter('bodyType')"
                   style="display: none;"
                >
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                    </svg>
                </a>
                <button
                    class="collapse-btn <?= Ancillary::classNotIf($filters, 'bodyType', "collapsed") ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterBodyStyle"
                    aria-expanded="false"
                    aria-controls="filterBodyStyle"
                >
                    <span><?= $translator->translate("Body style") ?></span>
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-arrow-up"></use>
                    </svg>
                </button>
            </div>
            <div
                class="collapse filter-collapse <?= Ancillary::classIf($filters, 'bodyType', "show") ?>"
                id="filterBodyStyle"
            >
                <div class="card card-body">
                    <div class="body-style-list">
                        <div class="form-check" v-for="bodyType in filtersItemsWithCounts.bodyType">
                            <input
                                class="btn-check"
                                type="checkbox"
                                name="bodyType"
                                :value="bodyType.id"
                                :id="bodyType.id"
                                v-model="filters.bodyType"
                                @change.prevent="applyFilters()"
                                :disabled="bodyType.countCars == 0"
                                autocomplete="off"
                            >
                            <label class="body-style-label" :class="{'disabled': bodyType.countCars == 0}" :for="bodyType.id">
                                <svg class="icon" :width="bodyType.iconWidth" :height="bodyType.iconHeight">
                                    <use :href="'/images/sprites/cars.svg#icon-' + bodyType.picture"></use>
                                </svg>
                                <span v-html="bodyType.name + displayCount(bodyType.countCars, bodyType.id, 'bodyType')"></span>
                            </label>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Price -->
        <div class="single-filter-collapse">
            <div class="collapse-top">
                <a href="#"
                    class="custom-link"
                    v-show="filters.minPrice || filters.maxPrice"
                    @click.stop.prevent="clearFilter(['minPrice', 'maxPrice'])"
                   style="display: none;"
                >
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                    </svg>
                </a>
                <button
                    class="collapse-btn <?= Ancillary::classNotIf($filters, ['minPrice', 'maxPrice'], "collapsed") ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterPrice"
                    aria-expanded="false"
                    aria-controls="filterPrice"
                >
                    <span><?= $translator->translate("Price") ?></span>
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-arrow-up"></use>
                    </svg>
                </button>
            </div>
            <div
                class="collapse filter-collapse <?= Ancillary::classIf($filters, ['minPrice', 'maxPrice'], "show") ?>"
                id="filterPrice"
                ref="filterPrice"
                data-validation-message="<?= $translator->translate("Min price cannot be greater than max price") ?>"
            >
                <div class="card card-body">
                    <div class="inline-form-group by-price">
                        <input
                            class="form-control"
                            placeholder="<?= $translator->translate("Min") ?>"
                            name="minPrice"
                            v-model="filters.minPrice"
                            v-typing="{ finish: (event) => validateFilter(event) }"
                            @blur="applyFiltersWithValidate($event)"
                            @keyup.enter="applyFiltersWithValidate($event)"
                            type="number"
                            min="0"
                            pattern="[0-9]+"
                            title="<?= $translator->translate("Please enter an integer values only") ?>"
                        >
                        <span>—</span>
                        <input
                            class="form-control"
                            placeholder="<?= $translator->translate("Max") ?>"
                            name="maxPrice"
                            v-model="filters.maxPrice"
                            v-typing="{ finish: (event) => validateFilter(event) }"
                            @blur="applyFiltersWithValidate($event)"
                            @keyup.enter="applyFiltersWithValidate($event)"
                            type="number"
                            min="0"
                            pattern="[0-9]+"
                            title="<?= $translator->translate("Please enter an integer values only") ?>"
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Year -->
        <div class="single-filter-collapse">
            <div class="collapse-top">
                <a href="#"
                   class="custom-link"
                   v-show="filters.minYear || filters.maxYear"
                   @click.stop.prevent="clearFilter(['minYear', 'maxYear'], $event)"
                   style="display: none;"
                >
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                    </svg>
                </a>
                <button
                    class="collapse-btn <?= Ancillary::classNotIf($filters, ['minYear', 'maxYear'], "collapsed") ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterYear"
                    aria-expanded="false"
                    aria-controls="filterYear"
                >
                    <span><?= $translator->translate("Year") ?></span>
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-arrow-up"></use>
                    </svg>
                </button>
            </div>
            <div
                class="collapse filter-collapse <?= Ancillary::classIf($filters, ['minYear', 'maxYear'], "show") ?>"
                id="filterYear"
                ref="filterYear"
                data-validation-message="<?= $translator->translate("Min year cannot be greater than max year") ?>"
            >
                <div class="card card-body">
                    <div class="inline-form-group by-price">
                        <select
                            name="minYear"
                            id="minYear"
                            class="form-select"
                            placeholder="<?= $translator->translate("Min") ?>"
                            title="<?= $translator->translate("Min") ?>"
                            @change.prevent="onMinYearChanged()"
                            v-model="filters.minYear"
                        >
                            <option value=""><?= $translator->translate("Min") ?></option>
                            <option
                                v-for="year in this.filtersItemsWithCounts.years"
                                :value="year"
                            >
                                {{ year }}
                            </option>
                        </select>
                        <span><?= $translator->translate("to") ?></span>
                        <select
                            name="maxYear"
                            id="maxYear"
                            class="form-select"
                            placeholder="<?= $translator->translate("Max") ?>"
                            title="<?= $translator->translate("Max") ?>"
                            @change.prevent="onMaxYearChanged()"
                            v-model="filters.maxYear"
                        >
                            <option value=""><?= $translator->translate("Max") ?></option>
                            <option
                                    v-for="year in this.filtersItemsWithCounts.years"
                                    :value="year"
                            >
                                {{ year }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Condition -->
        <div class="single-filter-collapse">
            <div class="collapse-top">
                <a href="#"
                   class="custom-link"
                   v-show="filters.condition != 'any' || filters.minMileage || filters.maxMileage"
                   @click.stop.prevent="clearFilter(['condition', 'minMileage', 'maxMileage'])"
                   style="display: none;"
                >
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                    </svg>
                </a>
                <button
                    class="collapse-btn <?= Ancillary::hasValueOrEmpty($filters, 'condition', 'any') ? Ancillary::classNotIf($filters, ['minMileage', 'maxMileage'], "collapsed") : "" ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterCondition"
                    aria-expanded="false"
                    aria-controls="filterCondition"
                >
                    <span><?= $translator->translate("Condition") ?></span>
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-arrow-up"></use>
                    </svg>
                </button>
            </div>
            <div
                class="collapse filter-collapse <?= Ancillary::hasValueOrEmpty($filters, 'condition', 'any') ? Ancillary::classIf($filters, ['minMileage', 'maxMileage'], "show") : "show" ?>"
                id="filterCondition"
                ref="filterCondition"
                data-validation-message="<?= $translator->translate("Min mileage cannot be greater than max mileage") ?>"
            >
                <div class="card card-body">
                    <div class="condition-checkboxes">
                        <div class="form-check">
                            <input
                                type="radio"
                                class="btn-check"
                                name="condition"
                                id="any"
                                autocomplete="off"
                                v-model="filters.condition"
                                @change.prevent="applyFilters()"
                                value="any"
                            >
                            <label class="condition-label" for="any"><?= $translator->translate("Any") ?></label>
                        </div>
                        <div class="form-check">
                            <input
                                type="radio"
                                class="btn-check"
                                name="condition"
                                id="new"
                                autocomplete="off"
                                v-model="filters.condition"
                                @change.prevent="($event) => {applyFilters()}"
                                value="new"
                            >
                            <label class="condition-label" for="new"><?= $translator->translate("New") ?></label>
                        </div>
                        <div class="form-check">
                            <input
                                type="radio"
                                class="btn-check"
                                name="condition"
                                id="mileage"
                                autocomplete="off"
                                v-model="filters.condition"
                                @change.prevent="($event) => {applyFilters()}"
                                value="used"
                            >
                            <label class="condition-label" for="mileage"><?= $translator->translate("Mileage") ?></label>
                        </div>
                    </div>
                    <div
                            class="inline-form-group by-year"
                            v-show="filters.condition != 'new'"
                            id="filterMileage"
                    >
                        <div class="form-group">
                            <label for="fromMileage"><?= $translator->translate("From") ?></label>
                            <input
                                class="form-control"
                                name="minMileage"
                                id="fromMileage"
                                placeholder="<?= $translator->translate("Min") ?>"
                                v-model="filters.minMileage"
                                v-typing="{ finish: (event) => { validateFilter(event) } }"
                                @blur="applyFiltersWithValidate($event)"
                                @keyup.enter="applyFiltersWithValidate($event)"
                                type="number"
                                min="0"
                                pattern="[0-9]+"
                                title="<?= $translator->translate("Please enter an integer values only") ?>"
                            >
                        </div>
                        <span><?= $translator->translate("to") ?></span>
                        <div class="form-group">
                            <label for="toMileage"><?= $translator->translate("To") ?></label>
                            <input
                                class="form-control"
                                name="maxMileage"
                                id="toMileage"
                                placeholder="<?= $translator->translate("Max") ?>"
                                v-model="filters.maxMileage"
                                v-typing="{ finish: (event) => { validateFilter(event) } }"
                                @blur="applyFiltersWithValidate($event)"
                                @keyup.enter="applyFiltersWithValidate($event)"
                                type="number"
                                min="0"
                                pattern="[0-9]+"
                                title="<?= $translator->translate("Please enter an integer values only") ?>"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transmission -->
        <div class="single-filter-collapse">
            <div class="collapse-top">
                <a href="#"
                   class="custom-link"
                   v-show="!isEmpty(filters.transmission)"
                   @click.stop.prevent="clearFilter(['transmission'])"
                   style="display: none;"
                >
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                    </svg>
                </a>
                <button
                    class="collapse-btn <?= Ancillary::classNotIf($filters, 'transmission', "collapsed") ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterGearbox"
                    aria-expanded="false"
                    aria-controls="filterGearbox"
                >
                    <span><?= $translator->translate("Transmission") ?></span>
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-arrow-up"></use>
                    </svg>
                </button>
            </div>
            <div class="collapse filter-collapse <?= Ancillary::classIf($filters, 'transmission', "show") ?>" id="filterGearbox">
                <div class="card card-body" v-for="transmission in filtersItemsWithCounts.transmission">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="transmission"
                            :value="transmission.id"
                            :id="transmission.id"
                            v-model="filters.transmission"
                            @change.prevent="applyFilters()"
                            :disabled="transmission.countCars == 0"
                        >
                        <label class="form-check-label" :class="{'disabled': transmission.countCars == 0}" :for="transmission.id">
                            {{ transmission.name }}
                            <span v-html="displayCount(transmission.countCars, transmission.id, 'transmission')"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Drivetrain -->
        <div class="single-filter-collapse">
            <div class="collapse-top">
                <a href="#"
                   class="custom-link"
                   v-show="!isEmpty(filters.drivetrain)"
                   @click.stop.prevent="clearFilter(['drivetrain'])"
                   style="display: none;"
                >
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                    </svg>
                </a>
                <button
                    class="collapse-btn <?= Ancillary::classNotIf($filters, 'drivetrain', "collapsed") ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterDrivetrain"
                    aria-expanded="false"
                    aria-controls="filterDrivetrain"
                >
                    <span><?= $translator->translate("Drivetrain") ?></span>
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-arrow-up"></use>
                    </svg>
                </button>
            </div>
            <div class="collapse filter-collapse <?= Ancillary::classIf($filters, 'drivetrain', "show") ?>" id="filterDrivetrain">
                <div class="card card-body" v-for="drivetrain in filtersItemsWithCounts.drivetrain">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="drivetrain"
                            :value="drivetrain.id"
                            :id="drivetrain.id"
                            v-model="filters.drivetrain"
                            @change.prevent="applyFilters()"
                            :disabled="drivetrain.countCars == 0"
                        >
                        <label class="form-check-label" :class="{'disabled': drivetrain.countCars == 0}" :for="drivetrain.id">
                            {{ drivetrain.name }}
                            <span v-html="displayCount(drivetrain.countCars, drivetrain.id, 'drivetrain')"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fuel Type -->
        <div class="single-filter-collapse">
            <div class="collapse-top">
                <a href="#"
                   class="custom-link"
                   v-show="!isEmpty(filters.fuelType)"
                   @click.stop.prevent="clearFilter(['fuelType'])"
                   style="display: none;"
                >
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                    </svg>
                </a>
                <button
                    class="collapse-btn <?= Ancillary::classNotIf($filters, 'fuelType', "collapsed") ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterFuelType"
                    aria-expanded="false"
                    aria-controls="filterFuelType"
                >
                    <span><?= $translator->translate("Fuel Type") ?></span>
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-arrow-up"></use>
                    </svg>
                </button>
            </div>
            <div class="collapse filter-collapse <?= Ancillary::classIf($filters, 'fuelType', "show") ?>" id="filterFuelType">
                <div class="card card-body" v-for="fuelType in filtersItemsWithCounts.fuelType">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="fuelType"
                            :value="fuelType.id"
                            :id="fuelType.id"
                            v-model="filters.fuelType"
                            @change.prevent="applyFilters()"
                            :disabled="fuelType.countCars == 0"
                        >
                        <label class="form-check-label"  :class="{'disabled': fuelType.countCars == 0}" :for="fuelType.id">
                            {{ fuelType.name }}
                            <span v-html="displayCount(fuelType.countCars, fuelType.id, 'fuelType')"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Engine Size -->
        <div class="single-filter-collapse">
            <div class="collapse-top">
                <a href="#"
                   class="custom-link"
                   v-show="filters.minEngine || filters.maxEngine"
                   @click.stop.prevent="clearFilter(['minEngine', 'maxEngine'])"
                   style="display: none;"
                >
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                    </svg>
                </a>
                <button
                    class="collapse-btn <?= Ancillary::classNotIf($filters, ['minEngine', 'maxEngine'], "collapsed") ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterEngine"
                    aria-expanded="false"
                    aria-controls="filterEngine"
                >
                    <span><?= $translator->translate("Engine Size (L)") ?></span>
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-arrow-up"></use>
                    </svg>
                </button>
            </div>
            <div
                class="collapse filter-collapse <?= Ancillary::classIf($filters, ['minEngine', 'maxEngine'], "show") ?>"
                id="filterEngine"
                ref="filterEngine"
                data-validation-message="<?= $translator->translate("Min filter value cannot be greater than max filter value") ?>"
            >
                <div class="card card-body">
                    <div class="inline-form-group by-price">
                        <input
                            name="minEngine"
                            class="form-control"
                            placeholder="<?= $translator->translate("From") ?>"
                            v-model="filters.minEngine"
                            v-typing="{ finish: (event) => { validateFilter(event) } }"
                            @blur="applyFiltersWithValidate($event)"
                            @keyup.enter="applyFiltersWithValidate($event)"
                            type="number"
                            min="0"
                            pattern="[0-9]+"
                            title="<?= $translator->translate("Please enter an integer values only") ?>"
                        >
                        <span>—</span>
                        <input
                            name="maxEngine"
                            class="form-control"
                            placeholder="<?= $translator->translate("To") ?>"
                            v-model="filters.maxEngine"
                            v-typing="{ finish: (event) => { validateFilter(event) } }"
                            @blur="applyFiltersWithValidate($event)"
                            @keyup.enter="applyFiltersWithValidate($event)"
                            type="number"
                            min="0"
                            pattern="[0-9]+"
                            title="<?= $translator->translate("Please enter an integer values only") ?>"
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Fuel Economy -->
        <div class="single-filter-collapse">
            <div class="collapse-top">
                <a href="#"
                   class="custom-link"
                   v-show="filters.minFuelEconomy || filters.maxFuelEconomy"
                   @click.stop.prevent="clearFilter(['minFuelEconomy', 'maxFuelEconomy'])"
                   style="display: none;"
                >
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                    </svg>
                </a>
                <button
                    class="collapse-btn <?= Ancillary::classNotIf($filters, ['minFuelEconomy', 'maxFuelEconomy'], "collapsed") ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterFuelEconomy"
                    aria-expanded="false"
                    aria-controls="filterFuelEconomy"
                >
                    <span><?= $translator->translate("Fuel Economy (L/100Km)") ?></span>
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-arrow-up"></use>
                    </svg>
                </button>
            </div>
            <div
                class="collapse filter-collapse <?= Ancillary::classIf($filters, ['minFuelEconomy', 'maxFuelEconomy'], "show") ?>"
                id="filterFuelEconomy"
                ref="filterFuelEconomy"
                data-validation-message="<?= $translator->translate("Min filter value cannot be greater than max filter value") ?>"
            >
                <div class="card card-body">
                    <div class="inline-form-group by-price">
                        <input
                            name="minFuelEconomy"
                            class="form-control"
                            placeholder="<?= $translator->translate("Min") ?>"
                            v-model="filters.minFuelEconomy"
                            v-typing="{ finish: (event) => { validateFilter(event) } }"
                            @blur="applyFiltersWithValidate($event)"
                            @keyup.enter="applyFiltersWithValidate($event)"
                            min="0"
                            pattern="[0-9]+"
                            title="<?= $translator->translate("Please enter an integer values only") ?>"
                        >
                        <span>—</span>
                        <input
                            name="maxFuelEconomy"
                            class="form-control"
                            placeholder="<?= $translator->translate("Max") ?>"
                            v-model="filters.maxFuelEconomy"
                            v-typing="{ finish: (event) => { validateFilter(event) } }"
                            @blur="applyFiltersWithValidate($event)"
                            @keyup.enter="applyFiltersWithValidate($event)"
                            type="number"
                            min="0"
                            pattern="[0-9]+"
                            title="<?= $translator->translate("Please enter an integer values only") ?>"
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- CO2 emissions -->
        <div class="single-filter-collapse">
            <div class="collapse-top">
                <a href="#"
                   class="custom-link"
                   v-show="filters.minCo2 || filters.maxCo2"
                   @click.stop.prevent="clearFilter(['minCo2', 'maxCo2'])"
                   style="display: none;"
                >
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                    </svg>
                </a>
                <button
                    class="collapse-btn <?= Ancillary::classNotIf($filters, ['minCo2', 'maxCo2'], "collapsed") ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterCo2"
                    aria-expanded="false"
                    aria-controls="filterCo2"
                >
                    <span><?= $translator->translate("CO2 emissions (g/Km)") ?></span>
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-arrow-up"></use>
                    </svg>
                </button>
            </div>
            <div
                class="collapse filter-collapse <?= Ancillary::classIf($filters, ['minCo2', 'maxCo2'], "show") ?>"
                id="filterCo2"
                ref="filterCo2"
                data-validation-message="<?= $translator->translate("Min filter value cannot be greater than max filter value") ?>"
            >
                <div class="card card-body">
                    <div class="inline-form-group by-price">
                        <input
                            name="minCo2"
                            class="form-control"
                            placeholder="<?= $translator->translate("Min") ?>"
                            v-model="filters.minCo2"
                            v-typing="{ finish: (event) => { validateFilter(event) } }"
                            @blur="applyFiltersWithValidate($event)"
                            @keyup.enter="applyFiltersWithValidate($event)"
                            type="number"
                            min="0"
                            pattern="[0-9]+"
                            title="<?= $translator->translate("Please enter an integer values only") ?>"
                        >
                        <span>—</span>
                        <input
                            name="maxCo2"
                            class="form-control"
                            placeholder="<?= $translator->translate("Max") ?>"
                            v-model="filters.maxCo2"
                            v-typing="{ finish: (event) => { validateFilter(event) } }"
                            @blur="applyFiltersWithValidate($event)"
                            @keyup.enter="applyFiltersWithValidate($event)"
                            type="number"
                            min="0"
                            pattern="[0-9]+"
                            title="<?= $translator->translate("Please enter an integer values only") ?>"
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Hybrid & electric -->
        <div class="single-filter-collapse">
            <div class="collapse-top">
                <a href="#"
                   class="custom-link"
                   v-show="filters.minEvBatteryRange || filters.maxEvBatteryRange || filters.minEvBatteryTime || filters.maxEvBatteryTime"
                   @click.stop.prevent="clearFilter(['minEvBatteryRange', 'maxEvBatteryRange', 'minEvBatteryTime', 'maxEvBatteryTime'])"
                   style="display: none;"
                >
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                    </svg>
                </a>
                <button
                    class="collapse-btn <?= Ancillary::classNotIf($filters, ['minEvBatteryRange', 'maxEvBatteryRange', 'minEvBatteryTime', 'maxEvBatteryTime'], "collapsed") ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterHybridElectric"
                    aria-expanded="false"
                    aria-controls="filterHybridElectric"
                >
                    <span><?= $translator->translate("Hybrid & electric") ?></span>
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-arrow-up"></use>
                    </svg>
                </button>
            </div>
            <div
                class="collapse filter-collapse <?= Ancillary::classIf($filters, ['minEvBatteryRange', 'maxEvBatteryRange', 'minEvBatteryTime', 'maxEvBatteryTime'], "show") ?>"
                id="filterHybridElectric"
                ref="filterHybridElectric"
                data-validation-message="<?= $translator->translate("Min filter value cannot be greater than max filter value") ?>"
            >
                <div class="card card-body">
                    <div class="inline-form-group flex-wrap by-price w-100 mb-3">
                        <label class="w-100" for="batteryRangeFrom"><?= $translator->translate("EV battery range (Km)") ?></label>
                        <input
                            name="minEvBatteryRange"
                            class="form-control"
                            placeholder="<?= $translator->translate("Min") ?>"
                            id="batteryRangeFrom"
                            v-model="filters.minEvBatteryRange"
                            v-typing="{ finish: (event) => { validateFilter(event) } }"
                            @blur="applyFiltersWithValidate($event)"
                            @keyup.enter="applyFiltersWithValidate($event)"
                            type="number"
                            min="0"
                            pattern="[0-9]+"
                            title="<?= $translator->translate("Please enter an integer values only") ?>"
                        >
                        <span>—</span>
                        <input
                            name="maxEvBatteryRange"
                            class="form-control"
                            placeholder="<?= $translator->translate("Max") ?>"
                            v-model="filters.maxEvBatteryRange"
                            v-typing="{ finish: (event) => { validateFilter(event) } }"
                            @blur="applyFiltersWithValidate($event)"
                            @keyup.enter="applyFiltersWithValidate($event)"
                            type="number"
                            min="0"
                            pattern="[0-9]+"
                            title="<?= $translator->translate("Please enter an integer values only") ?>"
                        >
                    </div>
                    <div class="inline-form-group flex-wrap by-price w-100 mb-3">
                        <label class="w-100" for="batteryChargingFrom"><?= $translator->translate("EV battery charging time (H)") ?></label>
                        <input
                            name="minEvBatteryTime"
                            class="form-control"
                            placeholder="<?= $translator->translate("Min") ?>"
                            id="batteryChargingFrom"
                            v-model="filters.maxEvBatteryTime"
                            v-typing="{ finish: (event) => { validateFilter(event) } }"
                            @blur="applyFiltersWithValidate($event)"
                            @keyup.enter="applyFiltersWithValidate($event)"
                            type="number"
                            min="0"
                            pattern="[0-9]+"
                            title="<?= $translator->translate("Please enter an integer values only") ?>"
                        >
                        <span>—</span>
                        <input
                            name="maxEvBatteryTime"
                            class="form-control"
                            placeholder="<?= $translator->translate("Max") ?>"
                            v-model="filters.maxEvBatteryRange"
                            v-typing="{ finish: (event) => { validateFilter(event) } }"
                            @blur="applyFiltersWithValidate($event)"
                            @keyup.enter="applyFiltersWithValidate($event)"
                            type="number"
                            min="0"
                            pattern="[0-9]+"
                            title="<?= $translator->translate("Please enter an integer values only") ?>"
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Days on market -->
        <div class="single-filter-collapse">
            <div class="collapse-top">
                <a href="#"
                   class="custom-link"
                   v-show="filters.minDaysOnMarket || filters.maxDaysOnMarket"
                   @click.stop.prevent="clearFilter(['minDaysOnMarket', 'maxDaysOnMarket'])"
                   style="display: none;"
                >
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                    </svg>
                </a>
                <button
                    class="collapse-btn <?= Ancillary::classNotIf($filters, ['minDaysOnMarket', 'maxDaysOnMarket'], "collapsed") ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterPublished"
                    aria-expanded="false"
                    aria-controls="filterPublished"
                >
                    <span><?= $translator->translate("Days on market") ?></span>
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-arrow-up"></use>
                    </svg>
                </button>
            </div>
            <div
                class="collapse filter-collapse <?= Ancillary::classIf($filters, ['minDaysOnMarket', 'maxDaysOnMarket'], "show") ?>"
                id="filterPublished"
                ref="filterPublished"
                data-validation-message="<?= $translator->translate("Min filter value cannot be greater than max filter value") ?>"
            >
                <div class="card card-body">
                    <div class="inline-form-group by-price">
                        <input
                            class="form-control"
                            placeholder="<?= $translator->translate("Min") ?>"
                            name="minPrice"
                            v-model="filters.minDaysOnMarket"
                            v-typing="{ finish: (event) => { validateFilter(event) } }"
                            @blur="applyFiltersWithValidate($event)"
                            @keyup.enter="applyFiltersWithValidate($event)"
                            type="number"
                            min="0"
                            pattern="[0-9]+"
                            title="<?= $translator->translate("Please enter an integer values only") ?>"
                        >
                        <span>—</span>
                        <input
                            class="form-control"
                            placeholder="<?= $translator->translate("Max") ?>"
                            name="maxPrice"
                            v-model="filters.maxDaysOnMarket"
                            v-typing="{ finish: (event) => { validateFilter(event) } }"
                            @blur="applyFiltersWithValidate($event)"
                            @keyup.enter="applyFiltersWithValidate($event)"
                            type="number"
                            min="0"
                            pattern="[0-9]+"
                            title="<?= $translator->translate("Please enter an integer values only") ?>"
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Hide vehicles without photos -->
        <div class="single-filter-collapse">
            <div class="form-check form-switch inline-switch-group">
                <label class="form-check-label" for="withPhotos" ref="withPhotosLabel"><?= $translator->translate("Hide vehicles without photos") ?></label>
                <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    name="withPhotos"
                    id="withPhotos"
                    @change.prevent="applyFilters()"
                    value="1"
                    v-model="filters.withPhotos"
                >
            </div>
        </div>

        <!-- Price drops -->
        <div class="single-filter-collapse">
            <div class="form-check form-switch inline-switch-group">
                <label class="form-check-label" for="priceDrops" ref="priceDropsLabel"><?= $translator->translate("Price drops") ?></label>
                <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    name="priceDrops"
                    id="priceDrops"
                    @change.prevent="applyFilters()"
                    value="1"
                    v-model="filters.priceDrops"
                >
            </div>
        </div>

        <!-- Doors -->
        <div class="single-filter-collapse">
            <div class="collapse-top">
                <a href="#"
                   class="custom-link"
                   v-show="!isEmpty(filters.doors)"
                   @click.stop.prevent="clearFilter(['doors'])"
                   style="display: none;"
                >
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                    </svg>
                </a>
                <button
                    class="collapse-btn <?= Ancillary::classNotIf($filters, 'doors', "collapsed") ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterDoors"
                    aria-expanded="false"
                    aria-controls="filterDoors"
                >
                    <span><?= $translator->translate("Number of doors") ?></span>
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-arrow-up"></use>
                    </svg>
                </button>
            </div>
            <div class="collapse filter-collapse <?= Ancillary::classIf($filters, 'doors', "show") ?>" id="filterDoors">
                <div class="card card-body" v-for="door in filtersItemsWithCounts.doors">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            name="doors"
                            type="checkbox"
                            :value="door.value"
                            :id="door.id"
                            v-model="filters.doors"
                            @change.prevent="applyFilters()"
                            :disabled="door.countCars == 0"
                        >
                        <label class="form-check-label" :class="{'disabled': door.countCars == 0}" :for="door.id">
                            {{ door.name }}
                            <span v-html="displayCount(door.countCars, door.id, 'door')"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cabin Size -->
        <div class="single-filter-collapse">
            <div class="collapse-top">
                <a href="#"
                   class="custom-link"
                   v-show="!isEmpty(filters.cabinSize)"
                   @click.stop.prevent="clearFilter(['cabinSize'])"
                   style="display: none;"
                >
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                    </svg>
                </a>
                <button
                    class="collapse-btn <?= Ancillary::classNotIf($filters, 'cabinSize', "collapsed") ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterCabin"
                    aria-expanded="false"
                    aria-controls="filterCabin"
                >
                    <span><?= $translator->translate("Cabin size") ?></span>
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-arrow-up"></use>
                    </svg>
                </button>
            </div>
            <div class="collapse filter-collapse <?= Ancillary::classIf($filters, 'cabinSize', "show") ?>" id="filterCabin">
                <div class="card card-body" v-for="cabinSize in filtersItemsWithCounts.cabinSize">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            name="cabinSize"
                            type="checkbox"
                            :value="cabinSize.id"
                            :id="cabinSize.id"
                            v-model="filters.cabinSize"
                            @change.prevent="applyFilters()"
                            :disabled="cabinSize.countCars == 0"
                        >
                        <label class="form-check-label" :class="{'disabled': cabinSize.countCars == 0}" :for="cabinSize.id">
                            {{ cabinSize.name }}
                            <span v-html="displayCount(cabinSize.countCars, cabinSize.id, 'cabinSize')"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bed Size -->
        <div class="single-filter-collapse">
            <div class="collapse-top">
                <a href="#"
                   class="custom-link"
                   v-show="!isEmpty(filters.bedSize)"
                   @click.stop.prevent="clearFilter(['bedSize'])"
                   style="display: none;"
                >
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                    </svg>
                </a>
                <button
                    class="collapse-btn <?= Ancillary::classNotIf($filters, 'bedSize', "collapsed") ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterBed"
                    aria-expanded="false"
                    aria-controls="filterBed"
                >
                	<span><?= $translator->translate("Bed size") ?></span>
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-arrow-up"></use>
                    </svg>
                </button>
            </div>
            <div class="collapse filter-collapse <?= Ancillary::classIf($filters, 'bedSize', "show") ?>" id="filterBed">
                <div class="card card-body" v-for="bedSize in filtersItemsWithCounts.bedSize">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            name="bedSize"
                            type="checkbox"
                            :value="bedSize.id"
                            :id="bedSize.id"
                            v-model="filters.bedSize"
                            @change.prevent="applyFilters()"
                            :disabled="bedSize.countCars == 0"
                        >
                        <label class="form-check-label" :class="{'disabled': bedSize.countCars == 0}" :for="bedSize.id">
                            {{ bedSize.name }}
                            <span v-html="displayCount(bedSize.countCars, bedSize.id, 'bedSize')"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seats -->
        <div class="single-filter-collapse">
            <div class="collapse-top">
                <a href="#"
                   class="custom-link"
                   v-show="!isEmpty(filters.seats)"
                   @click.stop.prevent="clearFilter(['seats'])"
                   style="display: none;"
                >
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                    </svg>
                </a>
                <button
                    class="collapse-btn <?= Ancillary::classNotIf($filters, 'seats', "collapsed") ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterSeats"
                    aria-expanded="false"
                    aria-controls="filterSeats"
                >
                    <span><?= $translator->translate("Number of seats") ?></span>
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-arrow-up"></use>
                    </svg>
                </button>
            </div>
            <div class="collapse filter-collapse <?= Ancillary::classIf($filters, 'seats', "show") ?>" id="filterSeats">
                <div class="card card-body" v-for="seat in filtersItemsWithCounts.seats">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            name="seats"
                            type="checkbox"
                            :value="seat.value"
                            :id="seat.id"
                            v-model="filters.seats"
                            @change.prevent="applyFilters()"
                            :disabled="seat.countCars == 0"
                        >
                        <label class="form-check-label" :class="{'disabled': seat.countCars == 0}" :for="seat.id">
                            {{ seat.name }}
                            <span v-html="displayCount(seat.countCars, seat.id, 'seat')"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exterior Color -->
        <div class="single-filter-collapse">
            <div class="collapse-top">
                <a href="#"
                   class="custom-link"
                   v-show="!isEmpty(filters.extColor)"
                   @click.stop.prevent="clearFilter(['extColor'])"
                   style="display: none;"
                >
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                    </svg>
                </a>
                <button
                    class="collapse-btn <?= Ancillary::classNotIf($filters, 'extColor', "collapsed") ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterExtColor"
                    aria-expanded="false"
                    aria-controls="filterExtColor"
                >
                    <span><?= $translator->translate("Exterior color") ?></span>
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-arrow-up"></use>
                    </svg>
                </button>
            </div>
            <div class="collapse filter-collapse <?= Ancillary::classIf($filters, 'extColor', "show") ?>" id="filterExtColor">
                <div class="card card-body" v-for="extColor in filtersItemsWithCounts.extColor">
                    <div class="form-check color-form-check">
                        <input
                            class="form-check-input color"
                            name="extColor"
                            type="checkbox"
                            :value="extColor.id"
                            :id="'ext_' + extColor.id"
                            v-model="filters.extColor"
                            @change.prevent="applyFilters()"
                            :data-color="extColor.id"
                            :disabled="extColor.countCars == 0"
                        >
                        <label class="form-check-label" :class="{'disabled': extColor.countCars == 0}" :for="'ext_' + extColor.id">
                            {{ extColor.name }}
                            <span v-html="displayCount(extColor.countCars, extColor.id, 'extColor')"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interior Color -->
        <div class="single-filter-collapse">
            <div class="collapse-top">
                <a href="#"
                   class="custom-link"
                   v-show="!isEmpty(filters.intColor)"
                   @click.stop.prevent="clearFilter(['intColor'])"
                   style="display: none;"
                >
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                    </svg>
                </a>
                <button
                    class="collapse-btn <?= Ancillary::classNotIf($filters, 'intColor', "collapsed") ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterIntColor"
                    aria-expanded="false"
                    aria-controls="filterIntColor"
                >
                    <span><?= $translator->translate("Interior color") ?></span>
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-arrow-up"></use>
                    </svg>
                </button>
            </div>
            <div class="collapse filter-collapse <?= Ancillary::classIf($filters, 'intColor', "show") ?>" id="filterIntColor">
                <div class="card card-body" v-for="intColor in filtersItemsWithCounts.intColor">
                    <div class="form-check color-form-check">
                        <input
                            class="form-check-input color"
                            name="intColor"
                            type="checkbox"
                            :value="intColor.id"
                            :id="'int' + intColor.id"
                            v-model="filters.intColor"
                            @change.prevent="applyFilters()"
                            :data-color="intColor.id"
                            :disabled="intColor.countCars == 0"
                        >
                        <label class="form-check-label" :class="{'disabled': intColor.countCars == 0}" :for="'int' + intColor.id">
                            {{ intColor.name }}
                            <span v-html="displayCount(intColor.countCars, intColor.id, 'intColor')"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Certified Pre-Owned -->
        <div class="single-filter-collapse">
            <div class="form-check form-switch inline-switch-group">
                <label class="form-check-label" for="certifiedPreOwned" ref="certifiedPreOwnedLabel"><?= $translator->translate("Certified Pre-Owned") ?></label>
                <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    name="certifiedPreOwned"
                    id="certifiedPreOwned"
                    @change.prevent="applyFilters()"
                    value="1"
                    v-model="filters.certifiedPreOwned"
                >
            </div>
        </div>

        <!-- Safety Rating -->
        <div class="single-filter-collapse">
            <div class="collapse-top">
                <a href="#"
                   class="custom-link"
                   v-show="!isEmpty(filters.safetyRating)"
                   @click.stop.prevent="clearFilter(['safetyRating'])"
                   style="display: none;"
                >
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                    </svg>
                </a>
                <button
                    class="collapse-btn <?= Ancillary::classNotIf($filters, 'safetyRating', "collapsed") ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterSafetyRating"
                    aria-expanded="false"
                    aria-controls="filterSafetyRating"
                >
                    <span :class="{ hiddentext: !isEmpty(filters.safetyRating) }"><?= $translator->translate("NHTSA overall safety rating") ?></span>
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-arrow-up"></use>
                    </svg>
                </button>
            </div>
            <div class="collapse filter-collapse <?= Ancillary::classIf($filters, 'safetyRating', "show") ?>" id="filterSafetyRating">
                <div class="card card-body" v-for="safetyRating in filtersItemsWithCounts.safetyRating">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            name="safetyRating"
                            type="checkbox"
                            :value="safetyRating.id"
                            :id="safetyRating.id"
                            v-model="filters.safetyRating"
                            @change.prevent="applyFilters()"
                            :disabled="safetyRating.countCars == 0"
                        >
                        <label class="form-check-label" :class="{'disabled': safetyRating.countCars == 0}" :for="safetyRating.id">
                            {{ safetyRating.name }}
                            <span v-html="displayCount(safetyRating.countCars, safetyRating.id, 'safetyRating')"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features -->
        <div class="single-filter-collapse">
            <div class="collapse-top">
                <a href="#"
                   class="custom-link"
                   v-show="!isEmpty(filters.feature)"
                   @click.stop.prevent="clearFilter(['feature'], $event)"
                   style="display: none;"
                >
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-clear-filter"></use>
                    </svg>
                </a>
                <button
                    class="collapse-btn <?= Ancillary::classNotIf($filters, 'feature', "collapsed") ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#filterFeatures"
                    aria-expanded="false"
                    aria-controls="filterFeatures"
                >
                    <span><?= $translator->translate("Features") ?></span>
                    <svg class="icon" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-arrow-up"></use>
                    </svg>
                </button>
            </div>
            <div class="collapse filter-collapse <?= Ancillary::classIf($filters, 'feature', "show") ?>" id="filterFeatures">
                <div class="card card-body" v-for="feature in filtersItemsWithCounts.feature">
                    <div class="form-check">
                        <input
                            type="checkbox"
                            class="form-check-input"
                            name="feature"
                            :id="feature.id"
                            autocomplete="off"
                            :value="feature.id"
                            @change.prevent="applyFilters()"
                            v-model="filters.feature"
                            :disabled="feature.countCars == 0"
                        >
                        <label class="form-check-label" :class="{'disabled': feature.countCars == 0}" :for="feature.id">
                            {{ feature.name }}<span v-html="displayCount(feature.countCars, feature.id, 'feature')"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
