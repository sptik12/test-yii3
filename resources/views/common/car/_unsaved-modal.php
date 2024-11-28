<div class="modal fade unsaved-modal" id="unsaved-modal" data-bs-keyboard="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= $translator->translate("Close") ?>"></button>
            </div>
            <div class="modal-body">
                <h2>You have unsaved changes on this page.</h2>
                <p>Do you want to continue without saving?</p>
                <div class="modal-buttons">
                    <button class="btn btn-primary btn-big">Сontinue</button>
                    <button class="btn btn-outline btn-big">Return to edit</button>
                </div>
            </div>
        </div>
    </div>
</div>
