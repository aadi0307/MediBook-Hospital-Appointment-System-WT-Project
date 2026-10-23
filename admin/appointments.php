<?php
/**
 * Admin appointment management.
 * Lists hospital-wide appointments, supports filtering and lets an
 * administrator correct a booking's status.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin', '../');

$db        = tryGetDB();
$status    = (string) ($_GET['status'] ?? '');
$date      = trim((string) ($_GET['date'] ?? ''));
$patientId = (int) ($_GET['patient_id'] ?? 0);

if (!in_array($status, APPOINTMENT_STATUSES, true)) {
    $status = '';
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $date = '';
}

/* ---------- Update status ---------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_appointment'])) {
    $appointmentId = (int) ($_POST['appointment_id'] ?? 0);
    $newStatus     = (string) ($_POST['status'] ?? '');

    if (!csrfIsValid()) {
        flashSet('danger', 'Your session expired. Please try again.');
    } elseif (!$db) {
        flashSet('danger', 'The database is unavailable.');
    } elseif ($appointmentId < 1 || !in_array($newStatus, APPOINTMENT_STATUSES, true)) {
        flashSet('danger', 'That appointment update is not valid.');
    } else {
        $statement = $db->prepare(
            'UPDATE appointments SET status = :status WHERE id = :id'
        );
        $statement->execute([':status' => $newStatus, ':id' => $appointmentId]);

        flashSet($statement->rowCount() > 0 ? 'success' : 'warning',
            $statement->rowCount() > 0
                ? 'Appointment status updated to ' . $newStatus . '.'
                : 'No appointment was changed.');
    }

    $query = array_filter([
        'status'     => $status,
        'date'       => $date,
        'patient_id' => $patientId > 0 ? $patientId : '',
    ], function ($value): bool {
        return $value !== '' && $value !== null;
    });

    header('Location: appointments.php' . ($query ? '?' . http_build_query($query) : ''));
    exit;
}

$patientName = '';
if ($patientId > 0 && $db) {
    $lookup = $db->prepare('SELECT name FROM users WHERE id = :id AND role = \'patient\' LIMIT 1');
    $lookup->execute([':id' => $patientId]);
    $patient = $lookup->fetch();
    $patientName = $patient['name'] ?? '';
    if (!$patient) {
        $patientId = 0;
    }
}

$appointments = getAllAppointments($db, $status, $date, $patientId);
$hasFilter = $status !== '' || $date !== '' || $patientId > 0;

$pageTitle = 'Appointment Management | MediBook Admin';
$baseUrl   = '../';
$activeNav = 'appointments';
$activeAdmin = 'appointments';
$extraCss  = ['assets/css/dashboard.css'];

require __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-hero role-admin compact-hero">
    <div class="container">
        <span class="dashboard-kicker">Administration</span>
        <h1>Appointment management</h1>
        <p>Review all hospital bookings, filter the list and correct statuses.</p>
    </div>
</section>

<section class="dashboard-section">
    <div class="container">
        <div class="admin-layout">
            <?php require __DIR__ . '/../includes/admin_nav.php'; ?>

            <div class="admin-main">
                <div class="dashboard-panel">
                    <div class="panel-heading">
                        <div>
                            <span class="section-label">Hospital-wide bookings</span>
                            <h2>All appointments</h2>
                        </div>
                        <span class="panel-hint"><?= count($appointments) ?> result<?= count($appointments) === 1 ? '' : 's' ?></span>
                    </div>

                    <form method="get" action="appointments.php" class="admin-filter-form">
                        <div>
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All statuses</option>
                                <?php foreach (APPOINTMENT_STATUSES as $option): ?>
                                    <option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>>
                                        <?= e(ucfirst($option)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" value="<?= e($date) ?>">
                        </div>
                        <div class="admin-filter-actions">
                            <button type="submit" class="btn-primary-custom">Apply filters</button>
                            <?php if ($hasFilter): ?>
                                <a href="appointments.php" class="btn-outline-custom">Reset</a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <?php if ($patientId > 0): ?>
                        <div class="filter-context">
                            Showing visits for <strong><?= e($patientName) ?></strong>
                            <a href="appointments.php">Show all patients</a>
                        </div>
                    <?php endif; ?>

                    <?php if (!$appointments): ?>
                        <div class="empty-state compact">
                            <span>📋</span>
                            <strong>No appointments found</strong>
                            <p>Try removing a filter to see more bookings.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-soft align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Date &amp; time</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th class="text-end">Set status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $appointment): ?>
                                        <tr>
                                            <td>
                                                <strong><?= e($appointment['patient_name']) ?></strong><br>
                                                <small class="text-muted"><?= e($appointment['patient_email']) ?></small>
                                            </td>
                                            <td>
                                                <?= e($appointment['doctor_name']) ?><br>
                                                <small class="text-muted"><?= e($appointment['department_name']) ?></small>
                                            </td>
                                            <td>
                                                <strong><?= formatDate($appointment['appointment_date']) ?></strong><br>
                                                <small class="text-muted">
                                                    <?= formatTime($appointment['start_time']) ?>
                                                    – <?= formatTime($appointment['end_time']) ?>
                                                </small>
                                            </td>
                                            <td class="reason-cell"><?= e($appointment['reason'] ?: 'No reason provided') ?></td>
                                            <td>
                                                <span class="badge <?= statusBadge($appointment['status']) ?>">
                                                    <?= e(ucfirst($appointment['status'])) ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <form method="post" action="appointments.php?<?= e(http_build_query(array_filter([
                                                    'status' => $status,
                                                    'date' => $date,
                                                    'patient_id' => $patientId > 0 ? $patientId : '',
                                                ]))) ?>" class="status-update-form">
                                                    <?= csrfField() ?>
                                                    <input type="hidden" name="appointment_id" value="<?= (int) $appointment['id'] ?>">
                                                    <select name="status" class="form-select form-select-sm"
                                                            aria-label="Status for appointment <?= (int) $appointment['id'] ?>">
                                                        <?php foreach (APPOINTMENT_STATUSES as $option): ?>
                                                            <option value="<?= e($option) ?>"
                                                                <?= $appointment['status'] === $option ? 'selected' : '' ?>>
                                                                <?= e(ucfirst($option)) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" name="update_appointment" value="1"
                                                            class="btn-mini view">Update</button>
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
