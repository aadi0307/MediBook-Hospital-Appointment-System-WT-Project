<?php
/**
 * Doctor appointment manager.
 *
 * Every read and write includes doctor_id = the logged-in doctor's own id.
 * A doctor therefore cannot view or change another doctor's appointments.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('doctor', '../');

$db     = tryGetDB();
$user   = currentUser();
$doctor = getDoctorByUserId($db, (int) $user['id']);

$status = (string) ($_GET['status'] ?? '');
if (!in_array($status, APPOINTMENT_STATUSES, true)) {
    $status = '';
}

/* ---------- Update status (POST, doctor's own appointments only) ---------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $appointmentId = (int) ($_POST['appointment_id'] ?? 0);
    $newStatus     = (string) ($_POST['status'] ?? '');
    $allowedTargets = ['confirmed', 'completed', 'cancelled'];

    if (!csrfIsValid()) {
        flashSet('danger', 'Your session expired. Please try again.');
    } elseif (!$doctor) {
        flashSet('danger', 'No doctor profile is linked to this account.');
    } elseif ($appointmentId < 1 || !in_array($newStatus, $allowedTargets, true)) {
        flashSet('danger', 'That status change is not valid.');
    } elseif (!$db) {
        flashSet('danger', 'The database is unavailable.');
    } else {
        $sql = 'UPDATE appointments
                SET status = :status
                WHERE id = :id
                  AND doctor_id = :doctor
                  AND status IN (\'pending\', \'confirmed\')';
        $statement = $db->prepare($sql);
        $statement->execute([
            ':status' => $newStatus,
            ':id'     => $appointmentId,
            ':doctor' => (int) $doctor['id'],
        ]);

        flashSet(
            $statement->rowCount() > 0 ? 'success' : 'warning',
            $statement->rowCount() > 0
                ? 'Appointment status updated to ' . $newStatus . '.'
                : 'That appointment could not be updated.'
        );
    }

    $query = $status !== '' ? '?status=' . urlencode($status) : '';
    header('Location: appointments.php' . $query);
    exit;
}

$appointments = $doctor
    ? getDoctorAppointments($db, (int) $doctor['id'], $status)
    : [];

$pageTitle = 'My Appointments | MediBook Doctor';
$baseUrl   = '../';
$activeNav = 'appointments';
$extraCss  = ['assets/css/dashboard.css'];

require __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-hero role-doctor compact-hero">
    <div class="container">
        <span class="dashboard-kicker">Doctor workspace</span>
        <h1>My Appointments</h1>
        <p>Review patient details and update the status of your own visits.</p>
    </div>
</section>

<section class="dashboard-section">
    <div class="container">

        <?php if (!$db): ?>
            <?= dbUnavailableNotice() ?>
        <?php endif; ?>

        <?php if (!$doctor): ?>
            <div class="dashboard-panel">
                <div class="empty-state">
                    <span>⚠️</span>
                    <strong>Doctor profile missing</strong>
                    <p>Ask an administrator to link your account to the doctors table.</p>
                    <a href="dashboard.php" class="btn-outline-custom">Back to dashboard</a>
                </div>
            </div>
        <?php else: ?>

            <div class="filter-pills" aria-label="Filter appointments by status">
                <a class="filter-pill <?= $status === '' ? 'active' : '' ?>" href="appointments.php">All</a>
                <?php foreach (APPOINTMENT_STATUSES as $option): ?>
                    <a class="filter-pill <?= $status === $option ? 'active' : '' ?>"
                       href="appointments.php?status=<?= urlencode($option) ?>">
                        <?= e(ucfirst($option)) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="dashboard-panel p-0">
                <?php if (!$appointments): ?>
                    <div class="empty-state">
                        <span>🗓️</span>
                        <strong>No appointments found</strong>
                        <p>There are no appointments in this filter yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-soft align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Date &amp; time</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th class="text-end">Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td>
                                            <strong><?= e($appointment['patient_name']) ?></strong><br>
                                            <small class="text-muted"><?= e($appointment['patient_email']) ?></small><br>
                                            <small class="text-muted"><?= e($appointment['patient_phone'] ?: 'No phone') ?></small>
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
                                        <td>
                                            <div class="status-actions justify-content-end">
                                                <?php if ($appointment['status'] === 'pending'): ?>
                                                    <?php $formId = 'confirm-' . (int) $appointment['id']; ?>
                                                    <form method="post" action="appointments.php?status=<?= urlencode($status) ?>" class="d-inline">
                                                        <?= csrfField() ?>
                                                        <input type="hidden" name="appointment_id" value="<?= (int) $appointment['id'] ?>">
                                                        <input type="hidden" name="status" value="confirmed">
                                                        <button type="submit" name="update_status" value="1" class="btn-mini confirm">Confirm</button>
                                                    </form>
                                                <?php endif; ?>

                                                <?php if (in_array($appointment['status'], ['pending', 'confirmed'], true)): ?>
                                                    <?php if ($appointment['status'] === 'confirmed'): ?>
                                                        <form method="post" action="appointments.php?status=<?= urlencode($status) ?>" class="d-inline">
                                                            <?= csrfField() ?>
                                                            <input type="hidden" name="appointment_id" value="<?= (int) $appointment['id'] ?>">
                                                            <input type="hidden" name="status" value="completed">
                                                            <button type="submit" name="update_status" value="1" class="btn-mini complete">Complete</button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <form method="post" action="appointments.php?status=<?= urlencode($status) ?>" class="d-inline"
                                                          onsubmit="return confirm('Cancel this appointment?');">
                                                        <?= csrfField() ?>
                                                        <input type="hidden" name="appointment_id" value="<?= (int) $appointment['id'] ?>">
                                                        <input type="hidden" name="status" value="cancelled">
                                                        <button type="submit" name="update_status" value="1" class="btn-mini cancel">Cancel</button>
                                                    </form>
                                                <?php endif; ?>

                                                <?php if (in_array($appointment['status'], ['completed', 'cancelled'], true)): ?>
                                                    <span class="text-muted small">Closed</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <p class="text-muted mt-3 mb-0" style="font-size:13px">
                Showing <?= count($appointments) ?> appointment<?= count($appointments) === 1 ? '' : 's' ?>
                for <?= e($doctor['name']) ?>.
            </p>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
