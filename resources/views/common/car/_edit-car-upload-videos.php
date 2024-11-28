<div class="upload-wrapper">
    <div class="uploaded-photos app-medias-container" v-show="car.videosAll" style="display:none">
        <div
            class="single-uploaded-photo"
            v-for="(media, index) in car.videosAll"
            :key="media.id"
        >
            <div
                class="img-wrapper app-image-wrapper"
                :class="{'loading': media.isProcessed}"
                draggable
                @dragstart="startDragMedia($event, media, index, 'video')"
                @drop="onDropMedia($event, media, index, 'video')"
                @dragover.prevent
                @dragenter.prevent
            >
                <video
                    controls
                    :id="'mediaWrapper' + media.id"
                    :src="media.baseUrl"
                    :poster="media.videoPreviewUrl"
                    draggable="true"
                >
                </video>
            </div>

            <span v-if="media.status!='active'"><?= $translator->translate("Video is processing...") ?></span>

            <button
                type="button"
                class="delete-image"
                @click.prevent="deleteMedia($event, media.id, media.carId)"
                @focus = "setCurrentMobileStepOnFocus($event)"
                title="<?= $translator->translate("Delete video") ?>"
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
            <?= $translator->translate("Upload videos") ?>
        </a>
        <input
            type="file"
            id="videos"
            name="videos[]"
            multiple="true"
            :accept="this.acceptAllowedMimeTypesVideos"
            @change="($event) => handleUploadFiles($event, this.allowedMimeTypesVideos, this.messageAllowedMimeTypesVideos)"
            @focus = "setCurrentMobileStepOnFocus($event)"
        >
    </div>
</div>
