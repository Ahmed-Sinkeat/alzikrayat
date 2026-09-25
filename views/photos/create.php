<section class="container py-5">
    <div class="form-shell mx-auto">
        <div class="mb-4">
            <p class="eyebrow">Add to the gallery</p>
            <h1 class="h2">Upload a photo</h1>
            <p class="text-secondary mb-0">JPEG, PNG, GIF, or WebP. Maximum file size: 5 MB.</p>
        </div>

        <form action="<?= e(url('/photo/store')) ?>" method="post" enctype="multipart/form-data" data-validate="photo">
            <?= csrfField() ?>
            <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
            <div class="mb-3">
                <label class="form-label" for="title">Photo title *</label>
                <input class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" id="title" name="title" type="text" minlength="1" maxlength="200" value="<?= e($input['title']) ?>" required>
                <div class="invalid-feedback"><?= e($errors['title'] ?? 'Enter a title no longer than 200 characters.') ?></div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" id="description" name="description" rows="5" maxlength="5000"><?= e($input['description']) ?></textarea>
                <div class="invalid-feedback"><?= e($errors['description'] ?? 'Use no more than 5000 characters.') ?></div>
            </div>
            <div class="mb-4">
                <label class="form-label" for="photo">Image file *</label>
                <input class="form-control <?= isset($errors['photo']) ? 'is-invalid' : '' ?>" id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/gif,image/webp" aria-describedby="photoHelp" required>
                <div id="photoHelp" class="form-text">The server checks the real file type before saving it.</div>
                <div class="invalid-feedback"><?= e($errors['photo'] ?? 'Choose a supported image no larger than 5 MB.') ?></div>
            </div>
            <div class="upload-preview mb-4 d-none" id="uploadPreview" aria-live="polite">
                <img id="uploadPreviewImage" alt="Preview of selected upload">
            </div>
            <div class="d-flex flex-column-reverse flex-sm-row justify-content-end gap-2">
                <a class="btn btn-outline-secondary" href="<?= e(url('/photos')) ?>">Cancel</a>
                <button class="btn btn-primary" type="submit">Upload photo</button>
            </div>
        </form>
    </div>
</section>
