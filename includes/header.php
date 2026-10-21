<?php
/**
 * Shared page header: HTML head + navigation bar.
 *
 * Every page must define $pageTitle before including this file.
 * Optional variables a page may set before including:
 *   $baseUrl    - '' at the project root, '../' inside /patient
 *   $activeNav  - 'home' | 'doctors' | 'about' | 'contact' | 'appointments' | 'profile'
 *   $extraCss   - array of extra stylesheet paths, e.g. ['assets/css/home.css']
 */

$baseUrl   = $baseUrl ?? '';
$activeNav = $activeNav ?? '';
$extraCss  = $extraCss ?? [];
$user      = currentUser();

/** Helper used below: prints 'active' on the current nav item. */
$navClass = function (string $name) use ($activeNav): string {
    return $activeNav === $name ? 'nav-link active' : 'nav-link';
};
$navCurrent = function (string $name) use ($activeNav): string {
    return $activeNav === $name ? ' aria-current="page"' : '';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MediBook hospital appointment system - find doctors and manage appointments online.">
    <title><?= e($pageTitle) ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Shared project styles -->
    <link href="<?= $baseUrl ?>assets/css/style.css" rel="stylesheet">
<?php foreach ($extraCss as $cssFile): ?>
    <link href="<?= $baseUrl . e($cssFile) ?>" rel="stylesheet">
<?php endforeach; ?>
</head>

<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= $baseUrl ?>index.php">
            <span class="brand-icon">+</span>MediBook<span class="brand-dot">.</span>
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#mainNavigation"
                aria-controls="mainNavigation" aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavigation">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="<?= $navClass('home') ?>" href="<?= $baseUrl ?>index.php"<?= $navCurrent('home') ?>>Home</a>
                </li>
                <li class="nav-item">
                    <a class="<?= $navClass('doctors') ?>" href="<?= $baseUrl ?>doctors.php"<?= $navCurrent('doctors') ?>>Doctors</a>
                </li>
                <li class="nav-item">
                    <a class="<?= $navClass('about') ?>" href="<?= $baseUrl ?>about.php"<?= $navCurrent('about') ?>>About</a>
                </li>
                <li class="nav-item">
                    <a class="<?= $navClass('contact') ?>" href="<?= $baseUrl ?>contact.php"<?= $navCurrent('contact') ?>>Contact</a>
                </li>

<?php if ($user): ?>
                <li class="nav-item">
                    <a class="<?= $navClass('appointments') ?>" href="<?= $baseUrl ?>patient/my-appointments.php"<?= $navCurrent('appointments') ?>>My Appointments</a>
                </li>
                <li class="nav-item">
                    <a class="<?= $navClass('profile') ?>" href="<?= $baseUrl ?>patient/profile.php"<?= $navCurrent('profile') ?>>Profile</a>
                </li>
                <li class="nav-item ms-lg-2 mt-3 mt-lg-0">
                    <a class="btn-primary-custom w-100" href="<?= $baseUrl ?>patient/appointment.php">Book Appointment</a>
                </li>
                <li class="nav-item ms-lg-3 mt-3 mt-lg-0">
                    <a class="nav-link logout-link" href="<?= $baseUrl ?>patient/logout.php">Logout</a>
                </li>
<?php else: ?>
                <li class="nav-item">
                    <a class="<?= $navClass('login') ?>" href="<?= $baseUrl ?>login.php"<?= $navCurrent('login') ?>>Login</a>
                </li>
                <li class="nav-item ms-lg-2 mt-3 mt-lg-0">
                    <a class="btn-primary-custom w-100" href="<?= $baseUrl ?>register.php">Register</a>
                </li>
<?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if ($flash = flashGet()): ?>
<div class="container mt-3">
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
<?php endif; ?>

<main>
