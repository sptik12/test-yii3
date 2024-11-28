<div class="image-wrapper"
     :style="{background: 'url(' + car.mediaMain.catalogUrl + ') no-repeat center' }"
     :id="'single-card-image-wrapper-'+car.id"
>
</div>
<div class="main-info">
    <h3>{{ car.makeName }} {{ car.modelName }} {{ car.year }} {{ car.trim }} {{ car.bodyTypeName }}</h3>
    <div class="center-block">
        <div class="tags-list">
            <span v-show="car.featuresNames.length > 0">{{ car.featuresNames.length > 0 ? car.featuresNames[0] : ''}}</span>
            <span v-show="car.featuresNames.length > 1">{{ car.featuresNames.length > 1 ? car.featuresNames[1] : ''}}</span>
            <span class="see-more" v-show="car.featuresNames.length > 2">+{{ car.featuresNames.length-2 }}</span>
        </div>
        <div class="main-chars">
            <div class="single-char" v-show="car.mileage > 0">
                <svg class="icon" width="18" height="19">
                    <use xlink:href="/images/sprites/sprites.svg#icon-mileage"></use>
                </svg>
                <span>{{ car.mileageName }}</span>
            </div>
            <div class="single-char">
                <svg class="icon" width="18" height="19">
                    <use xlink:href="/images/sprites/sprites.svg#icon-fuel"></use>
                </svg>
                <span>{{ car.fuelTypeName }}</span>
            </div>
            <div class="single-char">
                <svg class="icon" width="18" height="19">
                    <use xlink:href="/images/sprites/sprites.svg#icon-transmission"></use>
                </svg>
                <span>{{ car.transmissionName }}</i></span>
            </div>
        </div>
    </div>
    <div class="price-block">
        <span>{{ car.engineType }}</span>
        <h3>{{ car.price }}</h3>
        <div class="link">
            <?= $translator->translate("View Details") ?>
            <svg class="icon" width="18" height="19">
                <use xlink:href="/images/sprites/sprites.svg#icon-arrow-square"></use>
            </svg>
        </div>
    </div>
</div>
