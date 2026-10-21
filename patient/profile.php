<?php
/**
 * Profile (patient area) - view + update own account details.
 *
 * Two small forms on one page:
 *   1. Personal details  (name, email, phone)  -> UPDATE users
 *   2. Change password                         -> UPDATE users (hash only)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin('../');

$db   = tryGetDB();
$user = currentUser();
$errors = [];

/* ---------- Load the account row ---------- */

$account = null;
if ($db) {
    $statement = $db->prepare('SELECT id, name, email, phone, password_hash, created_at
                               FROM users WHERE id = :id');
    $statement->execute([':id' => $user['id']]);
    $account = $statement->fetch();
}

$infoOld = [
    'name'  => trim((string) ($_POST['name']  ?? ($account['name']  ?? ''))),
    'email' => trim((string) ($_POST['email'] ?? ($account['email'] ?? ''))),
    'phone' => trim((string) ($_POST['phone'] ?? ($account['phone'] ?? ''))),
];

/* ---------- 1. Update personal details ---------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
    if ($infoOld['name'] === '' || mb_strlen($infoOld['name']) > 100) {
        $errors['name'] = 'Please enter your name (max 100 characters).';
    }
    if (!filter_var($infoOld['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if ($infoOld['phone'] !== '' && !preg_match('/^[0-9]{7,15}$/', $infoOld['phone'])) {
        $errors['phone'] = 'Phone number must be 7 to 15 digits.';
    }

    if (!$errors && $db) {
        // The email must still be unique among other users
        $check = $db->prepare('SELECT id FROM users WHERE email = :email AND id <> :id');
        $check->execute([':email' => $infoOld['email'], ':id' => $user['id']]);
        if ($check->fetch()) {
            $errors['email'] = 'Another account already uses this email address.';
        }
    }

    if (!$errors) {
        if (!$db) {
            $errors['name'] = 'The database is unavailable.';
        } else {
            $sql = 'UPDATE users SET name = :name, email = :email, phone = :phone
                    WHERE id = :id';
            $statement = $db->prepare($sql);
            $statement->execute([
                ':name'  => $infoOld['name'],
                ':email' => $infoOld['email'],
                ':phone' => $infoOld['phone'] !== '' ? $infoOld['phone'] : null,
                ':id'    => $user['id'],
            ]);

            // Keep the session copy in sync with the database
            $_SESSION['user']['name']  = $infoOld['name'];
            $_SESSION['user']['email'] = $infoOld['email'];

            flashSet('success', 'Your profile has been updated.');
            header('Location: profile.php');
            exit;
        }
    }
}

/* ---------- 2. Change password ---------- */

$passwordOld = [
    'current' => (string) ($_POST['current_password'] ?? ''),
    'new'     => (string) ($_POST['new_password'] ?? ''),
    'confirm' => (string) ($_POST['confirm_password'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    if ($passwordOld['current'] === '' || $passwordOld['new'] === '') {
        $errors['current_password'] = 'Please fill in both password fields.';
    } elseif ($account && !password_verify($passwordOld['current'], $account['password_hash'])) {
        $errors['current_password'] = 'Your current password is incorrect.';
    }
    if (strlen($passwordOld['new']) < 6) {
        $errors['new_password'] = 'The new password must be at least 6 characters.';
    }
    if ($passwordOld['new'] !== $passwordOld['confirm']) {
        $errors['confirm_password'] = 'The two passwords do not match.';
    }

    if (!$errors) {
        if (!$db) {
            $errors['current_password'] = 'The database is unavailable.';
        } else {
            $statement = $db->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
            $statement->execute([
                ':hash' => password_hash($passwordOld['new'], PASSWORD_DEFAULT),
                ':id'   => $user['id'],
            ]);

            flashSet('success', 'Your password has been changed.');
            header('Location: profile.php');
            exit;
        }
    }
}

$pageTitle = 'My Profile | MediBook';
$baseUrl   = '../';
$activeNav = 'profile';

require __DIR__ . '/../includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <span class="section-label">Patient area</span>
        <h1>My Profile</h1>
        <p>Manage your personal details and password.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4 justify-content-center">

            <!-- Summary -->
            <div class="col-lg-4">
                <div class="info-panel">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar-circle" style="width:52px;height:52px">
                            <?= e(initials($user['name'])) ?>
                        </div>
                        <div>
                            <strong style="display:block"><?= e($user['name']) ?></strong>
                            <small class="text-muted">Patient account</small>
                        </div>
                    </div>
                    <div class="info-line">
                        <div>📧</div>
                        <div>
                            <strong>Email</strong>
                            <span><?= e($user['email']) ?></span>
                        </div>
                    </div>
                    <div class="info-line">
                        <div>📅</div>
                        <div>
                            <strong>Member since</strong>
                            <span><?= $account ? formatDate(substr($account['created_at'], 0, 10)) : '-' ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal details form -->
            <div class="col-lg-5">
                <div class="card-soft" style="height:auto">
                    <h2 style="font-size:18px;font-weight:800">Personal details</h2>

                    <form method="post" action="profile.php" data-validate>
                        <div class="mb-3">
                            <label for="name" class="form-label">Full name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                   value="<?= e($infoOld['name']) ?>"
                                   data-label="Full name" data-rules="required|maxlength:100">
                            <?php if (isset($errors['name'])): ?>
                                <span class="field-error"><?= e($errors['name']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= e($infoOld['email']) ?>"
                                   data-label="Email address" data-rules="required|email">
                            <?php if (isset($errors['email'])): ?>
                                <span class="field-error"><?= e($errors['email']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone number</label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                   value="<?= e($infoOld['phone']) ?>"
                                   data-label="Phone number" data-rules="digits|maxlength:15"
                                   placeholder="Optional - digits only">
                            <?php if (isset($errors['phone'])): ?>
                                <span class="field-error"><?= e($errors['phone']) ?></span>
                            <?php endif; ?>
                        </div>

                        <button type="submit" name="update_info" value="1"
                                class="btn-primary-custom w-100">
                            Save Changes
                        </button>
                    </form>
                </div>

                <!-- Password form -->
                <div class="card-soft mt-4" style="height:auto">
                    <h2 style="font-size:18px;font-weight:800">Change password</h2>

                    <form method="post" action="profile.php" data-validate>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current password</label>
                            <input type="password" class="form-control" id="current_password"
                                   name="current_password"
                                   data-label="Current password" data-rules="required"
                                   autocomplete="current-password">
                            <?php if (isset($errors['current_password'])): ?>
                                <span class="field-error"><?= e($errors['current_password']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="new_password" class="form-label">New password</label>
                                <input type="password" class="form-control" id="new_password"
                                       name="new_password"
                                       data-label="New password" data-rules="required|minlength:6"
                                       autocomplete="new-password">
                                <?php if (isset($errors['new_password'])): ?>
                                    <span class="field-error"><?= e($errors['new_password']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="col-sm-6">
                                <label for="confirm_password" class="form-label">Confirm</label>
                                <input type="password" class="form-control" id="confirm_password"
                                       name="confirm_password"
                                       data-label="Confirm password"
                                       data-rules="required|matches:new_password"
                                       autocomplete="new-password">
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <span class="field-error"><?= e($errors['confirm_password']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <button type="submit" name="update_password" value="1"
                                class="btn-outline-custom w-100 mt-3">
                            Change Password
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
