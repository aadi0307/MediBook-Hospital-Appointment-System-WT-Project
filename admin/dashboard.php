<?php
/**
 * Admin dashboard: hospital-wide summary and recent appointment activity.
 * Access is enforced on the server with requireRole('admin').
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin', '../');

$db      = tryGetDB();
$user    = currentUser();
$summary = getAdminSummary($db);
$recent  = getAllAppointments($db, '', '', 0, 6);

$pageTitle = 'Admin Dashboard | MediBook';
$baseUrl   = '../';
$activeNav = 'dashboard';
$activeAdmin = 'dashboard';
$extraCss  = ['assets/css/dashboard.css'];

require __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-hero role-admin">
    <div class="container">
        <div class="row align-items-end g-4">
            <div class="col-lg-8">
                <span class="dashboard-kicker">Administration · <?= date('l, d M Y') ?></span>
                <h1>Hospital control centre</h1>
                <p>
                    Keep doctors, departments, schedules and appointments
                    running smoothly from one place.
                </p>
            </div>
            <div class="col-lg-4">
                <div class="admin-hero-note">
                    <span>Signed in as</span>
                    <strong><?= e($user['name']) ?></strong>
                    <small>Full administrative access</small>
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

        <div class="admin-layout">
            <?php require __DIR__ . '/../includes/admin_nav.php'; ?>

            <div class="admin-main">
                <?php if ($summary['pending'] > 0): ?>
                    <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <span>
                            <strong><?= $summary['pending'] ?></strong>
                            appointment<?= $summary['pending'] === 1 ? '' : 's' ?>
                            still need confirmation.
                        </span>
                        <a href="appointments.php?status=pending" class="btn-outline-custom">Review pending</a>
                    </div>
                <?php endif; ?>

                <div class="stat-grid admin-stat-grid">
                    <div class="stat-card">
                        <span class="stat-icon">👤</span>
                        <div><strong><?= $summary['patients'] ?></strong><small>Active patients</small></div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-icon">🩺</span>
                        <div><strong><?= $summary['doctors'] ?></strong><small>Active doctors</small></div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-icon">🏥</span>
                        <div><strong><?= $summary['departments'] ?></strong><small>Departments</small></div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-icon">📅</span>
                        <div><strong><?= $summary['appointments'] ?></strong><small>Total appointments</small></div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-xl-8">
                        <div class="dashboard-panel">
                            <div class="panel-heading">
                                <div>
                                    <span class="section-label">Latest activity</span>
                                    <h2>Recent appointments</h2>
                                </div>
                                <a href="appointments.php">Manage all →</a>
                            </div>

                            <?php if (!$recent): ?>
                                <div class="empty-state compact">
                                    <span>📅</span>
                                    <strong>No appointments yet</strong>
                                    <p>New patient bookings will appear here.</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-soft align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Patient</th>
                                                <th>Doctor</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent as $appointment): ?>
                                                <tr>
                                                    <td><strong><?= e($appointment['patient_name']) ?></strong></td>
                                                    <td>
                                                        <?= e($appointment['doctor_name']) ?><br>
                                                        <small class="text-muted"><?= e($appointment['department_name']) ?></small>
                                                    </td>
                                                    <td>
                                                        <?= formatDate($appointment['appointment_date']) ?><br>
                                                        <small class="text-muted"><?= formatTime($appointment['start_time']) ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?= statusBadge($appointment['status']) ?>">
                                                            <?= e(ucfirst($appointment['status'])) ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="dashboard-panel h-100">
                            <div class="panel-heading">
                                <div>
                                    <span class="section-label">Today</span>
                                    <h2>Daily snapshot</h2>
                                </div>
                            </div>

                            <div class="admin-snapshot">
                                <div>
                                    <strong><?= $summary['today'] ?></strong>
                                    <span>Appointments today</span>
                                </div>
                                <div>
                                    <strong><?= $summary['upcoming'] ?></strong>
                                    <span>Active upcoming</span>
                                </div>
                                <div>
                                    <strong><?= $summary['pending'] ?></strong>
                                    <span>Pending approval</span>
                                </div>
                            </div>

                            <div class="admin-quick-links">
                                <a href="doctors.php"><span>✚</span> Add or manage doctors</a>
                                <a href="departments.php"><span>🏥</span> Manage departments</a>
                                <a href="schedules.php"><span>🕘</span> Update schedules</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="quick-grid admin-quick-grid">
                    <a class="quick-card" href="doctors.php">
                        <span>🩺</span>
                        <div><strong>Doctor management</strong><small>Create accounts and pause availability</small></div>
                        <b>→</b>
                    </a>
                    <a class="quick-card" href="patients.php">
                        <span>👥</span>
                        <div><strong>Patient directory</strong><small>Search patients and see visit totals</small></div>
                        <b>→</b>
                    </a>
                    <a class="quick-card" href="appointments.php">
                        <span>📋</span>
                        <div><strong>Appointment control</strong><small>Filter and update booking statuses</small></div>
                        <b>→</b>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
