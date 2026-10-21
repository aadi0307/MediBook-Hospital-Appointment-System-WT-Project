<?php
/**
 * My Appointments (patient area) - list + cancel own appointments.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin('../');

$db = tryGetDB();

/* ---------- Cancel an appointment (POST) ---------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $cancelId = (int) $_POST['cancel_id'];

    if (!$db) {
        flashSet('danger', 'The database is unavailable, so the appointment could not be cancelled.');
    } else {
        // Only the logged-in patient's own, still active appointments
        $sql = 'UPDATE appointments
                SET status = \'cancelled\'
                WHERE id = :id
                  AND patient_id = :patient
                  AND status IN (\'pending\', \'confirmed\')
                  AND appointment_date >= :today';

        $statement = $db->prepare($sql);
        $statement->execute([
            ':id'     => $cancelId,
            ':patient' => currentUser()['id'],
            ':today'  => date('Y-m-d'),
        ]);

        flashSet(
            $statement->rowCount() > 0 ? 'success' : 'warning',
            $statement->rowCount() > 0
                ? 'Your appointment has been cancelled.'
                : 'That appointment can no longer be cancelled.'
        );
    }

    header('Location: my-appointments.php');
    exit;
}

$appointments = getPatientAppointments($db, (int) currentUser()['id']);

$today    = date('Y-m-d');
$upcoming = [];
$history  = [];

foreach ($appointments as $appointment) {
    $active = in_array($appointment['status'], ['pending', 'confirmed'], true);
    if ($active && $appointment['appointment_date'] >= $today) {
        $upcoming[] = $appointment;
    } else {
        $history[] = $appointment;
    }
}

/** Render one appointment row (used for both tables). */
$row = function (array $appointment) {
    $canCancel = in_array($appointment['status'], ['pending', 'confirmed'], true)
        && $appointment['appointment_date'] >= date('Y-m-d');
    ?>
    <tr>
        <td>
            <strong><?= e($appointment['doctor_name']) ?></strong><br>
            <small class="text-muted"><?= e($appointment['department_name']) ?></small>
        </td>
        <td><?= formatDate($appointment['appointment_date']) ?></td>
        <td>
            <?= formatTime($appointment['start_time']) ?>
            - <?= formatTime($appointment['end_time']) ?>
        </td>
        <td><?= e(ucfirst($appointment['status'])) ?></td>
        <td class="text-end">
            <?php if ($canCancel): ?>
            <form method="post" action="my-appointments.php" class="d-inline"
                  onsubmit="return confirm('Cancel this appointment?');">
                <input type="hidden" name="cancel_id" value="<?= (int) $appointment['id'] ?>">
                <button type="submit" class="btn-danger-soft">Cancel</button>
            </form>
            <?php else: ?>
            <span class="text-muted small">-</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php
};

$pageTitle = 'My Appointments | MediBook';
$baseUrl   = '../';
$activeNav = 'appointments';

require __DIR__ . '/../includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <span class="section-label">Patient area</span>
        <h1>My Appointments</h1>
        <p>Hi <?= e(currentUser()['name']) ?> - here are your bookings.</p>
    </div>
</section>

<section class="section">
    <div class="container">

        <?php if (!$db): ?>
            <?= dbUnavailableNotice() ?>
        <?php endif; ?>

        <?php if ($db && !$appointments): ?>
            <div class="text-center py-4">
                <div class="avatar-circle mx-auto mb-3" style="border-radius:18px">📅</div>
                <h2 style="font-size:20px">No appointments yet</h2>
                <p class="text-muted mb-3">Book your first visit in a few clicks.</p>
                <a href="appointment.php" class="btn-primary-custom">Book an Appointment →</a>
            </div>
        <?php else: ?>

            <!-- Upcoming -->
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 style="font-size:20px;font-weight:800;margin:0">Upcoming</h2>
                <a href="appointment.php" class="btn-primary-custom">+ New Booking</a>
            </div>

            <div class="data-table-wrap mb-5">
                <?php if (!$upcoming): ?>
                    <p class="text-muted text-center py-3 mb-0">
                        No upcoming appointments.
                    </p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-soft align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Doctor</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcoming as $appointment) $row($appointment); ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <!-- History -->
            <h2 style="font-size:20px;font-weight:800" class="mb-3">History</h2>
            <div class="data-table-wrap">
                <?php if (!$history): ?>
                    <p class="text-muted text-center py-3 mb-0">
                        No past appointments.
                    </p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-soft align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Doctor</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $appointment) $row($appointment); ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
