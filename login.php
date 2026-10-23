<?php
/**
 * Role-aware login page.
 *
 * The account is looked up by email, the password is checked with
 * password_verify(), and only then is the selected role compared with the
 * role stored in the database. A user cannot choose a role they do not have.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

redirectLoggedInUser();

$db     = tryGetDB();
$errors = [];

$selectedRole = (string) ($_POST['role'] ?? $_GET['role'] ?? 'patient');
if (!in_array($selectedRole, APP_ROLES, true)) {
    $selectedRole = 'patient';
}

$old = [
    'email' => trim((string) ($_POST['email'] ?? '')),
];

$redirectTo = safeRedirectPath(
    $_POST['redirect'] ?? $_GET['redirect'] ?? '',
    ''
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfIsValid()) {
        $errors['form'] = 'Your session expired. Please try signing in again.';
    }

    if (!in_array($_POST['role'] ?? '', APP_ROLES, true)) {
        $errors['role'] = 'Please choose Patient, Doctor or Admin.';
    }

    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    $password = (string) ($_POST['password'] ?? '');
    if ($password === '') {
        $errors['password'] = 'Please enter your password.';
    }

    if (!$errors) {
        if (!$db) {
            $errors['form'] = 'The database is unavailable, so you cannot sign in right now.';
        } else {
            $sql = 'SELECT id, name, email, password_hash, role, is_active
                    FROM users
                    WHERE email = :email
                    LIMIT 1';
            $statement = $db->prepare($sql);
            $statement->execute([':email' => $old['email']]);
            $account = $statement->fetch();

            /*
             * Always run password_verify(), even when no account was found.
             * This keeps the response time similar and avoids leaking which
             * email addresses exist.
             */
            $dummyHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
            $hash      = $account['password_hash'] ?? $dummyHash;
            $passwordOk = password_verify($password, $hash);

            if (!$account || !$passwordOk) {
                $errors['form'] = 'Incorrect email or password.';
            } elseif ((int) $account['is_active'] === 0) {
                $errors['form'] = 'This account has been deactivated. Please contact the hospital.';
            } elseif ($account['role'] !== $selectedRole) {
                $errors['form'] = 'The selected role does not match this account. '
                    . 'Please choose the correct role and try again.';
            } else {
                loginUser($account);

                $fallback = dashboardPath($account['role']);
                if ($redirectTo === '' || !redirectMatchesRole($redirectTo, $account['role'])) {
                    $redirectTo = $fallback;
                }

                flashSet('success', 'Welcome back, ' . $account['name'] . '!');
                header('Location: ' . $redirectTo);
                exit;
            }
        }
    }
}

$pageTitle = 'Sign In | MediBook';
$baseUrl   = '';
$activeNav = 'login';
$extraCss  = ['assets/css/auth.css'];
$extraJs   = ['assets/js/login.js'];

require __DIR__ . '/includes/header.php';
?>

<section class="login-section">
    <div class="container">
        <div class="row g-5 align-items-center justify-content-center">

            <div class="col-lg-5 d-none d-lg-block">
                <div class="login-intro">
                    <span class="section-label">MediBook access</span>
                    <h1>One sign-in.<br>Three dedicated workspaces.</h1>
                    <p>
                        Patients book and track visits, doctors manage their
                        day, and administrators keep the hospital running.
                    </p>

                    <div class="login-role-list">
                        <div class="login-role-item role-patient">
                            <span>♥</span>
                            <div>
                                <strong>Patient</strong>
                                <small>Book appointments and view your history.</small>
                            </div>
                        </div>
                        <div class="login-role-item role-doctor">
                            <span>✚</span>
                            <div>
                                <strong>Doctor</strong>
                                <small>Review today's schedule and patient details.</small>
                            </div>
                        </div>
                        <div class="login-role-item role-admin">
                            <span>▦</span>
                            <div>
                                <strong>Admin</strong>
                                <small>Manage doctors, patients and the hospital schedule.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-xl-5">
                <div class="auth-card auth-card-wide">
                    <span class="section-label">Welcome back</span>
                    <h1>Sign in to MediBook</h1>
                    <p class="auth-subtitle">
                        Choose your account type, then enter your hospital credentials.
                    </p>

                    <?php if (isset($errors['form'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= e($errors['form']) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="login.php" data-validate>
                        <?= csrfField() ?>
                        <input type="hidden" name="redirect" value="<?= e($redirectTo) ?>">

                        <fieldset class="role-picker">
                            <legend>Sign in as</legend>
                            <div class="role-options">
                                <?php foreach (APP_ROLES as $role): ?>
                                <label class="role-option role-<?= e($role) ?>">
                                    <input type="radio" name="role" value="<?= e($role) ?>"
                                        <?= $selectedRole === $role ? 'checked' : '' ?>>
                                    <span class="role-option-card">
                                        <span class="role-option-icon">
                                            <?= $role === 'patient' ? '♥' : ($role === 'doctor' ? '✚' : '▦') ?>
                                        </span>
                                        <strong><?= e(roleLabel($role)) ?></strong>
                                    </span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                            <?php if (isset($errors['role'])): ?>
                                <span class="field-error"><?= e($errors['role']) ?></span>
                            <?php endif; ?>
                        </fieldset>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= e($old['email']) ?>"
                                   data-label="Email address" data-rules="required|email"
                                   autocomplete="email">
                            <?php if (isset($errors['email'])): ?>
                                <span class="field-error"><?= e($errors['email']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="password-field">
                                <input type="password" class="form-control" id="password"
                                       name="password"
                                       data-label="Password" data-rules="required"
                                       autocomplete="current-password">
                                <button type="button" class="password-toggle"
                                        data-password-toggle="password"
                                        aria-label="Show password">Show</button>
                            </div>
                            <?php if (isset($errors['password'])): ?>
                                <span class="field-error"><?= e($errors['password']) ?></span>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn-primary-custom w-100">
                            Sign In Securely →
                        </button>
                    </form>

                    <p class="auth-links">
                        New patient? <a href="register.php">Create an account</a><br>
                        <span class="auth-note">Doctor and admin accounts are created by the hospital.</span>
                    </p>

                    <details class="demo-accounts">
                        <summary>Demo accounts for this project</summary>
                        <div class="demo-account-grid">
                            <div><strong>Patient</strong><span>rahul@example.test</span></div>
                            <div><strong>Doctor</strong><span>aarav@medibook.test</span></div>
                            <div><strong>Admin</strong><span>admin@medibook.test</span></div>
                            <div class="demo-password"><strong>Password</strong><span>password</span></div>
                        </div>
                    </details>
                </div>
            </div>

        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
