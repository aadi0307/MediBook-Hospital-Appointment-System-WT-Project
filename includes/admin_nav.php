<?php
/**
 * Shared admin sidebar.
 * Set $activeAdmin before including: dashboard, doctors, patients,
 * departments, schedules or appointments.
 */

$activeAdmin = $activeAdmin ?? '';
$adminUser   = currentUser();

$adminLinks = [
    'dashboard'   => ['label' => 'Overview',     'icon' => '▦', 'href' => 'dashboard.php'],
    'doctors'     => ['label' => 'Doctors',      'icon' => '✚', 'href' => 'doctors.php'],
    'patients'    => ['label' => 'Patients',     'icon' => '👤', 'href' => 'patients.php'],
    'departments' => ['label' => 'Departments',  'icon' => '🏥', 'href' => 'departments.php'],
    'schedules'   => ['label' => 'Schedules',    'icon' => '🕘', 'href' => 'schedules.php'],
    'appointments'=> ['label' => 'Appointments', 'icon' => '📅', 'href' => 'appointments.php'],
];
?>
<aside class="admin-sidebar">
    <div class="admin-sidebar-profile">
        <div class="avatar-circle"><?= e(initials($adminUser['name'] ?? 'Admin')) ?></div>
        <div>
            <strong><?= e($adminUser['name'] ?? 'Administrator') ?></strong>
            <small>Hospital administrator</small>
        </div>
    </div>

    <nav class="admin-nav" aria-label="Admin management">
        <?php foreach ($adminLinks as $key => $link): ?>
            <a href="<?= e($link['href']) ?>"
               class="<?= $activeAdmin === $key ? 'active' : '' ?>"
               <?= $activeAdmin === $key ? 'aria-current="page"' : '' ?>>
                <span><?= $link['icon'] ?></span>
                <?= e($link['label']) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <a class="admin-public-link" href="../index.php">← View public website</a>
</aside>
