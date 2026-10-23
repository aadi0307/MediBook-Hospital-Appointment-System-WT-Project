<?php
/**
 * Legacy patient logout URL.
 *
 * Logging out is a state-changing action, so this page shows a confirmation
 * form and posts to the shared /logout.php handler. It also keeps old links
 * and bookmarks working.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole('patient', '../');

$pageTitle = 'Logout | MediBook';
$baseUrl   = '../';
$activeNav = '';

require __DIR__ . '/../includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="auth-card text-center">
            <span class="section-label">MediBook account</span>
            <h1>Log out?</h1>
            <p class="auth-subtitle">Your session will be cleared securely.</p>

            <form method="post" action="../logout.php">
                <?= csrfField() ?>
                <button type="submit" class="btn-primary-custom w-100">
                    Yes, log me out
                </button>
            </form>

            <p class="auth-links">
                <a href="dashboard.php">Return to dashboard</a>
            </p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
