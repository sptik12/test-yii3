<?php
use App\Frontend\Helper\Ancillary;

?>

<div class="col-xl-7">
    <div class="card-top-part d-xl-none">
        <div class="card-badges">
            <div class="<?= $car->priceStatusColor ?>-badge single-badge"><?= $car->priceStatusName ?></div>
            <?php if ($car->certifiedPreOwned) { ?>
                <div class="grey-badge single-badge"><?= $translator->translate("Certified Pre-Owned") ?></div>
            <?php } ?>
        </div>
        <div class="card-title">
            <h2><?= $car->year ?> <?= $car->makeName ?> <?= $car->modelName ?> <?= $car->trim ?> <?= $car->bodyTypeName ?></h2>
        </div>
        <?php if ($car->carfaxUrl) { ?>
            <div class="car-fax-button">
                <a href="<?= $car->carfaxUrl ?>" target="_blank">
                    <?= $translator->translate("Carfax History Report") ?>
                    <svg class="icon" width="18" height="19">
                        <use xlink:href="/images/sprites/sprites.svg#icon-arrow-square"></use>
                    </svg>
                </a>
            </div>
        <?php } ?>
        <?php if (!$preview && $car->canSaveCarToWishlist) { ?>
            <div class="card-rating">
                <a class="add-to-wishlist" :class="{active: car.isCarSaved == 1}" @click.prevent="toggleSavedCar(car.id)">
                    <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.965 16.5581C9.71 16.6481 9.29 16.6481 9.035 16.5581C6.86 15.8156 2 12.7181 2 7.46813C2 5.15063 3.8675 3.27563 6.17 3.27563C7.535 3.27563 8.7425 3.93563 9.5 4.95563C10.2575 3.93563 11.4725 3.27563 12.83 3.27563C15.1325 3.27563 17 5.15063 17 7.46813C17 12.7181 12.14 15.8156 9.965 16.5581Z" fill="#FB4A4A" stroke="#FB4A4A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <?= $translator->translate("Save") ?>
                </a>
            </div>
        <?php } ?>
    </div>
    <div class="image-carousel-wrapper">
        <div class="image-carousel-block">
                <?php if (!$car->hasMedias) { ?>
                    <div class="carousel-item active">
                        <a data-src="<?= $car->mediaMain->baseUrl ?>">
                            <img src="<?= $car->mediaMain->baseUrl ?>" class="d-block w-100" alt="">
                        </a>
                    </div>
                <?php } else { ?>
                    <div id="imageCarousel" ref="bsCarouselContainer" class="carousel slide">
                        <div class="carousel-inner app-lg-container" ref="lgContainer" >
                            <?php for ($i = 0; $i < count($car->mediasActive) && $i < 3; $i++) { ?>
                                <div
                                    class="carousel-item"
                                    :class="{'active': activeCarouselSlide == <?= $i ?>}"
                                >
                                    <a class="open-lg-item <?= $car->mediasActive[$i]->isVideo ? 'video' : '' ?>" data-slide="<?= $i ?>">
                                        <?php if ($car->mediasActive[$i]->isVideo) { ?>
                                            <img src="<?= $car->mediasActive[$i]->videoPreviewUrl ?>" class="d-block w-100" alt="">
                                        <?php } else { ?>
                                            <img src="<?= $car->mediasActive[$i]->baseUrl ?>" class="d-block w-100" alt="">
                                        <?php }  ?>
                                    </a>
                                </div>
                            <?php } ?>
                        </div>
                        <?php if (count($car->mediasActive) > 1) { ?>
                            <button class="carousel-control-prev" type="button" data-bs-target="#imageCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true">
                                    <svg class="icon" width="12" height="13">
                                        <use xlink:href="/images/sprites/sprites.svg#icon-chevron-left"></use>
                                    </svg>
                                </span>
                            </button>
                            <span class="d-lg-none carousel-control-count"><?= $translator->translate("image") ?> {{ activeCarouselSlide + 1}} <?= $translator->translate("of") ?> 3</span>
                            <button class="carousel-control-next" type="button" data-bs-target="#imageCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true">
                                    <svg class="icon" width="12" height="13">
                                        <use xlink:href="/images/sprites/sprites.svg#icon-chevron-right"></use>
                                    </svg>
                                </span>
                            </button>
                        <?php } ?>
                    </div>
                <?php } ?>
        </div>
        <?php if ($car->hasMedias) { ?>
            <div class="image-thumbnails-block">
                <div class="carousel-indicators">
                    <?php for ($i = 0; $i < count($car->mediasActive) && $i < 3; $i++) { ?>
                            <button
                                type="button"
                                data-bs-target="#imageCarousel"
                                data-bs-slide-to="<?= $i ?>"
                                class="<?= $car->mediasActive[$i]->isVideo ? 'video' : '' ?>"
                                :class="{'active': activeCarouselSlide == <?= $i ?>}"
                                aria-current="true"
                            >
                                <?php if ($car->mediasActive[$i]->isVideo) { ?>
                                    <img src="<?= $car->mediasActive[$i]->videoPreviewUrl ?>" class="d-block w-100" alt="">
                                <?php } else { ?>
                                    <img src="<?= $car->mediasActive[$i]->catalogUrl ?>" class="d-block w-100" alt="">
                                <?php }  ?>
                            </button>
                    <?php } ?>
                </div>
                <?php if (count($car->mediasActive) > 3) { ?>
                        <div class="show-more-images">
                            <button type="button" class="open-lg-item" data-slide="3">
                               <span>+<?= count($car->mediasActive) - 3 ?> <?= $translator->translate("Medias") ?></span>
                               <img src="/images/temp/slide4.png" class="d-block w-100" alt="">
                            </button>
                        </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
    <!--div class="rate-block d-xl-none">
        <span class="full"></span>
        <span class="full"></span>
        <span class="full"></span>
        <span class="full"></span>
        <span class="empty"></span>
        440+ Reviewer
    </div-->
    <div class="price-block d-xl-none">
        <div class="h1"><?= $car->priceFormatted ?></div>
        <?php if ($car->oldPrice) { ?>
            <div class="old-price"><?= $car->oldPrice ?></div>
        <?php } ?>
    </div>
    <div class="dealer-link d-xl-none">
        <?php if ($car->dealerId) { ?>
            <?= $this->render('//common/car/_view-dealer-block', compact("preview", "car")); ?>
        <?php } ?>
        <?php if ($car->clientId) { ?>
            <?= $this->render('//common/car/_view-client-block', compact("preview", "car")); ?>
        <?php } ?>
    </div>
    <div class="vehicle-characteristics-block">
        <div class="main-vehicle-characteristics">
            <div class="single-main-characteristics">
                <div class="single-main-vehicle">
                    <svg class="icon" width="16" height="30">
                        <use xlink:href="/images/sprites/sprites.svg#icon-mileage"></use>
                    </svg>
                    <p class="mb-0"><?= $translator->translate("Mileage") ?></p>
                </div>
                <h3 class="mb-0"><?= $car->mileageName ?></h3>
            </div>
            <div class="single-main-characteristics">
                <div class="single-main-vehicle">
                    <svg class="icon" width="16" height="30">
                        <use xlink:href="/images/sprites/sprites.svg#icon-year"></use>
                    </svg>
                    <p class="mb-0"><?= $translator->translate("Year") ?></p>
                </div>
                <h3 class="mb-0"><?= $car->year ?></h3>
            </div>
            <div class="single-main-characteristics">
                <div class="single-main-vehicle">
                    <svg class="icon" width="16" height="30">
                        <use xlink:href="/images/sprites/sprites.svg#icon-drivetrain"></use>
                    </svg>
                    <p class="mb-0"><?= $translator->translate("Drivetrain") ?></p>
                </div>
                <h3 class="mb-0"><?= $car->drivetrainName ?></h3>
            </div>
            <div class="single-main-characteristics">
                <div class="single-main-vehicle">
                    <svg class="icon" width="16" height="30">
                        <use xlink:href="/images/sprites/sprites.svg#icon-transmission"></use>
                    </svg>
                    <p class="mb-0"><?= $translator->translate("Transmission") ?></p>
                </div>
                <h3 class="mb-0"><?= $car->transmissionName ?></h3>
            </div>
            <div class="single-main-characteristics">
                <div class="single-main-vehicle">
                    <svg class="icon" width="16" height="30">
                        <use xlink:href="/images/sprites/sprites.svg#icon-fuel-type"></use>
                    </svg>
                    <p class="mb-0"><?= $translator->translate("Fuel type") ?></p>
                </div>
                <h3 class="mb-0"><?= $car->fuelTypeName ?></h3>
            </div>
            <div class="single-main-characteristics">
                <div class="single-main-vehicle">
                    <svg class="icon" width="16" height="30">
                        <use xlink:href="/images/sprites/sprites.svg#icon-engine-size"></use>
                    </svg>
                    <p class="mb-0"><?= $translator->translate("Engine size") ?></p>
                </div>
                <h3 class="mb-0"><?= $car->engineTypeName ?></h3>
            </div>
        </div>
        <div class="secondary-vehicle-characteristics">
            <div class="secondary-main-characteristics">
                <p class="mb-1"><?= $translator->translate("Fuel economy") ?></p>
                <h3><?= $car->fuelEconomyName ?></h3>
            </div>
            <div class="secondary-main-characteristics">
                <p class="mb-1"><?= $translator->translate("CO2 emissions") ?></p>
                <h3><?= $car->co2Name ?></h3>
            </div>
            <div class="secondary-main-characteristics">
                <p class="mb-1"><?= $translator->translate("Number of doors") ?></p>
                <h3><?= $car->doors ?></h3>
            </div>
            <?php if ($car->extColorName) { ?>
            <div class="secondary-main-characteristics">
                <p class="mb-1"><?= $translator->translate("Exterior colour") ?></p>
                <h3><?= $car->extColorName ?></h3>
            </div>
            <?php } ?>
            <?php if ($car->evBatteryRangeName) { ?>
            <div class="secondary-main-characteristics">
                <p class="mb-1"><?= $translator->translate("EV battery range") ?></p>
                <h3><?= $car->evBatteryRangeName ?></h3>
            </div>
            <?php } ?>
            <?php if ($car->cabinSizeName) { ?>
            <div class="secondary-main-characteristics">
                <p class="mb-1"><?= $translator->translate("Cabin size") ?></p>
                <h3><?= $car->cabinSizeName ?></h3>
            </div>
            <?php } ?>
            <?php if ($car->intColorName) { ?>
            <div class="secondary-main-characteristics">
                <p class="mb-1"><?= $translator->translate("Interior colour") ?></p>
                <h3><?= $car->intColorName ?></h3>
            </div>
            <?php } ?>
            <?php if ($car->evBatteryTimeName) { ?>
            <div class="secondary-main-characteristics">
                <p class="mb-1"><?= $translator->translate("EV battery charging time") ?></p>
                <h3><?= $car->evBatteryTimeName ?></h3>
            </div>
            <?php } ?>
            <?php if ($car->bedSizeName) { ?>
            <div class="secondary-main-characteristics">
                <p class="mb-1"><?= $translator->translate("Bed size") ?></p>
                <h3><?= $car->bedSizeName ?></h3>
            </div>
            <?php } ?>
            <?php if ($car->seats) { ?>
            <div class="secondary-main-characteristics">
                <p class="mb-1"><?= $translator->translate("Number of seats") ?></p>
                <h3><?= $car->seats ?></h3>
            </div>
            <?php } ?>
            <?php if ($car->safetyRatingName) { ?>
            <div class="secondary-main-characteristics">
                <p class="mb-1"><?= $translator->translate("NHTSA overall safety") ?></p>
                <h3><?= $car->safetyRatingName ?></h3>
            </div>
            <?php } ?>
        </div>
        <div class="overview-block">
            <h3><?= $translator->translate("Overview") ?></h3>
            <div ref="description" class="truncated-text"><?= $car->descriptionShort ?></div>
            <?php if ($car->isDescriptionTruncated) { ?>
                <a href="#" class="text-medium" @click.prevent="showFullDescription()" v-if="showFullDescriptionLink"><?= $translator->translate("Show full description") ?></a>
                <a href="#" class="text-medium" @click.prevent="hideFullDescription()" v-else><?= $translator->translate("Hide full description") ?></a>
            <?php } ?>
            <div class="full-description-block">
                <div class="main-detailed-description">
                    <p><span><?= $translator->translate("Make") ?>:</span><?= $car->makeName ?></p>
                    <p><span><?= $translator->translate("Model") ?>:</span><?= $car->modelName ?></p>
                    <p><span><?= $translator->translate("Body type") ?>:</span><?= $car->bodyTypeName ?></p>
                    <p class="mb-0"><span><?= $translator->translate("Condition") ?>:</span><?= $car->conditionName ?></p>
                </div>
                <div class="main-detailed-description">
                    <p><span><?= $translator->translate("VIN") ?>:</span><?= $car->vinCode ?></p>
                    <p><span><?= $translator->translate("Reg. date") ?>:</span><?= $car->published ?></p>
                    <p class="mb-0"><span><?= $translator->translate("Stock number") ?>:</span><?= $car->stockNumber ?></p>
                </div>
            </div>
        </div>
        <div class="features-block">
            <h3><?= $translator->translate("Features") ?></h3>
            <?php for ($i = 0; $i < count($car->featuresNames); $i += 2) { ?>
            <div class="all-features-block">
                <div class="main-features-block">
                    <div class="single-main-features">
                        <svg class="icon" width="18" height="18">
                            <use xlink:href="/images/sprites/sprites.svg#icon-circle-plus"></use>
                        </svg>
                        <p class="mb-0"><?= $car->featuresNames[$i] ?></p>
                    </div>
                </div>
                <?php if ($i + 1 < count($car->featuresNames)) { ?>
                <div class="main-features-block">
                    <div class="single-main-features">
                        <svg class="icon" width="18" height="18">
                            <use xlink:href="/images/sprites/sprites.svg#icon-circle-plus"></use>
                        </svg>
                        <p class="mb-0"><?= $car->featuresNames[$i + 1] ?></p>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
        <?php if ($car->dealerId) { ?>
            <div class="dealer-block"></div>
        <?php } ?>
    </div>
</div>
<div class="col-xl-5">
    <div class="right-part">
        <div class="card-top-part d-none d-xl-block">
            <div class="card-badges">
                <div class="<?= $car->priceStatusColor ?>-badge single-badge"><?= $car->priceStatusName ?></div>
                <?php if ($car->certifiedPreOwned) { ?>
                <div class="grey-badge single-badge"><?= $translator->translate("Certified Pre-Owned") ?></div>
                <?php } ?>
            </div>
            <div class="card-title">
                <h2><?= $car->year ?> <?= $car->makeName ?> <?= $car->modelName ?> <?= $car->trim ?> <?= $car->bodyTypeName ?></h2>
            </div>
            <?php if ($car->carfaxUrl) { ?>
                <div class="car-fax-button">
                    <a href="<?= $car->carfaxUrl ?>" target="_blank">
                        <?= $translator->translate("Carfax History Report") ?>
                        <svg class="icon" width="18" height="19">
                            <use xlink:href="/images/sprites/sprites.svg#icon-arrow-square"></use>
                        </svg>
                    </a>
                </div>
            <?php } ?>
            <div class="card-rating justify-content-end">
                <!--<div class="rate-block">
                    <span class="full"></span>
                    <span class="full"></span>
                    <span class="full"></span>
                    <span class="full"></span>
                    <span class="empty"></span>
                    440+ Reviewer
                </div>-->
                <?php if (!$preview && $car->canSaveCarToWishlist) { ?>
                <button
                    type="button"
                    class="add-to-wishlist"
                    :class="{active: car.isCarSaved == 1}"
                    @click.prevent="toggleSavedCar(car.id)"
                >
                    <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.965 16.5581C9.71 16.6481 9.29 16.6481 9.035 16.5581C6.86 15.8156 2 12.7181 2 7.46813C2 5.15063 3.8675 3.27563 6.17 3.27563C7.535 3.27563 8.7425 3.93563 9.5 4.95563C10.2575 3.93563 11.4725 3.27563 12.83 3.27563C15.1325 3.27563 17 5.15063 17 7.46813C17 12.7181 12.14 15.8156 9.965 16.5581Z" fill="#FB4A4A" stroke="#FB4A4A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <?php } ?>
            </div>
            <div class="price-block">
                <div class="h1"><?= $car->priceFormatted ?></div>
                <?php if ($car->oldPrice) { ?>
                    <div class="old-price"><?= $car->oldPrice ?></div>
                <?php } ?>
            </div>
            <div class="dealer-link">
                <?php if ($car->dealerId) { ?>
                    <?= $this->render('//common/car/_view-dealer-block', compact("preview", "car")); ?>
                <?php } ?>
                <?php if ($car->clientId) { ?>
                    <?= $this->render('//common/car/_view-client-block', compact("preview", "car")); ?>
                <?php } ?>
            </div>
        </div>
        <div class="request-form app-request-form">
            <div class="request-form-title">
                <h3 class="app-request-title">
                    <svg class="icon d-xl-none" width="24" height="24">
                        <use xlink:href="/images/sprites/sprites.svg#icon-back-arrow"></use>
                    </svg>
                    <?= $translator->translate("Request information") ?>
                </h3>
                <p class="text-small"><?= $translator->translate("Call {phone} or use this form", ['phone' => $applicationParameters->getPhone()]) ?></p>
            </div>
            <form action="">
                <label for="your-name" class="text-medium w-100 main-label"><?= $translator->translate("Hello, my name is") ?></label>
                <div class="inline-form-group">
                    <div class="form-group">
                        <input type="text" class="form-control" name="your-name" id="your-name" <?= ($preview) ? "readonly" : "" ?> placeholder="<?= $translator->translate("Full Name") ?>">
                    </div>
                    <div class="form-group">
                        <select name="your-choice" id="your-choice" class="form-select" <?= ($preview) ? "disabled" : "" ?> placeholder="<?= $translator->translate("I'd like to") ?>" title="<?= $translator->translate("I'd like to") ?>">
                            <option value=""><?= $translator->translate("I'd like to") ?></option>
                            <option value="1"><?= $translator->translate("Buy") ?></option>
                            <option value="2"><?= $translator->translate("Rent") ?></option>
                            <option value="3"><?= $translator->translate("Consult") ?></option>
                        </select>
                    </div>
                </div>
                <p class="text-bold mb-4"><?= $car->year ?> <?= $car->makeName ?> <?= $car->modelName ?> <?= $car->trim ?> <?= $car->bodyTypeName ?></p>
                <div class="inline-form-group inline-form-group-2">
                    <div class="form-group">
                        <label for="postalCode"><?= $translator->translate("Postal Code") ?></label>
                        <input type="text" class="form-control" name="postalCode" id="postalCode" <?= ($preview) ? "readonly" : "" ?> placeholder="<?= $translator->translate("Postal Code") ?>">
                    </div>
                    <div class="form-group">
                        <label for="phoneNumber"><?= $translator->translate("Phone Number") ?></label>
                        <input type="tel" class="form-control" name="phoneNumber" id="phoneNumber" <?= ($preview) ? "readonly" : "" ?> placeholder="<?= $translator->translate("Phone Number") ?>">
                    </div>
                    <div class="form-group">
                        <label for="email"><?= $translator->translate("Email") ?></label>
                        <input type="email" class="form-control" name="email" id="email" <?= ($preview) ? "readonly" : "" ?> placeholder="<?= $translator->translate("Email") ?>">
                    </div>
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="emailMe" id="" value="1" <?= ($preview) ? "disabled" : "" ?>>
                    <label class="form-check-label" for="rwd-4wd"><?= $translator->translate("Email me new results for my search") ?></label>
                </div>
                <button class="btn btn-primary btn-big w-100" :disabled="preview"><?= $translator->translate("Send message") ?></button>
                <p class="text-tiny mt-4">
                    <?= $translator->translate("By submitting my contact information on {appName}, I agree to receive communications from {appName}, from the vehicle's seller and from the seller's agent(s).", ['appName' => $applicationParameters->getName()]) ?>
                    <?= $translator->translate("If I include my phone number, I agree to receive calls and text messages (including via automation).") ?>
                    <?= $translator->translate(
                        "I can opt out at any time. I also agree to the {termsOfUse} and {privacyStatement}, which explain how my data is used to better understand my vehicle shopping interests.",
                        ["termsOfUse" => Ancillary::getTermsOfUseLink($translator), "privacyStatement" => Ancillary::getPrivacyStatementLink($translator)]
                    )
?>
                </p>
            </form>
        </div>
        <div class="small-banner">
            <img src="/images/temp/banner.jpg" alt="">
        </div>
        <div class="flying-button d-xl-none">
            <button class="btn btn-primary app-fly-button" :disabled="preview"><?= $translator->translate("Message") ?></button>
        </div>
    </div>
</div>
