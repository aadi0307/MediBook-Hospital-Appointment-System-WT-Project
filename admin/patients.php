<?php
/**
 * Admin patient directory.
 * Read-only list so existing patient records and appointments stay intact.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('admin', '../');

$db     = tryGetDB();
$search = trim((string) ($_GET['q'] ?? ''));
$patients = getPatients($db, $search);

$pageTitle = 'Patient Directory | MediBook Admin';
$baseUrl   = '../';
$activeNav = 'patients';
$activeAdmin = 'patients';
$extraCss  = ['assets/css/dashboard.css'];

require __DIR__ . '/../includes/header.php';
?>

<section class="dashboard-hero role-admin compact-hero">
    <div class="container">
        <span class="dashboard-kicker">Administration</span>
        <h1>Patient directory</h1>
        <p>Search registered patients and review their appointment activity.</p>
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
                            <span class="section-label">Registered patients</span>
                            <h2>Patient list</h2>
                        </div>
                        <span class="panel-hint"><?= count($patients) ?> result<?= count($patients) === 1 ? '' : 's' ?></span>
                    </div>

                    <form method="get" action="patients.php" class="admin-search">
                        <label for="q" class="visually-hidden">Search patients</label>
                        <input type="search" class="form-control" id="q" name="q"
                               value="<?= e($search) ?>" placeholder="Search by name or email">
                        <button type="submit" class="btn-primary-custom">Search</button>
                        <?php if ($search !== ''): ?>
                            <a href="patients.php" class="btn-outline-custom">Clear</a>
                        <?php endif; ?>
                    </form>

                    <?php if (!$patients): ?>
                        <div class="empty-state compact">
                            <span>👥</span>
                            <strong>No patients found</strong>
                            <p><?= $search !== '' ? 'Try a different name or email.' : 'Patient registrations will appear here.' ?></p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-soft align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Contact</th>
                                        <th>Joined</th>
                                        <th>Visits</th>
                                        <th>Last appointment</th>
                                        <th class="text-end">History</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($patients as $patient): ?>
                                        <tr>
                                            <td>
                                                <strong><?= e($patient['name']) ?></strong><br>
                                                <small class="text-muted">
                                                    <?= (int) $patient['is_active'] === 1 ? 'Active account' : 'Inactive account' ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?= e($patient['email']) ?><br>
                                                <small class="text-muted"><?= e($patient['phone'] ?: 'No phone') ?></small>
                                            </td>
                                            <td><?= formatDate(substr($patient['created_at'], 0, 10)) ?></td>
                                            <td><strong><?= (int) $patient['appointment_count'] ?></strong></td>
                                            <td>
                                                <?= $patient['last_appointment']
                                                    ? formatDate($patient['last_appointment'])
                                                    : '<span class="text-muted">None</span>' ?>
                                            </td>
                                            <td class="text-end">
                                                <a class="btn-mini view"
                                                   href="appointments.php?patient_id=<?= (int) $patient['id'] ?>">
                                                    View visits
                                                </a>
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
