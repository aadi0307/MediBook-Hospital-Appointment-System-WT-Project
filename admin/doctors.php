<?php
/**
 * Admin doctor management.
 *
 * Creates a linked pair of records in users (role = doctor) and doctors,
 * all inside one transaction. Doctors can be paused without deleting any
 * existing appointments or history.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin', '../');

$db     = tryGetDB();
$errors = [];
$departments = getDepartments($db);

$form = [
    'name'             => trim((string) ($_POST['name'] ?? '')),
    'email'            => trim((string) ($_POST['email'] ?? '')),
    'phone'            => trim((string) ($_POST['phone'] ?? '')),
    'department_id'    => (int) ($_POST['department_id'] ?? 0),
    'specialization'   => trim((string) ($_POST['specialization'] ?? '')),
    'qualification'    => trim((string) ($_POST['qualification'] ?? '')),
    'experience_years' => (int) ($_POST['experience_years'] ?? 0),
    'consultation_fee' => trim((string) ($_POST['consultation_fee'] ?? '0')),
    'bio'              => trim((string) ($_POST['bio'] ?? '')),
];

/* ---------- Add a doctor ---------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_doctor'])) {
    $password = (string) ($_POST['password'] ?? '');

    if (!csrfIsValid()) {
        $errors['form'] = 'Your session expired. Please submit the form again.';
    }
    if ($form['name'] === '' || mb_strlen($form['name']) > 100) {
        $errors['name'] = 'Enter the doctor\'s full name (max 100 characters).';
    }
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if ($form['phone'] !== '' && !preg_match('/^[0-9]{7,15}$/', $form['phone'])) {
        $errors['phone'] = 'Phone must be 7 to 15 digits.';
    }
    if (strlen($password) < 8) {
        $errors['password'] = 'Use a password of at least 8 characters.';
    }
    if ($form['department_id'] < 1) {
        $errors['department_id'] = 'Choose a department.';
    }
    if ($form['specialization'] === '' || mb_strlen($form['specialization']) > 150) {
        $errors['specialization'] = 'Enter a specialization (max 150 characters).';
    }
    if ($form['qualification'] === '' || mb_strlen($form['qualification']) > 200) {
        $errors['qualification'] = 'Enter a qualification (max 200 characters).';
    }
    if ($form['experience_years'] < 0 || $form['experience_years'] > 80) {
        $errors['experience_years'] = 'Experience must be between 0 and 80 years.';
    }
    if (!is_numeric($form['consultation_fee'])
        || (float) $form['consultation_fee'] < 0
        || (float) $form['consultation_fee'] > 1000000
    ) {
        $errors['consultation_fee'] = 'Enter a valid consultation fee.';
    }
    if (mb_strlen($form['bio']) > 1000) {
        $errors['bio'] = 'Bio must be 1000 characters or fewer.';
    }

    if (!$errors && $db) {
        $check = $db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $check->execute([':email' => $form['email']]);
        if ($check->fetch()) {
            $errors['email'] = 'An account already uses this email address.';
        }
    }

    if (!$errors) {
        if (!$db) {
            $errors['form'] = 'The database is unavailable, so the doctor could not be created.';
        } else {
            try {
                $db->beginTransaction();

                $statement = $db->prepare(
                    'INSERT INTO users (name, email, password_hash, phone, role, is_active)
                     VALUES (:name, :email, :password, :phone, \'doctor\', 1)'
                );
                $statement->execute([
                    ':name'     => $form['name'],
                    ':email'    => $form['email'],
                    ':password' => password_hash($password, PASSWORD_DEFAULT),
                    ':phone'    => $form['phone'] !== '' ? $form['phone'] : null,
                ]);
                $userId = (int) $db->lastInsertId();

                $statement = $db->prepare(
                    'INSERT INTO doctors
                        (user_id, department_id, specialization, qualification,
                         experience_years, consultation_fee, bio, is_active)
                     VALUES
                        (:user, :department, :specialization, :qualification,
                         :experience, :fee, :bio, 1)'
                );
                $statement->execute([
                    ':user'           => $userId,
                    ':department'     => $form['department_id'],
                    ':specialization' => $form['specialization'],
                    ':qualification'  => $form['qualification'],
                    ':experience'     => $form['experience_years'],
                    ':fee'            => number_format((float) $form['consultation_fee'], 2, '.', ''),
                    ':bio'            => $form['bio'] !== '' ? $form['bio'] : null,
                ]);

                $db->commit();

                flashSet('success', $form['name'] . ' was added as a doctor.');
                header('Location: doctors.php');
                exit;
            } catch (PDOException $exception) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $errors['form'] = $exception->getCode() === '23000'
                    ? 'An account or doctor record already exists for those details.'
                    : 'The doctor could not be created. Please try again.';
            }
        }
    }
}

/* ---------- Pause / reactivate a doctor ---------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_doctor'])) {
    $doctorId = (int) ($_POST['doctor_id'] ?? 0);

    if (!csrfIsValid()) {
        flashSet('danger', 'Your session expired. Please try again.');
    } elseif (!$db) {
        flashSet('danger', 'The database is unavailable.');
    } else {
        $lookup = $db->prepare(
            'SELECT d.id, d.user_id, d.is_active, u.is_active AS user_active
             FROM doctors d
             JOIN users u ON u.id = d.user_id
             WHERE d.id = :id AND u.role = \'doctor\'
             LIMIT 1'
        );
        $lookup->execute([':id' => $doctorId]);
        $target = $lookup->fetch();

        if (!$target) {
            flashSet('warning', 'Doctor account not found.');
        } else {
            $currentlyActive = ((int) $target['is_active'] === 1 && (int) $target['user_active'] === 1);
            $newActive = $currentlyActive ? 0 : 1;

            try {
                $db->beginTransaction();

                $update = $db->prepare('UPDATE doctors SET is_active = :active WHERE id = :id');
                $update->execute([':active' => $newActive, ':id' => $doctorId]);

                $update = $db->prepare(
                    'UPDATE users SET is_active = :active WHERE id = :user AND role = \'doctor\''
                );
                $update->execute([':active' => $newActive, ':user' => (int) $target['user_id']]);

                $db->commit();
                flashSet('success', $newActive
                    ? 'Doctor account reactivated. They can sign in again.'
                    : 'Doctor account paused. Existing appointments are preserved.');
            } catch (PDOException $exception) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                flashSet('danger', 'The doctor account could not be updated.');
            }
        }
    }

    header('Location: doctors.php');
    exit;
}

$doctors = getAllDoctorsAdmin($db);

$pageTitle = 'Doctor Management | MediBook Admin';
$baseUrl   = '../';
$activeNav = 'doctors';
$activeAdmin = 'doctors';
$extraCss  = ['assets/css/dashboard.css'];

require __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-hero role-admin compact-hero">
    <div class="container">
        <span class="dashboard-kicker">Administration</span>
        <h1>Doctor management</h1>
        <p>Create doctor accounts and control who can sign in.</p>
    </div>
</section>

<section class="dashboard-section">
    <div class="container">
        <div class="admin-layout">
            <?php require __DIR__ . '/../includes/admin_nav.php'; ?>

            <div class="admin-main">
                <?php if (isset($errors['form'])): ?>
                    <div class="alert alert-danger"><?= e($errors['form']) ?></div>
                <?php endif; ?>

                <div class="dashboard-panel">
                    <div class="panel-heading">
                        <div>
                            <span class="section-label">New account</span>
                            <h2>Add a doctor</h2>
                        </div>
                        <span class="panel-hint">Password is stored with password_hash()</span>
                    </div>

                    <form method="post" action="doctors.php" data-validate>
                        <?= csrfField() ?>

                        <div class="form-grid">
                            <div>
                                <label for="name" class="form-label">Full name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="<?= e($form['name']) ?>"
                                       data-label="Full name" data-rules="required|maxlength:100">
                                <?php if (isset($errors['name'])): ?><span class="field-error"><?= e($errors['name']) ?></span><?php endif; ?>
                            </div>
                            <div>
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?= e($form['email']) ?>"
                                       data-label="Email address" data-rules="required|email">
                                <?php if (isset($errors['email'])): ?><span class="field-error"><?= e($errors['email']) ?></span><?php endif; ?>
                            </div>
                            <div>
                                <label for="phone" class="form-label">Phone (optional)</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       value="<?= e($form['phone']) ?>"
                                       data-label="Phone" data-rules="digits|maxlength:15">
                                <?php if (isset($errors['phone'])): ?><span class="field-error"><?= e($errors['phone']) ?></span><?php endif; ?>
                            </div>
                            <div>
                                <label for="password" class="form-label">Temporary password</label>
                                <input type="password" class="form-control" id="password" name="password"
                                       data-label="Password" data-rules="required|minlength:8"
                                       autocomplete="new-password">
                                <?php if (isset($errors['password'])): ?><span class="field-error"><?= e($errors['password']) ?></span><?php endif; ?>
                            </div>
                            <div>
                                <label for="department_id" class="form-label">Department</label>
                                <select class="form-select" id="department_id" name="department_id"
                                        data-label="Department" data-rules="required">
                                    <option value="">Choose department</option>
                                    <?php foreach ($departments as $department): ?>
                                        <option value="<?= (int) $department['id'] ?>"
                                            <?= $form['department_id'] === (int) $department['id'] ? 'selected' : '' ?>>
                                            <?= e($department['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['department_id'])): ?><span class="field-error"><?= e($errors['department_id']) ?></span><?php endif; ?>
                            </div>
                            <div>
                                <label for="specialization" class="form-label">Specialization</label>
                                <input type="text" class="form-control" id="specialization" name="specialization"
                                       value="<?= e($form['specialization']) ?>"
                                       data-label="Specialization" data-rules="required|maxlength:150"
                                       placeholder="e.g. Cardiologist">
                                <?php if (isset($errors['specialization'])): ?><span class="field-error"><?= e($errors['specialization']) ?></span><?php endif; ?>
                            </div>
                            <div>
                                <label for="qualification" class="form-label">Qualification</label>
                                <input type="text" class="form-control" id="qualification" name="qualification"
                                       value="<?= e($form['qualification']) ?>"
                                       data-label="Qualification" data-rules="required|maxlength:200"
                                       placeholder="e.g. MBBS, MD">
                                <?php if (isset($errors['qualification'])): ?><span class="field-error"><?= e($errors['qualification']) ?></span><?php endif; ?>
                            </div>
                            <div>
                                <label for="experience_years" class="form-label">Experience (years)</label>
                                <input type="number" class="form-control" id="experience_years" name="experience_years"
                                       min="0" max="80" value="<?= (int) $form['experience_years'] ?>">
                                <?php if (isset($errors['experience_years'])): ?><span class="field-error"><?= e($errors['experience_years']) ?></span><?php endif; ?>
                            </div>
                            <div>
                                <label for="consultation_fee" class="form-label">Consultation fee (₹)</label>
                                <input type="number" class="form-control" id="consultation_fee" name="consultation_fee"
                                       min="0" step="0.01" value="<?= e($form['consultation_fee']) ?>">
                                <?php if (isset($errors['consultation_fee'])): ?><span class="field-error"><?= e($errors['consultation_fee']) ?></span><?php endif; ?>
                            </div>
                            <div class="form-grid-full">
                                <label for="bio" class="form-label">Short bio (optional)</label>
                                <textarea class="form-control" id="bio" name="bio" rows="3"
                                          data-label="Bio" data-rules="maxlength:1000"><?= e($form['bio']) ?></textarea>
                                <?php if (isset($errors['bio'])): ?><span class="field-error"><?= e($errors['bio']) ?></span><?php endif; ?>
                            </div>
                        </div>

                        <button type="submit" name="add_doctor" value="1" class="btn-primary-custom mt-3">
                            Create doctor account →
                        </button>
                    </form>
                </div>

                <div class="dashboard-panel p-0">
                    <div class="panel-heading px-3 pt-3">
                        <div>
                            <span class="section-label">Directory</span>
                            <h2>All doctors</h2>
                        </div>
                        <span class="panel-hint"><?= count($doctors) ?> records</span>
                    </div>

                    <?php if (!$doctors): ?>
                        <div class="empty-state">
                            <span>🩺</span>
                            <strong>No doctors yet</strong>
                            <p>Create the first doctor account using the form above.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-soft align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Doctor</th>
                                        <th>Department</th>
                                        <th>Details</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($doctors as $item): ?>
                                        <?php $isActive = ((int) $item['is_active'] === 1 && (int) $item['user_active'] === 1); ?>
                                        <tr>
                                            <td>
                                                <strong><?= e($item['name']) ?></strong><br>
                                                <small class="text-muted"><?= e($item['email']) ?></small><br>
                                                <small class="text-muted"><?= e($item['phone'] ?: 'No phone') ?></small>
                                            </td>
                                            <td>
                                                <?= e($item['department_name']) ?><br>
                                                <small class="text-muted"><?= e($item['specialization']) ?></small>
                                            </td>
                                            <td>
                                                <?= (int) $item['experience_years'] ?> yrs<br>
                                                <small class="text-muted">₹<?= number_format((float) $item['consultation_fee'], 0) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge <?= $isActive ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= $isActive ? 'Active' : 'Paused' ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <form method="post" action="doctors.php" class="d-inline"
                                                      onsubmit="return confirm('<?= $isActive ? 'Pause' : 'Reactivate' ?> this doctor account?');">
                                                    <?= csrfField() ?>
                                                    <input type="hidden" name="doctor_id" value="<?= (int) $item['id'] ?>">
                                                    <button type="submit" name="toggle_doctor" value="1"
                                                            class="btn-mini <?= $isActive ? 'cancel' : 'confirm' ?>">
                                                        <?= $isActive ? 'Pause' : 'Reactivate' ?>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
