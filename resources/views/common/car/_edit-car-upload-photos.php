<div class="upload-wrapper">
    <div class="uploaded-photos app-medias-container" v-show="car.images" style="display:none">
        <div
            class="single-uploaded-photo"
            v-for="(media, index) in car.images"
            :key="media.id"
        >
            <div
                class="img-wrapper app-image-wrapper"
                :class="{'loading': media.isProcessed}"
                draggable
                @dragstart="startDragMedia($event, media, index, 'image')"
                @drop="onDropMedia($event, media, index, 'image')"
                @dragover.prevent
                @dragenter.prevent
            >
                <img
                    :id="'mediaWrapper' + media.id"
                    :src="media.catalogUrl"
                >
            </div>

            <span v-if="media.isMain"><?= $translator->translate("Main media") ?></span>
            <a
                v-else
                href="#"  @click.prevent="setMediaMain($event, media.id, media.carId)"
                @focus = "setCurrentMobileStepOnFocus($event)"
            >
                <?= $translator->translate("Make it a main media") ?>
            </a>

            <button
                type="button"
                class="delete-image"
                @click.prevent="deleteMedia($event, media.id, media.carId)"
                @focus = "setCurrentMobileStepOnFocus($event)"
                title="<?= $translator->translate("Delete image") ?>"
            >
                <svg class="icon" width="14" height="14">
                    <use xlink:href="/images/sprites/sprites.svg#icon-cross"></use>
                </svg>
            </button>
        </div>
    </div>
    <div class="upload-link app-upload-link">
        <a href="#" @focus = "setCurrentMobileStepOnFocus($event)">
            <svg class="icon" width="24" height="24">
                <use xlink:href="/images/sprites/sprites.svg#icon-image"></use>
            </svg>
            <?= $translator->translate("Upload images") ?>
        </a>
        <input
            type="file"
            id="photos"
            name="photos[]"
            multiple="true"
            :accept="this.acceptAllowedMimeTypesImages"
            @change="($event) => handleUploadFiles($event, this.allowedMimeTypesImages, this.messageAllowedMimeTypesImages)"
            @focus = "setCurrentMobileStepOnFocus($event)"
        >
    </div>
</div>
