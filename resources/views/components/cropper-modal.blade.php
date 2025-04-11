<div class="modal fade" id="cropperModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crop Image</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center">
                <div class="img-container">
                    <img id="cropper-image" style="max-width: 100%;" alt="" src="">
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-secondary btn-sm" id="rotateLeft"><i class="fa fa-undo"></i></button>
                    <button type="button" class="btn btn-secondary btn-sm" id="rotateRight"><i class="fa fa-redo"></i></button>
                    <button type="button" class="btn btn-secondary btn-sm" id="flipHorizontal"><i class="fa fa-arrows-alt-h"></i></button>
                    <button type="button" class="btn btn-secondary btn-sm" id="flipVertical"><i class="fa fa-arrows-alt-v"></i></button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" id="cropImage">Crop</button>
            </div>
        </div>
    </div>
</div>
