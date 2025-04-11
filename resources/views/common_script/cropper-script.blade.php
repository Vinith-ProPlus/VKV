<script>
    $(document).ready(function () {
        let cropper;
        let selectedFile;

        function updateCropperPreview(url) {
            $("#image-preview").removeClass("d-none").attr("src", url);
            $("#image-dropzone i, #image-dropzone p").hide();
        }

        function resetCropperPreview() {
            $("#image-preview").addClass("d-none").attr("src", "");
            $("#image-dropzone i, #image-dropzone p").show(); // Show text & icon again
        }

        // Click to open file input
        $("#image-dropzone").click(function () {
            $("#image-input").click();
        });

        // Drag & drop functionality
        $("#image-dropzone").on("dragover", function (e) {
            e.preventDefault();
            $(this).css("border-color", "#007bff");
        }).on("dragleave", function () {
            $(this).css("border-color", "#ccc");
        }).on("drop", function (e) {
            e.preventDefault();
            let files = e.originalEvent.dataTransfer.files;
            if (files.length > 0) {
                $("#image-input")[0].files = files;
                handleFileSelect(files[0]);
            }
        });

        function handleFileSelect(file) {
            if (!file) return;

            selectedFile = file;
            let reader = new FileReader();
            reader.onload = function (e) {
                $("#cropper-image").attr("src", e.target.result);
                $("#cropperModal").modal("show");
            };
            reader.readAsDataURL(file);
        }

        // When modal opens, initialize cropper
        $("#cropperModal").on("shown.bs.modal", function () {
            let image = document.getElementById("cropper-image");
            if (cropper) cropper.destroy(); // Ensure cropper resets
            cropper = new Cropper(image, {
                aspectRatio: 1,
                viewMode: 1,
            });
        });

        // Rotate and flip actions
        $("#rotateLeft").click(() => cropper.rotate(-90));
        $("#rotateRight").click(() => cropper.rotate(90));
        $("#flipHorizontal").click(() => cropper.scaleX(-cropper.getData().scaleX || -1));
        $("#flipVertical").click(() => cropper.scaleY(-cropper.getData().scaleY || -1));

        // Upload new image inside modal
        $("#uploadNewImage").click(function () {
            $("#image-input").val(""); // Clear previous selection
            $("#image-input").trigger("click"); // Open file selection
        });

        // Ensure the new file gets handled properly
        $("#image-input").off("change").on("change", function (event) {
            let files = event.target.files;
            if (files && files.length > 0) {
                handleFileSelect(files[0]);
            }
        });

        $("#cropImage").click(function () {
            if (cropper) {
                cropper.getCroppedCanvas().toBlob(function (blob) {
                    let croppedFile = new File([blob], selectedFile.name, { type: "image/jpeg" });

                    let dataTransfer = new DataTransfer();
                    dataTransfer.items.add(croppedFile);
                    $("#image-input")[0].files = dataTransfer.files;

                    let objectURL = URL.createObjectURL(croppedFile);
                    updateCropperPreview(objectURL);

                    $("#cropperModal").modal("hide");
                }, "image/jpeg");
            }
        });

        // Destroy cropper on modal close
        $("#cropperModal").on("hidden.bs.modal", function () {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
        });

        // Reset image when clicking outside
        $("#image-dropzone").dblclick(function () {
            resetCropperPreview();
            $("#image-input").val(""); // Reset file input
        });
    });
</script>
