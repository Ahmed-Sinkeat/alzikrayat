<?php
$pageTitle = $pageTitle ?? 'Alzikrayat';
$flash = takeFlash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Alzikrayat is a simple community photo-sharing application.">
    <title><?= e($pageTitle) ?> | Alzikrayat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(asset('/css/app.css')) ?>" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
<a class="skip-link" href="#main-content">Skip to main content</a>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar sticky-top" aria-label="Main navigation">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= e(url('/')) ?>">
            <span class="brand-mark" aria-hidden="true"></span>
            <span>Alzikrayat</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavigation" aria-controls="mainNavigation" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavigation">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= e(url('/')) ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('/photos')) ?>">Gallery</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('/about')) ?>">About Us</a></li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('/photos/create')) ?>">Upload Photo</a></li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-lg-center gap-2 flex-column flex-lg-row">
                <?php if ($currentUser = currentUser()): ?>
                    <span class="navbar-text text-white me-lg-2">Hi <?= e($currentUser['first_name']) ?></span>
                    <form action="<?= e(url('/logout')) ?>" method="post">
                        <?= csrfField() ?>
                        <button class="btn btn-light btn-sm" type="submit">Logout</button>
                    </form>
                <?php else: ?>
                    <a class="btn btn-light btn-sm" href="<?= e(url('/login')) ?>">Please Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<main class="flex-grow-1" id="main-content" tabindex="-1">
    <?php if ($flash !== null): ?>
        <div class="container pt-3">
            <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
                <?= e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>
