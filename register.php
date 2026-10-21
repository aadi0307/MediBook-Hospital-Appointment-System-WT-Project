<?php
/**
 * Patient registration - inserts a new row into users with role "patient".
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Already logged in? Nothing to do here.
if (isLoggedIn()) {
    header('Location: patient/my-appointments.php');
    exit;
}

$db    = tryGetDB();
$errors = [];

$old = [
    'name'     => trim((string) ($_POST['name'] ?? '')),
    'email'    => trim((string) ($_POST['email'] ?? '')),
    'phone'    => trim((string) ($_POST['phone'] ?? '')),
    'password' => (string) ($_POST['password'] ?? ''),
    'confirm'  => (string) ($_POST['confirm'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Same rules as the JavaScript, checked again on the server
    if ($old['name'] === '' || mb_strlen($old['name']) > 100) {
        $errors['name'] = 'Please enter your full name (max 100 characters).';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if ($old['phone'] !== '' && !preg_match('/^[0-9]{7,15}$/', $old['phone'])) {
        $errors['phone'] = 'Phone number must be 7 to 15 digits.';
    }
    if (strlen($old['password']) < 6) {
        $errors['password'] = 'Password must be at least 6 characters.';
    }
    if ($old['password'] !== $old['confirm']) {
        $errors['confirm'] = 'The two passwords do not match.';
    }

    if (!$errors && $db) {
        // Email must be unique (the database also has a UNIQUE key on it)
        $check = $db->prepare('SELECT id FROM users WHERE email = :email');
        $check->execute([':email' => $old['email']]);

        if ($check->fetch()) {
            $errors['email'] = 'An account with this email already exists.';
        }
    }

    if (!$errors) {
        if (!$db) {
            $errors['name'] = 'The database is unavailable, so the account could not be created.';
        } else {
            $sql = 'INSERT INTO users (name, email, password_hash, phone, role)
                    VALUES (:name, :email, :password, :phone, \'patient\')';
            $statement = $db->prepare($sql);
            $statement->execute([
                ':name'     => $old['name'],
                ':email'    => $old['email'],
                // password_hash() = bcrypt, checked later with password_verify()
                ':password' => password_hash($old['password'], PASSWORD_DEFAULT),
                ':phone'    => $old['phone'] !== '' ? $old['phone'] : null,
            ]);

            loginUser([
                'id'    => (int) $db->lastInsertId(),
                'name'  => $old['name'],
                'email' => $old['email'],
                'role'  => 'patient',
            ]);

            flashSet('success', 'Your account has been created. Welcome to MediBook!');
            header('Location: patient/my-appointments.php');
            exit;
        }
    }
}

$pageTitle = 'Create Account | MediBook';
$baseUrl   = '';
$activeNav = 'register';

require __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <span class="section-label">Patient registration</span>
        <h1>Create Your Account</h1>
        <p>Register once and you can book, review and cancel your appointments.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="auth-card">
            <h1>Register</h1>
            <p class="auth-subtitle">Fill in your details to create a patient account.</p>

            <form method="post" action="register.php" data-validate>
                <div class="mb-3">
                    <label for="name" class="form-label">Full name</label>
                    <input type="text" class="form-control" id="name" name="name"
                           value="<?= e($old['name']) ?>"
                           data-label="Full name" data-rules="required|maxlength:100"
                           autocomplete="name">
                    <?php if (isset($errors['name'])): ?>
                        <span class="field-error"><?= e($errors['name']) ?></span>
                    <?php endif; ?>
                </div>

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
                    <label for="phone" class="form-label">Phone number</label>
                    <input type="tel" class="form-control" id="phone" name="phone"
                           value="<?= e($old['phone']) ?>"
                           data-label="Phone number" data-rules="digits|maxlength:15"
                           placeholder="Optional - digits only" autocomplete="tel">
                    <?php if (isset($errors['phone'])): ?>
                        <span class="field-error"><?= e($errors['phone']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password"
                               name="password"
                               data-label="Password" data-rules="required|minlength:6"
                               autocomplete="new-password">
                        <?php if (isset($errors['password'])): ?>
                            <span class="field-error"><?= e($errors['password']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="col-sm-6">
                        <label for="confirm" class="form-label">Confirm password</label>
                        <input type="password" class="form-control" id="confirm"
                               name="confirm"
                               data-label="Confirm password"
                               data-rules="required|matches:password"
                               autocomplete="new-password">
                        <?php if (isset($errors['confirm'])): ?>
                            <span class="field-error"><?= e($errors['confirm']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-text mb-3">
                    Minimum 6 characters. Your password is stored as a
                    bcrypt hash (password_hash) - never as plain text.
                </div>

                <button type="submit" class="btn-primary-custom w-100">
                    Create Account →
                </button>
            </form>

            <p class="auth-links">
                Already registered? <a href="login.php">Sign in</a>
            </p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
