<section class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div>
            <p class="eyebrow">Community collection</p>
            <h1 class="display-5 mb-1">Photo Gallery</h1>
            <p class="text-secondary mb-0">Choose the layout that fits how you want to browse.</p>
        </div>
        <?php if (isLoggedIn()): ?>
            <a class="btn btn-primary" href="<?= e(url('/photos/create')) ?>">Upload a photo</a>
        <?php endif; ?>
    </div>

    <?php if ($photos === []): ?>
        <div class="empty-state text-center p-5">
            <h2 class="h4">No photos have been shared yet.</h2>
            <p class="text-secondary mb-3">The gallery will appear here after the first upload.</p>
            <?php if (isLoggedIn()): ?>
                <a class="btn btn-primary" href="<?= e(url('/photos/create')) ?>">Upload the first photo</a>
            <?php else: ?>
                <a class="btn btn-primary" href="<?= e(url('/login')) ?>">Login to upload</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="gallery-toolbar d-flex flex-wrap align-items-center gap-2 mb-4" role="group" aria-label="Gallery display style">
            <span class="small fw-semibold me-1">Display:</span>
            <button class="btn btn-sm btn-outline-secondary gallery-layout-button" type="button" data-layout="three" aria-pressed="true">3 columns</button>
            <button class="btn btn-sm btn-outline-secondary gallery-layout-button" type="button" data-layout="four" aria-pressed="false">4 columns</button>
            <button class="btn btn-sm btn-outline-secondary gallery-layout-button" type="button" data-layout="list" aria-pressed="false">List</button>
        </div>

        <div id="galleryGrid" class="gallery-grid layout-three">
            <?php foreach ($photos as $photo): ?>
                <article class="photo-card gallery-item">
                    <a class="gallery-image-link" href="<?= e(url('/photo/' . $photo['id'])) ?>">
                        <img class="photo-card-image" src="<?= e(asset('/images/uploads/' . rawurlencode((string) $photo['file_name']))) ?>" alt="<?= e($photo['title']) ?>" loading="lazy">
                    </a>
                    <div class="gallery-card-body p-3">
                        <div>
                            <h2 class="h5 mb-1"><a class="text-decoration-none text-dark" href="<?= e(url('/photo/' . $photo['id'])) ?>"><?= e($photo['title']) ?></a></h2>
                            <p class="text-secondary small mb-2">By <?= e($photo['first_name'] . ' ' . $photo['last_name']) ?></p>
                            <?php if (!empty($photo['description'])): ?>
                                <p class="gallery-description mb-2"><?= e($photo['description']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-3 text-secondary small">
                            <span><?= e(formatDate((string) $photo['date_time'])) ?></span>
                            <span><?= e($photo['comment_count']) ?> comments</span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
