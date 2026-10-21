<?php
/**
 * Logout - clears the session and returns to the homepage.
 * (Lives in /patient, so links back to the root use ../)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

logoutUser();

flashSet('info', 'You have been logged out.');
header('Location: ../index.php');
exit;
