<?php
/**
 * Admin department management.
 * Departments are paused (not deleted) so existing doctor and appointment
 * relationships remain valid.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin', '../');

$db     = tryGetDB();
$errors = [];
$form   = [
    'name'        => trim((string) ($_POST['name'] ?? '')),
    'description' => trim((string) ($_POST['description'] ?? '')),
];

/* ---------- Add department ---------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_department'])) {
    if (!csrfIsValid()) {
        $errors['form'] = 'Your session expired. Please submit the form again.';
    }
    if ($form['name'] === '' || mb_strlen($form['name']) > 100) {
        $errors['name'] = 'Enter a department name (max 100 characters).';
    }
    if (mb_strlen($form['description']) > 1000) {
        $errors['description'] = 'Description must be 1000 characters or fewer.';
    }

    if (!$errors && $db) {
        $check = $db->prepare('SELECT id FROM departments WHERE name = :name LIMIT 1');
        $check->execute([':name' => $form['name']]);
        if ($check->fetch()) {
            $errors['name'] = 'A department with this name already exists.';
        }
    }

    if (!$errors) {
        if (!$db) {
            $errors['form'] = 'The database is unavailable.';
        } else {
            try {
                $statement = $db->prepare(
                    'INSERT INTO departments (name, description, is_active)
                     VALUES (:name, :description, 1)'
                );
                $statement->execute([
                    ':name'        => $form['name'],
                    ':description' => $form['description'] !== '' ? $form['description'] : null,
                ]);

                flashSet('success', $form['name'] . ' department was added.');
                header('Location: departments.php');
                exit;
            } catch (PDOException $exception) {
                $errors['form'] = $exception->getCode() === '23000'
                    ? 'A department with this name already exists.'
                    : 'The department could not be created.';
            }
        }
    }
}

/* ---------- Pause / reactivate ---------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_department'])) {
    $departmentId = (int) ($_POST['department_id'] ?? 0);

    if (!csrfIsValid()) {
        flashSet('danger', 'Your session expired. Please try again.');
    } elseif (!$db) {
        flashSet('danger', 'The database is unavailable.');
    } else {
        $lookup = $db->prepare('SELECT id, is_active FROM departments WHERE id = :id LIMIT 1');
        $lookup->execute([':id' => $departmentId]);
        $department = $lookup->fetch();

        if (!$department) {
            flashSet('warning', 'Department not found.');
        } else {
            $newActive = (int) $department['is_active'] === 1 ? 0 : 1;
            $update = $db->prepare('UPDATE departments SET is_active = :active WHERE id = :id');
            $update->execute([':active' => $newActive, ':id' => $departmentId]);

            flashSet('success', $newActive
                ? 'Department reactivated.'
                : 'Department paused. Existing records were preserved.');
        }
    }

    header('Location: departments.php');
    exit;
}

$departments = getAllDepartmentsAdmin($db);

$pageTitle = 'Department Management | MediBook Admin';
$baseUrl   = '../';
$activeNav = 'departments';
$activeAdmin = 'departments';
$extraCss  = ['assets/css/dashboard.css'];

require __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-hero role-admin compact-hero">
    <div class="container">
        <span class="dashboard-kicker">Administration</span>
        <h1>Department management</h1>
        <p>Add hospital specialties and pause departments you no longer use.</p>
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
                            <span class="section-label">New specialty</span>
                            <h2>Add a department</h2>
                        </div>
                    </div>

                    <form method="post" action="departments.php" data-validate>
                        <?= csrfField() ?>
                        <div class="form-grid">
                            <div>
                                <label for="name" class="form-label">Department name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="<?= e($form['name']) ?>"
                                       data-label="Department name" data-rules="required|maxlength:100"
                                       placeholder="e.g. Pediatrics">
                                <?php if (isset($errors['name'])): ?><span class="field-error"><?= e($errors['name']) ?></span><?php endif; ?>
                            </div>
                            <div class="form-grid-full">
                                <label for="description" class="form-label">Description (optional)</label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                          data-label="Description" data-rules="maxlength:1000"><?= e($form['description']) ?></textarea>
                                <?php if (isset($errors['description'])): ?><span class="field-error"><?= e($errors['description']) ?></span><?php endif; ?>
                            </div>
                        </div>
                        <button type="submit" name="add_department" value="1" class="btn-primary-custom mt-3">
                            Add department →
                        </button>
                    </form>
                </div>

                <div class="dashboard-panel p-0">
                    <div class="panel-heading px-3 pt-3">
                        <div>
                            <span class="section-label">Hospital structure</span>
                            <h2>All departments</h2>
                        </div>
                        <span class="panel-hint"><?= count($departments) ?> departments</span>
                    </div>

                    <?php if (!$departments): ?>
                        <div class="empty-state compact">
                            <span>🏥</span>
                            <strong>No departments yet</strong>
                            <p>Add the first hospital department above.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-soft align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th>Description</th>
                                        <th>Doctors</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($departments as $department): ?>
                                        <?php $isActive = (int) $department['is_active'] === 1; ?>
                                        <tr>
                                            <td><strong><?= e($department['name']) ?></strong></td>
                                            <td class="reason-cell"><?= e($department['description'] ?: 'No description') ?></td>
                                            <td><?= (int) $department['doctor_count'] ?></td>
                                            <td>
                                                <span class="badge <?= $isActive ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= $isActive ? 'Active' : 'Paused' ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <form method="post" action="departments.php" class="d-inline"
                                                      onsubmit="return confirm('<?= $isActive ? 'Pause' : 'Reactivate' ?> this department?');">
                                                    <?= csrfField() ?>
                                                    <input type="hidden" name="department_id" value="<?= (int) $department['id'] ?>">
                                                    <button type="submit" name="toggle_department" value="1"
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
