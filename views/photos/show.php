<section class="container py-5">
    <div class="row gx-4 gy-5">
        <div class="col-lg-8">
            <div class="photo-detail-frame">
                <img id="detailPhoto" src="<?= e(asset('/images/uploads/' . rawurlencode((string) $photo['file_name']))) ?>" alt="<?= e($photo['title']) ?>">
            </div>
            <div class="photo-filter-toolbar d-flex flex-wrap align-items-center gap-2 mt-3" role="group" aria-label="Non-destructive photo filter">
                <span class="small fw-semibold me-1">Memory mood:</span>
                <button class="btn btn-sm btn-outline-secondary photo-filter-button" type="button" data-filter="original" aria-pressed="true">Original</button>
                <button class="btn btn-sm btn-outline-secondary photo-filter-button" type="button" data-filter="warm" aria-pressed="false">Warm</button>
                <button class="btn btn-sm btn-outline-secondary photo-filter-button" type="button" data-filter="mono" aria-pressed="false">Monochrome</button>
            </div>
        </div>
        <div class="col-lg-4">
            <p class="eyebrow">Shared memory</p>
            <h1 class="display-6"><?= e($photo['title']) ?></h1>
            <p class="text-secondary">By <?= e($photo['first_name'] . ' ' . $photo['last_name']) ?><br><?= e(formatDate((string) $photo['date_time'])) ?></p>

            <?php if (!empty($photo['description'])): ?>
                <p class="photo-story"><?= nl2br(e($photo['description'])) ?></p>
            <?php endif; ?>

            <?php if (currentUserId() === (int) $photo['user_id']): ?>
                <form class="mt-4" action="<?= e(url('/photo/' . $photo['id'] . '/delete')) ?>" method="post" data-confirm="Delete this photo and all of its comments? This cannot be undone.">
                    <?= csrfField() ?>
                    <button class="btn btn-outline-danger" type="submit">Delete my photo</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mt-5" id="comments">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-baseline mb-3">
                <h2 class="h3 mb-0">Comments</h2>
                <span class="text-secondary"><?= count($comments) ?> total</span>
            </div>

            <?php if ($comments === []): ?>
                <p class="empty-state p-4 text-secondary">No comments yet. Start the conversation.</p>
            <?php else: ?>
                <div class="comment-list">
                    <?php foreach ($comments as $comment): ?>
                        <article class="comment-card">
                            <div class="d-flex justify-content-between gap-3 mb-2">
                                <strong><?= e($comment['first_name'] . ' ' . $comment['last_name']) ?></strong>
                                <time class="text-secondary small" datetime="<?= e($comment['date_time']) ?>"><?= e(formatDate((string) $comment['date_time'])) ?></time>
                            </div>
                            <p class="mb-0"><?= nl2br(e($comment['comment'])) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="surface-card p-4 mt-4" id="comment-form">
                <?php if (isLoggedIn()): ?>
                    <h3 class="h5">Add a comment</h3>
                    <form action="<?= e(url('/photo/' . $photo['id'] . '/comments')) ?>" method="post" data-validate="comment">
                        <?= csrfField() ?>
                        <div class="mb-3">
                            <label class="form-label" for="comment">Your comment</label>
                            <textarea class="form-control <?= isset($commentErrors['comment']) ? 'is-invalid' : '' ?>" id="comment" name="comment" rows="4" maxlength="1000" required><?= e($oldComment) ?></textarea>
                            <div class="invalid-feedback"><?= e($commentErrors['comment'] ?? 'Enter a comment no longer than 1000 characters.') ?></div>
                        </div>
                        <button class="btn btn-primary" type="submit">Post comment</button>
                    </form>
                <?php else: ?>
                    <h3 class="h5">Join the conversation</h3>
                    <p class="text-secondary">You must be logged in to comment on this photo.</p>
                    <a class="btn btn-primary" href="<?= e(url('/login')) ?>">Please Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
