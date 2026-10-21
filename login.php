<?php
/**
 * Login page - checks the email + password and starts the session.
 *
 * Only patients are allowed into the patient area here. Admin and doctor
 * accounts belong to the other modules of the project (handled by the
 * team), so they are not logged in on this page.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: patient/my-appointments.php');
    exit;
}

$db    = tryGetDB();
$errors = [];

$old = [
    'email'    => trim((string) ($_POST['email'] ?? '')),
    'password' => (string) ($_POST['password'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if ($old['password'] === '') {
        $errors['password'] = 'Please enter your password.';
    }

    if (!$errors) {
        if (!$db) {
            $errors['email'] = 'The database is unavailable, so you cannot sign in right now.';
        } else {
            $sql = 'SELECT id, name, email, password_hash, role, is_active
                    FROM users WHERE email = :email LIMIT 1';
            $statement = $db->prepare($sql);
            $statement->execute([':email' => $old['email']]);
            $account = $statement->fetch();

            // password_verify() checks the typed password against the bcrypt hash
            if (!$account || !password_verify($old['password'], $account['password_hash'])) {
                $errors['email'] = 'Incorrect email or password.';
            } elseif ((int) $account['is_active'] === 0) {
                $errors['email'] = 'This account has been deactivated.';
            } elseif ($account['role'] !== 'patient') {
                $errors['email'] = 'This account belongs to the '
                    . e($account['role']) . ' area of the project.';
            } else {
                loginUser($account);

                // Send the user back to the page they originally wanted
                $redirect = (string) ($_POST['redirect'] ?? '');
                if ($redirect === '' || $redirect[0] !== '/' || str_starts_with($redirect, '//')) {
                    $redirect = '/patient/my-appointments.php';
                }

                flashSet('success', 'Welcome back, ' . $account['name'] . '!');
                header('Location: ' . $redirect);
                exit;
            }
        }
    }
}

$pageTitle = 'Login | MediBook';
$baseUrl   = '';
$activeNav = 'login';

require __DIR__ . '/includes/header.php';

// Where guests were sent from (set by requireLogin())
$redirectTo = (string) ($_GET['redirect'] ?? '');
if ($redirectTo === '' || $redirectTo[0] !== '/' || str_starts_with($redirectTo, '//')) {
    $redirectTo = '/patient/my-appointments.php';
}
?>

<section class="page-banner">
    <div class="container">
        <span class="section-label">Patient account</span>
        <h1>Welcome Back</h1>
        <p>Sign in to book appointments and manage your visits.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="auth-card">
            <h1>Login</h1>
            <p class="auth-subtitle">Sign in with your email and password.</p>

            <form method="post" action="login.php" data-validate>
                <input type="hidden" name="redirect" value="<?= e($redirectTo) ?>">

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
                    <input type="password" class="form-control" id="password"
                           name="password"
                           data-label="Password" data-rules="required"
                           autocomplete="current-password">
                    <?php if (isset($errors['password'])): ?>
                        <span class="field-error"><?= e($errors['password']) ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn-primary-custom w-100">
                    Sign In →
                </button>
            </form>

            <div class="form-text mt-3 text-center">
                Demo database account (seed.sql):<br>
                <strong>rahul@example.test</strong> / <strong>password</strong>
            </div>

            <p class="auth-links">
                New patient? <a href="register.php">Create an account</a>
            </p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
