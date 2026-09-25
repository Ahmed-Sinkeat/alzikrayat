<section class="container py-5">
    <div class="auth-shell mx-auto">
        <div class="mb-4">
            <p class="eyebrow">Welcome back</p>
            <h1 class="h2">Login to Alzikrayat</h1>
            <p class="text-secondary mb-0">Continue sharing and discussing memorable photographs.</p>
        </div>

        <?php if ($lastLogin !== null): ?>
            <div class="alert alert-info" role="status">
                Last login from this computer was <?= e($lastLogin) ?>.
            </div>
        <?php endif; ?>

        <?php if (isset($errors['credentials'])): ?>
            <div class="alert alert-danger" role="alert"><?= e($errors['credentials']) ?></div>
        <?php endif; ?>

        <form action="<?= e(url('/login')) ?>" method="post" data-validate="login">
            <?= csrfField() ?>
            <div class="mb-3">
                <label class="form-label" for="email">Email address</label>
                <input class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" type="email" maxlength="100" autocomplete="email" value="<?= e($input['email']) ?>" required>
                <div class="invalid-feedback"><?= e($errors['email'] ?? 'Enter a valid email address.') ?></div>
            </div>
            <div class="mb-4">
                <label class="form-label" for="password">Password</label>
                <input class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" type="password" maxlength="255" autocomplete="current-password" required>
                <div class="invalid-feedback"><?= e($errors['password'] ?? 'Enter your password.') ?></div>
            </div>
            <button class="btn btn-primary w-100" type="submit">Login</button>
        </form>

        <p class="text-center text-secondary mt-4 mb-0">No account yet? <a href="<?= e(url('/register')) ?>">Create one</a>.</p>
    </div>
</section>
