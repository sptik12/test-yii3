<?php
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use App\Backend\Model\Car\CarStatus;
use App\Backend\Model\Dealer\DealerStatus;
use App\Backend\Model\Car\Condition;
use App\Backend\Model\Car\BodyType;
use App\Backend\Model\Car\FuelType;
use App\Backend\Model\Car\Transmission;
use App\Backend\Model\Car\Feature;
use App\Backend\Model\Car\Drivetrain;
use App\Backend\Model\Car\ExtColor;
use App\Backend\Model\Car\IntColor;
use App\Backend\Model\Car\CabinSize;
use App\Backend\Model\Car\BedSize;
use App\Backend\Model\Car\SafetyRating;
use App\Backend\Model\Car\VehicleType;
use App\Frontend\Helper\Ancillary;

/**
* @var WebView               $this
* @var TranslatorInterface   $translator
* @var UrlGeneratorInterface $urlGenerator
* @var Yiisoft\Assets\AssetManager $assetManager
* @var string                $csrf
*/
?>

<form action="" method="post" ref="editCarForm">
    <input type="hidden" name="_csrf" value="<?= $csrf ?>">
    <input type="hidden" id="publicId" name="publicId" v-model="car.publicId">
    <div class="box-wrapper app-step" v-show="isStepDisplayed('vin')" id="step-vin" data-step="vin">
        <h1><?= $translator->translate("Edit Car") ?></h1>
        <div class="form-wrapper">
            <div class="inline-form-group inline-form-group-first flex-nowrap">
                <div class="form-group">
                    <label for="vinCode"><?= $translator->translate("Vin Code") ?></label>
                    <input
                        type="text"
                        maxlength="17"
                        class="form-control form-control-lg"
                        id="vinCode"
                        name="vinCode"
                        ref="vinCode"
                        pattern="[0-9A-Z]{17}"
                        placeholder="<?= $translator->translate("Enter VIN") ?>"
                        title="<?= $translator->translate("The VIN number contains 17 characters, including digits and capital letters") ?>"
                        v-model="car.vinCode"
                        v-typing="{ finish: ($event) => { updatePreviewSession($event, 'vinCode') } }"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                </div>
                <div class="form-group">
                    <a href
                        class="btn btn-outline btn-big w-100"
                        ref="btnSendVinCode"
                        @click.prevent="sendVinCode()"
                    >
                        <svg class="icon" width="20" height="20">
                            <use xlink:href="/images/sprites/sprites.svg#icon-refresh"></use>
                        </svg>
                    </a>
                </div>
            </div>
            <div class="inline-form-group">
                <div class="form-group d-none">
                    <label for="stock"><?= $translator->translate("Stock #") ?></label>
                    <input
                        type="text"
                        class="form-control form-control-lg"
                        id="stockNumber"
                        name="stockNumber"
                        placeholder="<?= $translator->translate("Input Stock") ?>"
                        v-model="car.stockNumber"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                </div>
                <div class="form-group w-100">
                    <label for="condition"><?= $translator->translate("Condition") ?></label>
                    <select
                        id="condition"
                        name="condition"
                        class="form-select form-select-lg"
                        required
                        v-model="car.condition"
                        @change.prevent="updatePreviewSession($event, 'condition')"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                        <option value=""><?= $translator->translate("Select Condition") ?></option>
                        <?php foreach (Condition::cases() as $condition) { ?>
                            <option value="<?= $condition->value ?>">
                                <?= $condition->titleEdit($translator) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="inline-form-group">
                <h3 class="label-with-border w-100"><?= $translator->translate("Mileage (Km)") ?></h3>
                <div class="form-group w-100">
                    <input
                        type="number"
                        class="form-control form-control-lg"
                        id="mileage"
                        name="mileage"
                        placeholder="<?= $translator->translate("Mileage") ?>"
                        required
                        pattern="[0-9]+"
                        max="99999"
                        step="1"
                        v-model="car.mileage"
                        v-typing="{ finish: ($event) => { updatePreviewSession($event, 'mileage') } }"
                        onkeydown="return event.keyCode !== 69"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                </div>
            </div>
        </div>
    </div>
    <div class="box-wrapper app-step" v-show="isStepDisplayed('specifications')" id="step-specifications" data-step="specifications">
        <h2><?= $translator->translate("Specifications") ?></h2>
        <div class="form-wrapper">
            <div class="inline-form-group inline-form-group-2 mb-4">
                <h3 class="label-with-border w-100"><?= $translator->translate("General") ?></h3>
                <div class="form-group w-100">
                    <label for="makeId"><?= $translator->translate("Make") ?></label>
                    <select
                        id="makeId"
                        name="makeId"
                        ref="make"
                        class="form-select form-select-lg"
                        required
                        v-model="car.makeId"
                        @change.prevent="getModels($event), updatePreviewSession($event, 'makeId')"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                        <option value=""><?= $translator->translate("Select Make") ?></option>
                        <?php foreach ($makes as $make) { ?>
                            <option value="<?= $make->id ?>">
                                <?= $make->name ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group w-100">
                    <label for="modelId"><?= $translator->translate("Model") ?></label>
                    <select
                        id="modelId"
                        name="modelId"
                        class="form-select form-select-lg"
                        required
                        v-model="car.modelId"
                        @change.prevent="updatePreviewSession($event, 'modelId')"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                        <option value=""><?= $translator->translate("Select Model") ?></option>
                        <option
                            :value="model.id"
                            v-for="model in models"
                        >
                            {{ model.name }}
                        </option>
                    </select>
                </div>
                <div class="form-group w-100">
                    <label for="year"><?= $translator->translate("Year") ?></label>
                    <select
                        id="year"
                        name="year"
                        class="form-select form-select-lg"
                        required
                        v-model="car.year"
                        @change.prevent="updatePreviewSession($event, 'year')"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                        <option value=""><?= $translator->translate("Select Year") ?></option>
                        <?= Ancillary::getYearsOptions() ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="form-wrapper">
            <div class="inline-form-group inline-form-group-2">
                <h3 class="label-with-border w-100"><?= $translator->translate("Engine") ?></h3>
                <div class="form-group">
                    <label for="engineType"><?= $translator->translate("Engine Type") ?></label>
                    <input
                        class="form-control"
                        id="engineType"
                        name="engineType"
                        v-model="car.engineType"
                        v-typing="{ finish: ($event) => { updatePreviewSession($event, 'engineType') } }"
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="vehicleType"><?= $translator->translate("Vehicle Type") ?></label>
                    <select
                        id="vehicleType"
                        name="vehicleType"
                        class="form-select form-select-lg"
                        required
                        v-model="car.vehicleType"
                        @change.prevent="updatePreviewSession($event, 'vehicleType')"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                        <option value=""><?= $translator->translate("Select Vehicle Type") ?></option>
                        <?php foreach (VehicleType::cases() as $vehicleType) { ?>
                            <option value="<?= $vehicleType->value ?>">
                                <?= $vehicleType->title($translator) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="evBatteryRange"><?= $translator->translate("EV battery range (Km)") ?></label>
                    <input
                        class="form-control form-control-lg"
                        id="evBatteryRange"
                        name="evBatteryRange"
                        v-model="car.evBatteryRange"
                        v-typing="{ finish: ($event) => { updatePreviewSession($event, 'evBatteryRange') } }"
                        type="number"
                        min="0"
                        pattern="[0-9]+"
                        placeholder="<?= $translator->translate("EV battery range") ?>"
                        onkeydown="return event.keyCode !== 69"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                </div>
                <div class="form-group">
                    <label for="evBatteryTime"><?= $translator->translate("EV battery charging time (H)") ?></label>
                    <input
                        type="number"
                        class="form-control form-control-lg"
                        id="evBatteryTime"
                        name="evBatteryTime"
                        v-model="car.evBatteryTime"
                        v-typing="{ finish: ($event) => { updatePreviewSession($event, 'evBatteryTime') } }"
                        min="0"
                        pattern="[0-9]+"
                        onkeydown="return event.keyCode !== 69"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                </div>
            </div>
            <div class="inline-form-group inline-form-group-3 mb-4">
                <div class="form-group">
                    <label for="fuelType"><?= $translator->translate("Fuel Type") ?></label>
                    <select
                        id="fuelType"
                        name="fuelType"
                        class="form-select form-select-lg"
                        required
                        v-model="car.fuelType"
                        @change.prevent="updatePreviewSession($event, 'fuelType')"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                        <option value=""><?= $translator->translate("Select Fuel Type") ?></option>
                        <?php foreach (FuelType::cases() as $fuelType) { ?>
                            <option value="<?= $fuelType->value ?>">
                                <?= $fuelType->title($translator) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="fuelEconomy"><?= $translator->translate("Fuel Economy (L/100Km)") ?></label>
                    <input
                        class="form-control form-control-lg"
                        id="fuelEconomy"
                        name="fuelEconomy"
                        placeholder="<?= $translator->translate("Fuel economy") ?>"
                        v-model="car.fuelEconomy"
                        v-typing="{ finish: ($event) => { updatePreviewSession($event, 'fuelEconomy') } }"
                        type="number"
                        min="0"
                        step="0.1"
                        onkeydown="return event.keyCode !== 69"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                </div>
                <div class="form-group">
                    <label for="co2"><?= $translator->translate("CO2 emissions (g/Km)") ?></label>
                    <input
                        class="form-control form-control-lg"
                        id="co2"
                        name="co2"
                        placeholder="<?= $translator->translate("CO2 emissions") ?>"
                        v-model="car.co2"
                        v-typing="{ finish: ($event) => { updatePreviewSession($event, 'co2') } }"
                        type="number"
                        min="0"
                        pattern="[0-9]+"
                        onkeydown="return event.keyCode !== 69"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                </div>
            </div>
        </div>
        <div class="form-wrapper">
            <div class="inline-form-group inline-form-group-2 mb-4">
                <h3 class="label-with-border w-100"><?= $translator->translate("Transmission") ?></h3>
                <div class="form-group">
                    <label for="drivetrain"><?= $translator->translate("Drivetrain") ?></label>
                    <select
                        id="drivetrain"
                        name="drivetrain"
                        class="form-select form-select-lg"
                        required
                        v-model="car.drivetrain"
                        @change.prevent="updatePreviewSession($event, 'drivetrain')"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                        <option value=""><?= $translator->translate("Select Drivetrain") ?></option>
                        <?php foreach (Drivetrain::cases() as $drivetrain) { ?>
                            <option value="<?= $drivetrain->value ?>">
                                <?= $drivetrain->title($translator) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="transmission"><?= $translator->translate("Transmission") ?></label>
                    <select
                        id="transmission"
                        name="transmission"
                        class="form-select form-select-lg"
                        required
                        v-model="car.transmission"
                        @change.prevent="updatePreviewSession($event, 'transmission')"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                        <option value=""><?= $translator->translate("Select Transmission") ?></option>
                        <?php foreach (Transmission::cases() as $transmission) { ?>
                            <option value="<?= $transmission->value ?>">
                                <?= $transmission->title($translator) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="form-wrapper">
            <div class="inline-form-group inline-form-group-2">
                <h3 class="label-with-border w-100"><?= $translator->translate("Others") ?></h3>
                <div class="form-group w-100">
                    <label for="safetyRating"><?= $translator->translate("NHTSA overall safety rating") ?></label>
                    <select
                        id="safetyRating"
                        name="safetyRating"
                        class="form-select form-select-lg"
                        required
                        v-model="car.safetyRating"
                        @change.prevent="updatePreviewSession($event, 'safetyRating')"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                        <?php foreach (SafetyRating::cases() as $safetyRating) { ?>
                            <option value="<?= $safetyRating->value ?>">
                                <?= $safetyRating->title($translator) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-check form-switch inline-switch-group w-100">
                    <label class="form-check-label" for="certifiedPreOwned"><?= $translator->translate("Certified Pre-Owned") ?></label>
                    <input
                        class="form-check-input me-0"
                        type="checkbox"
                        role="switch"
                        name="certifiedPreOwned"
                        id="certifiedPreOwned"
                        v-model="car.certifiedPreOwned"
                        @change.prevent="updatePreviewSession($event, 'certifiedPreOwned')"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                </div>
            </div>
        </div>
    </div>
    <div class="box-wrapper app-step" v-show="isStepDisplayed('exterior')" id="step-exterior" data-step="exterior">
        <h2><?= $translator->translate("Exterior") ?></h2>
        <div class="form-wrapper">
            <div class="inline-form-group">
                <h3 class="label-with-border w-100"><?= $translator->translate("Body style") ?></h3>
                <div class="body-style-list">
                    <?php foreach (BodyType::cases() as $bodyType) { ?>
                        <div class="form-check">
                            <input
                                type="radio"
                                id="<?= $bodyType->value ?>"
                                name="bodyType"
                                class="btn-check"
                                required
                                autocomplete="off"
                                v-model="car.bodyType"
                                @change.prevent="updatePreviewSession($event, 'bodyType')"
                                value="<?= $bodyType->value ?>"
                                @focus = "setCurrentMobileStepOnFocus($event)"
                            >
                            <label class="body-style-label" for="<?= $bodyType->value ?>">
                                <svg class="icon" width="<?= $bodyType->iconWidth() ?>" height="<?= $bodyType->iconHeight() ?>">
                                    <use href="/images/sprites/cars.svg#icon-<?= $bodyType->picture() ?>"></use>
                                </svg>
                                <?= $bodyType->title($translator) ?>
                            </label>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="inline-form-group">
                <h3 class="label-with-border w-100"><?= $translator->translate("Exterior color") ?></h3>
                <div class="all-colors">
                    <?php foreach (ExtColor::cases() as $color) { ?>
                        <div class="form-check color-form-check">
                            <input
                                type="radio"
                                id="ext<?= $color->value ?>"
                                name="extColor"
                                class="form-check-input color"
                                required
                                data-color="<?= $color->value ?>"
                                v-model="car.extColor"
                                @change.prevent="updatePreviewSession($event, 'extColor')"
                                value="<?= $color->value ?>"
                                @focus = "setCurrentMobileStepOnFocus($event)"
                            >
                            <label class="form-check-label" for="ext<?= $color->value ?>">
                                <?= $color->title($translator) ?>
                            </label>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="inline-form-group inline-form-group-2">
                <h3 class="label-with-border w-100"><?= $translator->translate("Parameters") ?></h3>
                <div class="form-group">
                    <label for="doors"><?= $translator->translate("Number of doors") ?></label>
                    <select
                        id="doors"
                        name="doors"
                        class="form-select form-select-lg"
                        required
                        v-model="car.doors"
                        @change.prevent="updatePreviewSession($event, 'doors')"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                        <option value=""><?= $translator->translate("Select Number of doors") ?></option>
                        <?php for ($i = 2; $i <= 5; $i++) { ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="cabinSize"><?= $translator->translate("Cabin Size") ?></label>
                    <select
                        id="cabinSize"
                        name="cabinSize"
                        class="form-select form-select-lg"
                        required
                        v-model="car.cabinSize"
                        @change.prevent="updatePreviewSession($event, 'cabinSize')"
                    >
                        <option value=""><?= $translator->translate("Select Cabin Size") ?></option>
                        <?php foreach (CabinSize::cases() as $cabinSize) { ?>
                            <option value="<?= $cabinSize->value ?>">
                                <?= $cabinSize->title($translator) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="box-wrapper app-step" v-show="isStepDisplayed('interior')" id="step-interior" data-step="interior">
        <h2><?= $translator->translate("Interior") ?></h2>
        <div class="form-wrapper">
            <div class="inline-form-group">
                <h3 class="label-with-border w-100"><?= $translator->translate("Interior color") ?></h3>
                <div class="all-colors">
                    <?php foreach (IntColor::cases() as $color) { ?>
                        <div class="form-check color-form-check">
                            <input
                                type="radio"
                                id="int<?= $color->value ?>"
                                name="intColor"
                                class="form-check-input color"
                                required
                                data-color="<?= $color->value ?>"
                                v-model="car.intColor"
                                @change.prevent="updatePreviewSession($event, 'intColor')"
                                value="<?= $color->value ?>"
                                @focus = "setCurrentMobileStepOnFocus($event)"
                            >
                            <label class="form-check-label" for="int<?= $color->value ?>">
                                <?= $color->title($translator) ?>
                            </label>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="inline-form-group inline-form-group-2">
                <h3 class="label-with-border w-100"><?= $translator->translate("Parameters") ?></h3>
                <div class="form-group">
                    <label for="bedSize"><?= $translator->translate("Bed size") ?></label>
                    <select
                        id="bedSize"
                        name="bedSize"
                        class="form-select form-select-lg"
                        required
                        v-model="car.bedSize"
                        @change.prevent="updatePreviewSession($event, 'bedSize')"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                        <option value=""><?= $translator->translate("Select Bed Size") ?></option>
                        <?php foreach (BedSize::cases() as $bedSize) { ?>
                            <option value="<?= $bedSize->value ?>">
                                <?= $bedSize->title($translator) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="seats"><?= $translator->translate("Number of seats") ?></label>
                    <select
                        id="seats"
                        name="seats"
                        class="form-select form-select-lg"
                        required
                        v-model="car.seats"
                        @change.prevent="updatePreviewSession($event, 'seats')"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                        <option value=""><?= $translator->translate("Select Number of seats") ?></option>
                        <?php for ($i = 2; $i < 6; $i++) { ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php } ?>
                        <option value="6">6+</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="box-wrapper app-step" v-show="isStepDisplayed('features')" id="step-features" data-step="features">
        <div class="form-wrapper">
            <div class="inline-form-group">
                <h3 class="label-with-border w-100 mt-0"><?= $translator->translate("Features") ?></h3>
                <div class="all-features">
                    <?php foreach (Feature::cases() as $feature) { ?>
                        <div class="form-check feature-form-check">
                            <input
                                type="checkbox"
                                id="<?= $feature->value ?>"
                                name="features[]"
                                class="form-check-input"
                                value="<?= $feature->value ?>"
                                v-model="car.features"
                                @change.prevent="updatePreviewSession($event, 'features')"
                                @focus = "setCurrentMobileStepOnFocus($event)"
                            >
                            <label class="form-check-label" for="<?= $feature->value ?>">
                                <?= $feature->title($translator) ?>
                            </label>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <div class="box-wrapper app-step" v-show="isStepDisplayed('description')" id="step-description" data-step="description">
        <div class="form-wrapper">

            <div class="inline-form-group">
                <h3 class="label-with-border w-100 mt-0"><?= $translator->translate("Description") ?></h3>
                <div class="form-group w-100">
                    <input
                        type="hidden"
                        id="description"
                        name="description"
                        ref="description"
                        class="form-control"
                        placeholder="<?= $translator->translate("Description") ?>"
                        v-model="car.description"
                        @change.prevent="updatePreviewSession($event, 'description')"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                    <div id="quillDescription"  data-placeholder="<?= $translator->translate("Car description") ?>"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="box-wrapper app-step" v-show="isStepDisplayed('carfax')" id="step-carfax" data-step="carfax">
        <div class="form-wrapper">
            <div class="inline-form-group">
                <h3 class="label-with-border w-100 mt-0"><?= $translator->translate("Carfax History Report") ?></h3>
                <div class="form-group w-100">
                    <input
                        type="url"
                        name="carfaxUrl"
                        class="form-control"
                        ref="carfaxUrl"
                        title="<?= $translator->translate("Please enter a valid carfax url") ?>"
                        v-model="car.carfaxUrl"
                        @change.prevent="updatePreviewSession($event, 'carfaxUrl')"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                </div>
                <div class="d-flex align-items-center gap-2">
                    <svg class="icon" width="18" height="19">
                        <use xlink:href="/images/sprites/sprites.svg#icon-info"></use>
                    </svg>
                    <p class="mb-0"><?= $translator->translate("You can get you Carfax {here}", ['here' => Ancillary::getCarfaxLink($translator)]) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="box-wrapper app-step" v-show="isStepDisplayed('medias')" id="step-medias" data-step="medias">
        <div class="form-wrapper">
            <div class="inline-form-group">
                <h3 class="label-with-border w-100 mt-0"><?= $translator->translate("Photos") ?></h3>
            </div>
            <?= $this->render("//common/car/_edit-car-upload-photos") ?>
        </div>
    </div>

    <?php if ($car->dealerId) { ?>
        <div class="box-wrapper app-step" v-show="isStepDisplayed('medias')" data-step="medias">
            <div class="form-wrapper">
                <div class="inline-form-group">
                    <h3 class="label-with-border w-100 mt-0"><?= $translator->translate("Videos") ?></h3>
                </div>
                <?= $this->render("//common/car/_edit-car-upload-videos") ?>
            </div>
        </div>
    <?php } ?>

    <?php if ($car->clientId) { ?>
        <?= $this->render("//common/car/_edit-car-contact-info") ?>
        <?= $this->render("//common/car/_edit-car-location") ?>
    <?php } ?>

    <div class="box-wrapper app-step" v-show="isStepDisplayed('price')" id="step-price" data-step="price">
        <div class="form-wrapper">
            <div class="inline-form-group">
                <h3 class="label-with-border w-100 mt-0"><?= $translator->translate("Price ($)") ?></h3>
                <div class="form-group w-100">
                    <input
                        class="form-control form-control-lg"
                        id="price"
                        name="price"
                        placeholder="<?= $translator->translate("Price") ?>"
                        type="number"
                        max="999999999"
                        min="0"
                        step="1"
                        required
                        v-model="car.price"
                        v-typing="{ finish: ($event) => { updatePreviewSession($event, 'price') } }"
                        onkeydown="return event.keyCode !== 69 && event.keyCode !== 190 && event.keyCode !== 188"
                        @focus = "setCurrentMobileStepOnFocus($event)"
                    >
                </div>
            </div>
            <div class="d-none d-xl-block w-100">
                <?= $submitButtons ?>
            </div>
        </div>
    </div>

</form>
