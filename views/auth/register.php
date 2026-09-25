<section class="container py-5">
    <div class="auth-shell auth-shell-wide mx-auto">
        <div class="mb-4">
            <p class="eyebrow">Join the community</p>
            <h1 class="h2">Create your account</h1>
            <p class="text-secondary mb-0">Required fields are marked with an asterisk.</p>
        </div>

        <form action="<?= e(url('/register')) ?>" method="post" data-validate="register">
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="first_name">First name *</label>
                    <input class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" id="first_name" name="first_name" type="text" maxlength="50" pattern="[\p{L}]+" autocomplete="given-name" value="<?= e($input['first_name']) ?>" required>
                    <div class="invalid-feedback"><?= e($errors['first_name'] ?? 'Use letters only, up to 50 characters.') ?></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="last_name">Last name *</label>
                    <input class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" id="last_name" name="last_name" type="text" maxlength="50" pattern="[\p{L}]+" autocomplete="family-name" value="<?= e($input['last_name']) ?>" required>
                    <div class="invalid-feedback"><?= e($errors['last_name'] ?? 'Use letters only, up to 50 characters.') ?></div>
                </div>
                <div class="col-12">
                    <label class="form-label" for="email">Email address *</label>
                    <input class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" type="email" maxlength="100" autocomplete="email" value="<?= e($input['email']) ?>" required>
                    <div class="invalid-feedback"><?= e($errors['email'] ?? 'Enter a unique, valid email address.') ?></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password">Password *</label>
                    <input class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" type="password" minlength="8" maxlength="255" autocomplete="new-password" aria-describedby="passwordHelp" required>
                    <div id="passwordHelp" class="form-text">Use at least 8 characters.</div>
                    <div class="invalid-feedback"><?= e($errors['password'] ?? 'Use between 8 and 255 characters.') ?></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password_confirmation">Confirm password *</label>
                    <input class="form-control <?= isset($errors['password_confirmation']) ? 'is-invalid' : '' ?>" id="password_confirmation" name="password_confirmation" type="password" minlength="8" maxlength="255" autocomplete="new-password" required>
                    <div class="invalid-feedback"><?= e($errors['password_confirmation'] ?? 'Enter the same password again.') ?></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="location">Location</label>
                    <input class="form-control <?= isset($errors['location']) ? 'is-invalid' : '' ?>" id="location" name="location" type="text" maxlength="100" autocomplete="address-level2" value="<?= e($input['location']) ?>">
                    <div class="invalid-feedback"><?= e($errors['location'] ?? 'Use no more than 100 characters.') ?></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="occupation">Occupation</label>
                    <input class="form-control <?= isset($errors['occupation']) ? 'is-invalid' : '' ?>" id="occupation" name="occupation" type="text" maxlength="100" autocomplete="organization-title" value="<?= e($input['occupation']) ?>">
                    <div class="invalid-feedback"><?= e($errors['occupation'] ?? 'Use no more than 100 characters.') ?></div>
                </div>
                <div class="col-12">
                    <label class="form-label" for="description">Short biography</label>
                    <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" id="description" name="description" rows="4" maxlength="2000"><?= e($input['description']) ?></textarea>
                    <div class="invalid-feedback"><?= e($errors['description'] ?? 'Use no more than 2000 characters.') ?></div>
                </div>
                <div class="col-12 mt-4">
                    <button class="btn btn-primary w-100" type="submit">Create account</button>
                </div>
            </div>
        </form>

        <p class="text-center text-secondary mt-4 mb-0">Already registered? <a href="<?= e(url('/login')) ?>">Login</a>.</p>
    </div>
</section>
