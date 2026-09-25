<section class="hero-section py-5">
    <div class="container py-lg-4">
        <div class="row align-items-center gx-4 gy-5">
            <div class="col-lg-6">
                <p class="eyebrow">Photos become shared stories</p>
                <h1 class="display-4 fw-semibold">Keep the moments that matter close.</h1>
                <p class="lead text-secondary mt-3">Alzikrayat is a simple place to upload meaningful photographs, tell their stories, and join the conversation.</p>
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a class="btn btn-primary btn-lg" href="<?= e(url('/photos')) ?>">Explore the gallery</a>
                    <?php if (isLoggedIn()): ?>
                        <a class="btn btn-outline-primary btn-lg" href="<?= e(url('/photos/create')) ?>">Share a photo</a>
                    <?php else: ?>
                        <a class="btn btn-outline-primary btn-lg" href="<?= e(url('/register')) ?>">Create an account</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <img class="img-fluid hero-art" src="<?= e(asset('/images/hero-collage.svg')) ?>" alt="An illustrated arrangement of framed photographs" width="720" height="500">
            </div>
        </div>
    </div>
</section>

<section class="container stats-section" aria-labelledby="communityStats">
    <div class="surface-card p-4 p-lg-5">
        <p class="eyebrow" id="communityStats">Our growing collection</p>
        <div class="row g-4 text-center">
            <div class="col-4">
                <strong class="stat-number d-block"><?= e($statistics['users']) ?></strong>
                <span class="text-secondary">Members</span>
            </div>
            <div class="col-4 border-start border-end">
                <strong class="stat-number d-block"><?= e($statistics['photos']) ?></strong>
                <span class="text-secondary">Photos</span>
            </div>
            <div class="col-4">
                <strong class="stat-number d-block"><?= e($statistics['comments']) ?></strong>
                <span class="text-secondary">Comments</span>
            </div>
        </div>
    </div>
</section>

<section class="container py-5 my-lg-4" aria-labelledby="latestHeading">
    <div class="d-flex justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="eyebrow">From the community</p>
            <h2 id="latestHeading" class="h1 mb-0">Latest memories</h2>
        </div>
        <a href="<?= e(url('/photos')) ?>">View all photos</a>
    </div>

    <?php if ($latestPhotos === []): ?>
        <div class="empty-state text-center p-5">
            <h3 class="h4">The first memory is waiting.</h3>
            <p class="text-secondary">Create an account and begin the gallery.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($latestPhotos as $photo): ?>
                <div class="col-md-4">
                    <article class="photo-card h-100">
                        <a href="<?= e(url('/photo/' . $photo['id'])) ?>">
                            <img class="photo-card-image" src="<?= e(asset('/images/uploads/' . rawurlencode((string) $photo['file_name']))) ?>" alt="<?= e($photo['title']) ?>" loading="lazy">
                        </a>
                        <div class="p-3">
                            <h3 class="h5 mb-1"><a class="stretched-link text-decoration-none text-dark" href="<?= e(url('/photo/' . $photo['id'])) ?>"><?= e($photo['title']) ?></a></h3>
                            <p class="text-secondary small mb-0">By <?= e($photo['first_name'] . ' ' . $photo['last_name']) ?></p>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php if (!isLoggedIn()): ?>
    <section class="container pb-5" aria-labelledby="joinHeading">
        <div class="join-panel p-4 p-lg-5 text-center">
            <p class="eyebrow">Your story belongs here</p>
            <h2 id="joinHeading">Login or register to take part</h2>
            <p class="text-secondary mx-auto mb-4">Visitors can explore every photo. Members can upload memories, write comments, and manage their own photographs.</p>
            <div class="d-flex justify-content-center flex-wrap gap-2">
                <a class="btn btn-primary" href="<?= e(url('/login')) ?>">Login</a>
                <a class="btn btn-outline-primary" href="<?= e(url('/register')) ?>">Register</a>
            </div>
        </div>
    </section>
<?php endif; ?>
