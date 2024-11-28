<?php
use App\Frontend\Helper\Ancillary;

?>

<div class="modal fade app-save-car-search-url-modal" id="add-role" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="mb-0 text-bold" v-show="!carSearchUrl.id"><?= $translator->translate("Save Search") ?></h3>
                <h3 class="mb-0 text-bold" v-show="carSearchUrl.id"><?= $translator->translate("Update Search") ?></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= $translator->translate("Close") ?>"></button>
            </div>
            <div class="modal-body">
                <form action="<?= $urlGenerator->generate('client.addCarSearchUrlAjax') ?>" method="post" ref="carSearchUrlForm">
                    <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                    <div class="form-group w-100 mb-3">
                        <label for="title"><?= $translator->translate("Search Title") ?></label>
                        <input
                            type="text"
                            class="form-control"
                            placeholder="<?= $translator->translate("Search Title") ?>"
                            id="title"
                            name="title"
                            required
                            v-model="carSearchUrl.title"
                        >
                    </div>
                    <div class="filters-list search-tags">
                        <span class="single-filter search-tag" v-for="(displayedFilter, index) in carSearchUrl.filters">
                            {{ displayedFilter.text }}
                            <a href="#" @click.prevent="clearFilterItemInSaveCarSearchUrlModal(index)" class="d-none">
                                <svg class="icon" width="13" height="13">
                                    <use xlink:href="/images/sprites/sprites.svg#icon-clear"></use>
                                </svg>
                            </a>
                        </span>
                    </div>
                    <div class="form-footer d-flex align-items-center justify-content-center gap-3 flex-wrap">
                        <input
                            type="button"
                            class="btn btn-primary"
                            value="<?= $translator->translate("Save Search") ?>"
                            @click.prevent="addCarSearchUrl()"
                            v-show="!carSearchUrl.id"
                        >
                        <input
                            v-show="carSearchUrl.id"
                            type="button"
                            class="btn btn-outline"
                            value="<?= $translator->translate("Update") ?>"
                            @click.prevent="updateCarSearchUrl()"
                        >
                        <input
                            v-show="carSearchUrl.id"
                            type="button"
                            class="btn btn-outline"
                            value="<?= $translator->translate("Remove") ?>"
                            @click.prevent="removeCarSearchUrl()"
                        >
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
