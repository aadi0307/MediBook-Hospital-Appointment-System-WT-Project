<?php
/**
 * Doctor dashboard.
 *
 * A doctor only sees appointments whose doctor_id belongs to their own
 * users.id. The link between the account and the doctors table is through
 * doctors.user_id, so no appointment id from the URL can widen the scope.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('doctor', '../');

$db     = tryGetDB();
$user   = currentUser();
$doctor = getDoctorByUserId($db, (int) $user['id']);

$stats = $doctor
    ? getDoctorAppointmentStats($db, (int) $doctor['id'])
    : ['today' => 0, 'pending' => 0, 'upcoming' => 0, 'patients' => 0];

$todayAppointments = $doctor
    ? getDoctorAppointmentsForDate($db, (int) $doctor['id'], date('Y-m-d'))
    : [];

$upcomingAppointments = $doctor
    ? getDoctorUpcomingAppointments($db, (int) $doctor['id'], 6)
    : [];

$pageTitle = 'Doctor Dashboard | MediBook';
$baseUrl   = '../';
$activeNav = 'dashboard';
$extraCss  = ['assets/css/dashboard.css'];

require __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-hero role-doctor">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="dashboard-kicker">Doctor workspace</span>
                <h1>Good <?= date('G') < 12 ? 'morning' : (date('G') < 17 ? 'afternoon' : 'evening') ?>,
                    <?= e($doctor['name'] ?? $user['name']) ?>.</h1>
                <p>
                    Here is your clinical day at a glance. Review today's
                    patients, confirm upcoming visits and keep appointment
                    statuses up to date.
                </p>
                <div class="hero-action-row">
                    <a href="appointments.php" class="btn-primary-custom">Manage Appointments →</a>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="doctor-profile-card">
                    <?php if ($doctor): ?>
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-circle"><?= e(initials($doctor['name'])) ?></div>
                            <div>
                                <strong><?= e($doctor['name']) ?></strong>
                                <span><?= e($doctor['specialization']) ?></span>
                            </div>
                        </div>
                        <div class="doctor-profile-meta">
                            <span><?= e($doctor['department_name']) ?></span>
                            <span><?= e($doctor['qualification']) ?></span>
                        </div>
                    <?php else: ?>
                        <strong>Doctor profile not found</strong>
                        <span>Your user account is not linked to a doctor record.</span>
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

        <?php if ($db && !$doctor): ?>
            <div class="alert alert-warning">
                The account is marked as a doctor, but no matching row exists in
                the <code>doctors</code> table. Please ask an administrator to
                complete the doctor profile.
            </div>
        <?php endif; ?>

        <div class="stat-grid">
            <div class="stat-card">
                <span class="stat-icon">📅</span>
                <div><strong><?= $stats['today'] ?></strong><small>Appointments today</small></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">⏳</span>
                <div><strong><?= $stats['pending'] ?></strong><small>Awaiting confirmation</small></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">🗓️</span>
                <div><strong><?= $stats['upcoming'] ?></strong><small>Upcoming visits</small></div>
            </div>
            <div class="stat-card">
                <span class="stat-icon">👤</span>
                <div><strong><?= $stats['patients'] ?></strong><small>Patients in your care</small></div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="dashboard-panel h-100">
                    <div class="panel-heading">
                        <div>
                            <span class="section-label"><?= date('l, d M Y') ?></span>
                            <h2>Today's schedule</h2>
                        </div>
                        <a href="appointments.php">Full list →</a>
                    </div>

                    <?php if (!$doctor): ?>
                        <div class="empty-state">
                            <span>⚠️</span>
                            <strong>No doctor profile linked</strong>
                            <p>An administrator needs to link your account to a doctor record.</p>
                        </div>
                    <?php elseif (!$todayAppointments): ?>
                        <div class="empty-state">
                            <span>☕</span>
                            <strong>No appointments today</strong>
                            <p>Your upcoming confirmed and pending visits are listed beside this panel.</p>
                        </div>
                    <?php else: ?>
                        <div class="doctor-schedule">
                            <?php foreach ($todayAppointments as $appointment): ?>
                                <div class="schedule-item">
                                    <div class="schedule-time">
                                        <strong><?= e(date('g:i', strtotime($appointment['start_time']))) ?></strong>
                                        <small><?= e(date('A', strtotime($appointment['start_time']))) ?></small>
                                    </div>
                                    <div class="schedule-body">
                                        <div class="d-flex flex-wrap align-items-center gap-2">
                                            <strong><?= e($appointment['patient_name']) ?></strong>
                                            <span class="badge <?= statusBadge($appointment['status']) ?>">
                                                <?= e(ucfirst($appointment['status'])) ?>
                                            </span>
                                        </div>
                                        <span><?= e($appointment['reason'] ?: 'No reason provided') ?></span>
                                        <small class="patient-contact">
                                            <?= e($appointment['patient_phone'] ?: $appointment['patient_email']) ?>
                                        </small>
                                    </div>
                                    <div class="schedule-side">
                                        <small><?= formatTime($appointment['end_time']) ?> ends</small>
                                    </div>
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
                            <span class="section-label">Next up</span>
                            <h2>Upcoming visits</h2>
                        </div>
                    </div>

                    <?php if (!$upcomingAppointments): ?>
                        <div class="empty-state compact">
                            <span>🗓️</span>
                            <strong>Nothing scheduled ahead</strong>
                            <p>New patient bookings will appear here.</p>
                        </div>
                    <?php else: ?>
                        <div class="upcoming-stack">
                            <?php foreach ($upcomingAppointments as $appointment): ?>
                                <div class="upcoming-card">
                                    <div class="upcoming-date">
                                        <strong><?= e(date('d', strtotime($appointment['appointment_date']))) ?></strong>
                                        <small><?= e(date('M', strtotime($appointment['appointment_date']))) ?></small>
                                    </div>
                                    <div>
                                        <strong><?= e($appointment['patient_name']) ?></strong>
                                        <span><?= formatTime($appointment['start_time']) ?>
                                            · <?= e(ucfirst($appointment['status'])) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
