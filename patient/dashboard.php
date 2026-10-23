<?php
/**
 * Patient dashboard.
 *
 * Shows the patient's next visit, upcoming/history summaries, quick actions
 * and a small doctor browser. Booking/cancelling still uses the existing
 * patient/appointment.php and patient/my-appointments.php pages.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('patient', '../');

$db   = tryGetDB();
$user = currentUser();
$appointments = getPatientAppointments($db, (int) $user['id']);

$today    = date('Y-m-d');
$upcoming = [];
$history  = [];
$completedCount = 0;

foreach ($appointments as $appointment) {
    $active = in_array($appointment['status'], ['pending', 'confirmed'], true);

    if ($appointment['status'] === 'completed') {
        $completedCount++;
    }

    if ($active && $appointment['appointment_date'] >= $today) {
        $upcoming[] = $appointment;
    } else {
        $history[] = $appointment;
    }
}

usort($upcoming, function (array $a, array $b): int {
    return [$a['appointment_date'], $a['start_time']] <=> [$b['appointment_date'], $b['start_time']];
});

$nextAppointment = $upcoming[0] ?? null;
$doctors         = getDoctors($db);
$featuredDoctors = array_slice($doctors, 0, 3);

$pageTitle = 'Patient Dashboard | MediBook';
$baseUrl   = '../';
$activeNav = 'dashboard';
$extraCss  = ['assets/css/dashboard.css'];

require __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-hero role-patient">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="dashboard-kicker">Patient workspace</span>
                <h1>Hello, <?= e(explode(' ', $user['name'])[0]) ?>.</h1>
                <p>
                    Your health journey is in one place. Review visits, book a
                    doctor and keep your profile up to date.
                </p>
                <div class="hero-action-row">
                    <a href="appointment.php" class="btn-primary-custom">Book an Appointment →</a>
                    <a href="../doctors.php" class="btn-outline-custom">Browse Doctors</a>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="next-visit-card">
                    <span class="next-visit-label">Next appointment</span>
                    <?php if ($nextAppointment): ?>
                        <strong><?= e($nextAppointment['doctor_name']) ?></strong>
                        <span><?= e($nextAppointment['department_name']) ?></span>
                        <div class="next-visit-date">
                            <?= formatDate($nextAppointment['appointment_date']) ?>
                            · <?= formatTime($nextAppointment['start_time']) ?>
                        </div>
                        <span class="badge <?= statusBadge($nextAppointment['status']) ?>">
                            <?= e(ucfirst($nextAppointment['status'])) ?>
                        </span>
                    <?php else: ?>
                        <strong>No upcoming visit</strong>
                        <span>Choose a doctor and a time that suits you.</span>
                        <div class="next-visit-date">You are all caught up.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="dashboard-section">
    <div class="container">

        <?php if (!$db): ?>
            <?= dbUnavailableNotice() ?>
        <?php endif; ?>

        <div class="stat-grid">
            <div class="stat-card">
                <span class="stat-icon">📅</span>
                <div><strong><?= count($upcoming) ?></strong><small>Upcoming visits</small></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">✓</span>
                <div><strong><?= $completedCount ?></strong><small>Completed visits</small></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">🩺</span>
                <div><strong><?= count($doctors) ?></strong><small>Doctors available</small></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">📋</span>
                <div><strong><?= count($appointments) ?></strong><small>Total bookings</small></div>
            </div>
        </div>

        <div class="quick-grid">
            <a class="quick-card" href="../doctors.php">
                <span>🔎</span>
                <div><strong>Find a doctor</strong><small>Browse departments and specialists</small></div>
                <b>→</b>
            </a>
            <a class="quick-card" href="my-appointments.php">
                <span>🗓️</span>
                <div><strong>My appointments</strong><small>Review or cancel a booking</small></div>
                <b>→</b>
            </a>
            <a class="quick-card" href="profile.php">
                <span>👤</span>
                <div><strong>My profile</strong><small>Update contact details and password</small></div>
                <b>→</b>
            </a>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="dashboard-panel">
                    <div class="panel-heading">
                        <div>
                            <span class="section-label">Coming up</span>
                            <h2>Upcoming appointments</h2>
                        </div>
                        <a href="my-appointments.php">View all →</a>
                    </div>

                    <?php if (!$upcoming): ?>
                        <div class="empty-state">
                            <span>📅</span>
                            <strong>No upcoming appointments</strong>
                            <p>Book a visit with one of our doctors when you need care.</p>
                            <a href="appointment.php" class="btn-primary-custom">Book now</a>
                        </div>
                    <?php else: ?>
                        <div class="dashboard-list">
                            <?php foreach (array_slice($upcoming, 0, 4) as $appointment): ?>
                                <div class="appointment-row">
                                    <div class="appointment-date-box">
                                        <strong><?= e(date('d', strtotime($appointment['appointment_date']))) ?></strong>
                                        <small><?= e(date('M', strtotime($appointment['appointment_date']))) ?></small>
                                    </div>
                                    <div class="appointment-main">
                                        <strong><?= e($appointment['doctor_name']) ?></strong>
                                        <span><?= e($appointment['department_name']) ?>
                                            · <?= formatTime($appointment['start_time']) ?></span>
                                    </div>
                                    <span class="badge <?= statusBadge($appointment['status']) ?>">
                                        <?= e(ucfirst($appointment['status'])) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="dashboard-panel h-100">
                    <div class="panel-heading">
                        <div>
                            <span class="section-label">Recent history</span>
                            <h2>Past visits</h2>
                        </div>
                    </div>

                    <?php if (!$history): ?>
                        <div class="empty-state compact">
                            <span>🗂️</span>
                            <strong>No past visits yet</strong>
                            <p>Your completed and cancelled appointments will appear here.</p>
                        </div>
                    <?php else: ?>
                        <div class="mini-history">
                            <?php foreach (array_slice($history, 0, 4) as $appointment): ?>
                                <div class="mini-history-item">
                                    <span class="history-dot"></span>
                                    <div>
                                        <strong><?= e($appointment['doctor_name']) ?></strong>
                                        <small><?= formatDate($appointment['appointment_date']) ?>
                                            · <?= e(ucfirst($appointment['status'])) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="panel-heading doctors-heading">
            <div>
                <span class="section-label">Care team</span>
                <h2>Doctors you can book</h2>
            </div>
            <a href="../doctors.php">See all doctors →</a>
        </div>

        <?php if (!$featuredDoctors): ?>
            <div class="dashboard-panel">
                <p class="text-muted mb-0">No doctors are available right now.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($featuredDoctors as $doctor): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="doctor-mini-card">
                            <div class="doctor-mini-top">
                                <div class="avatar-circle"><?= e(initials($doctor['name'])) ?></div>
                                <div>
                                    <strong><?= e($doctor['name']) ?></strong>
                                    <span><?= e($doctor['specialization']) ?></span>
                                </div>
                            </div>
                            <div class="doctor-mini-meta">
                                <span><?= e($doctor['department_name']) ?></span>
                                <span><?= (int) $doctor['experience_years'] ?> yrs experience</span>
                            </div>
                            <a class="btn-outline-custom w-100"
                               href="appointment.php?doctor_id=<?= (int) $doctor['id'] ?>">
                                Book with this doctor
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
