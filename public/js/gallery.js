"use strict";

function applyGalleryLayout(gallery, buttons, layout) {
    gallery.classList.remove("layout-three", "layout-four", "layout-list");
    gallery.classList.add(`layout-${layout}`);

    buttons.forEach((button) => {
        button.setAttribute("aria-pressed", String(button.dataset.layout === layout));
    });
}

const gallery = document.querySelector("#galleryGrid");
const galleryButtons = document.querySelectorAll(".gallery-layout-button");

if (gallery && galleryButtons.length > 0) {
    galleryButtons.forEach((button) => {
        button.addEventListener("click", () => {
            applyGalleryLayout(gallery, galleryButtons, button.dataset.layout);
        });
    });
}

const uploadField = document.querySelector("#photo");
const uploadPreview = document.querySelector("#uploadPreview");
const uploadPreviewImage = document.querySelector("#uploadPreviewImage");
let previewObjectUrl = null;

if (uploadField && uploadPreview && uploadPreviewImage) {
    uploadField.addEventListener("change", () => {
        if (previewObjectUrl !== null) {
            URL.revokeObjectURL(previewObjectUrl);
            previewObjectUrl = null;
        }

        const selectedFile = uploadField.files[0];
        if (!selectedFile || !selectedFile.type.startsWith("image/")) {
            uploadPreview.classList.add("d-none");
            uploadPreviewImage.removeAttribute("src");
            return;
        }

        previewObjectUrl = URL.createObjectURL(selectedFile);
        uploadPreviewImage.src = previewObjectUrl;
        uploadPreview.classList.remove("d-none");
    });
}

document.querySelectorAll("form[data-confirm]").forEach((form) => {
    form.addEventListener("submit", (event) => {
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

const detailPhoto = document.querySelector("#detailPhoto");
const filterButtons = document.querySelectorAll(".photo-filter-button");

if (detailPhoto && filterButtons.length > 0) {
    filterButtons.forEach((button) => {
        button.addEventListener("click", () => {
            detailPhoto.classList.remove("filter-warm", "filter-mono");
            if (button.dataset.filter !== "original") {
                detailPhoto.classList.add(`filter-${button.dataset.filter}`);
            }

            filterButtons.forEach((filterButton) => {
                filterButton.setAttribute("aria-pressed", String(filterButton === button));
            });
        });
    });
}
