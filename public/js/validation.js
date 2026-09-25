"use strict";

function setFieldError(field, message) {
    field.setCustomValidity(message);
    const feedback = field.parentElement?.querySelector(".invalid-feedback");
    if (feedback && message) {
        feedback.textContent = message;
    }
}

function validateRegistration(form) {
    const firstName = form.elements.namedItem("first_name");
    const lastName = form.elements.namedItem("last_name");
    const password = form.elements.namedItem("password");
    const confirmation = form.elements.namedItem("password_confirmation");
    const lettersOnly = /^\p{L}+$/u;

    setFieldError(firstName, lettersOnly.test(firstName.value.trim()) ? "" : "First name must contain letters only.");
    setFieldError(lastName, lettersOnly.test(lastName.value.trim()) ? "" : "Last name must contain letters only.");
    setFieldError(password, password.value.length >= 8 ? "" : "Password must contain at least 8 characters.");
    setFieldError(confirmation, confirmation.value === password.value ? "" : "Password confirmation must match.");
}

function validateLogin(form) {
    const email = form.elements.namedItem("email");
    const password = form.elements.namedItem("password");
    const simpleEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    setFieldError(email, simpleEmail.test(email.value.trim()) ? "" : "Enter a valid email address.");
    setFieldError(password, password.value === "" ? "Password is required." : "");
}

function validatePhotoUpload(form) {
    const title = form.elements.namedItem("title");
    const photoField = form.elements.namedItem("photo");
    const selectedFile = photoField.files[0];
    const allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];
    let message = "";

    setFieldError(title, title.value.trim() === "" ? "Photo title is required." : "");

    if (!selectedFile) {
        message = "Choose an image to upload.";
    } else if (!allowedTypes.includes(selectedFile.type)) {
        message = "Choose a JPEG, PNG, GIF, or WebP image.";
    } else if (selectedFile.size > 5 * 1024 * 1024) {
        message = "The image must not exceed 5 MB.";
    }

    setFieldError(photoField, message);
}

function validateComment(form) {
    const comment = form.elements.namedItem("comment");
    setFieldError(comment, comment.value.trim() === "" ? "Comment text is required." : "");
}

function validateForm(event) {
    const form = event.currentTarget;

    if (form.dataset.validate === "login") {
        validateLogin(form);
    } else if (form.dataset.validate === "register") {
        validateRegistration(form);
    } else if (form.dataset.validate === "photo") {
        validatePhotoUpload(form);
    } else if (form.dataset.validate === "comment") {
        validateComment(form);
    }

    if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
        form.querySelector(":invalid")?.focus();
    }

    form.classList.add("was-validated");
}

document.querySelectorAll("form[data-validate]").forEach((form) => {
    form.addEventListener("submit", validateForm);
});
