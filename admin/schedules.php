<?php
/**
 * Admin schedule management.
 * Adds weekly working blocks for doctors and toggles their availability.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin', '../');

$db     = tryGetDB();
$errors = [];
$allDoctors = getAllDoctorsAdmin($db);
$activeDoctors = array_values(array_filter($allDoctors, function (array $doctor): bool {
    return (int) $doctor['is_active'] === 1 && (int) $doctor['user_active'] === 1;
}));

$form = [
    'doctor_id'              => (int) ($_POST['doctor_id'] ?? 0),
    'day_of_week'            => (int) ($_POST['day_of_week'] ?? 1),
    'start_time'             => trim((string) ($_POST['start_time'] ?? '')),
    'end_time'               => trim((string) ($_POST['end_time'] ?? '')),
    'slot_duration_minutes'  => (int) ($_POST['slot_duration_minutes'] ?? 30),
];

/* ---------- Add a schedule block ---------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_schedule'])) {
    if (!csrfIsValid()) {
        $errors['form'] = 'Your session expired. Please submit the form again.';
    }
    if ($form['doctor_id'] < 1) {
        $errors['doctor_id'] = 'Choose a doctor.';
    }
    if ($form['day_of_week'] < 0 || $form['day_of_week'] > 6) {
        $errors['day_of_week'] = 'Choose a valid weekday.';
    }
    if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $form['start_time'])
        || !preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $form['end_time'])
    ) {
        $errors['start_time'] = 'Enter valid start and end times.';
    } elseif ($form['end_time'] <= $form['start_time']) {
        $errors['end_time'] = 'The end time must be after the start time.';
    }
    if ($form['slot_duration_minutes'] < 10 || $form['slot_duration_minutes'] > 180) {
        $errors['slot_duration_minutes'] = 'Slot duration must be between 10 and 180 minutes.';
    }

    // The chosen doctor must exist and be active.
    if (!$errors && $db) {
        $check = $db->prepare(
            'SELECT d.id
             FROM doctors d
             JOIN users u ON u.id = d.user_id
             WHERE d.id = :doctor AND d.is_active = 1 AND u.is_active = 1
             LIMIT 1'
        );
        $check->execute([':doctor' => $form['doctor_id']]);
        if (!$check->fetch()) {
            $errors['doctor_id'] = 'That doctor is not available for new schedules.';
        }
    }

    if (!$errors) {
        if (!$db) {
            $errors['form'] = 'The database is unavailable.';
        } else {
            try {
                $statement = $db->prepare(
                    'INSERT INTO schedules
                        (doctor_id, day_of_week, start_time, end_time,
                         slot_duration_minutes, is_available)
                     VALUES
                        (:doctor, :day, :start, :end, :duration, 1)'
                );
                $statement->execute([
                    ':doctor'   => $form['doctor_id'],
                    ':day'      => $form['day_of_week'],
                    ':start'    => $form['start_time'] . ':00',
                    ':end'      => $form['end_time'] . ':00',
                    ':duration' => $form['slot_duration_minutes'],
                ]);

                flashSet('success', 'Schedule added successfully.');
                header('Location: schedules.php');
                exit;
            } catch (PDOException $exception) {
                $errors['form'] = $exception->getCode() === '23000'
                    ? 'This exact schedule block already exists for that doctor.'
                    : 'The schedule could not be saved.';
            }
        }
    }
}

/* ---------- Toggle schedule availability ---------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_schedule'])) {
    $scheduleId = (int) ($_POST['schedule_id'] ?? 0);

    if (!csrfIsValid()) {
        flashSet('danger', 'Your session expired. Please try again.');
    } elseif (!$db) {
        flashSet('danger', 'The database is unavailable.');
    } else {
        $lookup = $db->prepare('SELECT id, is_available FROM schedules WHERE id = :id LIMIT 1');
        $lookup->execute([':id' => $scheduleId]);
        $schedule = $lookup->fetch();

        if (!$schedule) {
            flashSet('warning', 'Schedule block not found.');
        } else {
            $newAvailable = (int) $schedule['is_available'] === 1 ? 0 : 1;
            $update = $db->prepare('UPDATE schedules SET is_available = :available WHERE id = :id');
            $update->execute([':available' => $newAvailable, ':id' => $scheduleId]);

            flashSet('success', $newAvailable
                ? 'Schedule block is available again.'
                : 'Schedule block hidden from patient booking.');
        }
    }

    header('Location: schedules.php');
    exit;
}

$schedules = getAllSchedulesAdmin($db);

$pageTitle = 'Schedule Management | MediBook Admin';
$baseUrl   = '../';
$activeNav = 'schedules';
$activeAdmin = 'schedules';
$extraCss  = ['assets/css/dashboard.css'];

require __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-hero role-admin compact-hero">
    <div class="container">
        <span class="dashboard-kicker">Administration</span>
        <h1>Schedule management</h1>
        <p>Set the weekly hours patients can book and hide blocks when needed.</p>
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
                            <span class="section-label">Weekly hours</span>
                            <h2>Add a schedule block</h2>
                        </div>
                        <span class="panel-hint">Times use the hospital's local clock</span>
                    </div>

                    <?php if (!$activeDoctors): ?>
                        <div class="alert alert-warning mb-0">
                            There are no active doctors. Add or reactivate a doctor first.
                        </div>
                    <?php else: ?>
                        <form method="post" action="schedules.php" data-validate>
                            <?= csrfField() ?>
                            <div class="form-grid">
                                <div>
                                    <label for="doctor_id" class="form-label">Doctor</label>
                                    <select class="form-select" id="doctor_id" name="doctor_id"
                                            data-label="Doctor" data-rules="required">
                                        <option value="">Choose doctor</option>
                                        <?php foreach ($activeDoctors as $doctor): ?>
                                            <option value="<?= (int) $doctor['id'] ?>"
                                                <?= $form['doctor_id'] === (int) $doctor['id'] ? 'selected' : '' ?>>
                                                <?= e($doctor['name']) ?> · <?= e($doctor['department_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['doctor_id'])): ?><span class="field-error"><?= e($errors['doctor_id']) ?></span><?php endif; ?>
                                </div>
                                <div>
                                    <label for="day_of_week" class="form-label">Weekday</label>
                                    <select class="form-select" id="day_of_week" name="day_of_week">
                                        <?php for ($day = 0; $day <= 6; $day++): ?>
                                            <option value="<?= $day ?>" <?= $form['day_of_week'] === $day ? 'selected' : '' ?>>
                                                <?= e(weekdayName($day)) ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                    <?php if (isset($errors['day_of_week'])): ?><span class="field-error"><?= e($errors['day_of_week']) ?></span><?php endif; ?>
                                </div>
                                <div>
                                    <label for="start_time" class="form-label">Start time</label>
                                    <input type="time" class="form-control" id="start_time" name="start_time"
                                           value="<?= e($form['start_time']) ?>"
                                           data-label="Start time" data-rules="required">
                                    <?php if (isset($errors['start_time'])): ?><span class="field-error"><?= e($errors['start_time']) ?></span><?php endif; ?>
                                </div>
                                <div>
                                    <label for="end_time" class="form-label">End time</label>
                                    <input type="time" class="form-control" id="end_time" name="end_time"
                                           value="<?= e($form['end_time']) ?>"
                                           data-label="End time" data-rules="required">
                                    <?php if (isset($errors['end_time'])): ?><span class="field-error"><?= e($errors['end_time']) ?></span><?php endif; ?>
                                </div>
                                <div>
                                    <label for="slot_duration_minutes" class="form-label">Slot length (minutes)</label>
                                    <input type="number" class="form-control" id="slot_duration_minutes"
                                           name="slot_duration_minutes" min="10" max="180" step="5"
                                           value="<?= (int) $form['slot_duration_minutes'] ?>">
                                    <?php if (isset($errors['slot_duration_minutes'])): ?><span class="field-error"><?= e($errors['slot_duration_minutes']) ?></span><?php endif; ?>
                                </div>
                            </div>
                            <button type="submit" name="add_schedule" value="1" class="btn-primary-custom mt-3">
                                Add schedule →
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <div class="dashboard-panel p-0">
                    <div class="panel-heading px-3 pt-3">
                        <div>
                            <span class="section-label">Weekly timetable</span>
                            <h2>All schedule blocks</h2>
                        </div>
                        <span class="panel-hint"><?= count($schedules) ?> blocks</span>
                    </div>

                    <?php if (!$schedules): ?>
                        <div class="empty-state compact">
                            <span>🕘</span>
                            <strong>No schedules yet</strong>
                            <p>Add a doctor's working hours above.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-soft align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Doctor</th>
                                        <th>Day</th>
                                        <th>Hours</th>
                                        <th>Slot</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($schedules as $schedule): ?>
                                        <?php $isAvailable = (int) $schedule['is_available'] === 1; ?>
                                        <tr>
                                            <td>
                                                <strong><?= e($schedule['doctor_name']) ?></strong><br>
                                                <small class="text-muted"><?= e($schedule['department_name']) ?></small>
                                            </td>
                                            <td><?= e(weekdayName((int) $schedule['day_of_week'])) ?></td>
                                            <td>
                                                <?= formatTime($schedule['start_time']) ?>
                                                – <?= formatTime($schedule['end_time']) ?>
                                            </td>
                                            <td><?= (int) $schedule['slot_duration_minutes'] ?> min</td>
                                            <td>
                                                <span class="badge <?= $isAvailable ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= $isAvailable ? 'Available' : 'Hidden' ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <form method="post" action="schedules.php" class="d-inline">
                                                    <?= csrfField() ?>
                                                    <input type="hidden" name="schedule_id" value="<?= (int) $schedule['id'] ?>">
                                                    <button type="submit" name="toggle_schedule" value="1"
                                                            class="btn-mini <?= $isAvailable ? 'cancel' : 'confirm' ?>">
                                                        <?= $isAvailable ? 'Hide' : 'Show' ?>
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
