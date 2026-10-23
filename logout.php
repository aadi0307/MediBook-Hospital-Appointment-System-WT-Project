<?php
/**
 * Secure logout.
 *
 * Logging out changes state, so it is handled with POST + a CSRF token.
 * The whole session is destroyed and the session cookie is removed.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!csrfIsValid()) {
    flashSet('danger', 'Your session expired. Please use the logout button again.');
    header('Location: index.php');
    exit;
}

logoutUser();

// Start a fresh, empty session only to show the goodbye message.
session_start();
session_regenerate_id(true);
flashSet('info', 'You have been logged out securely.');

header('Location: index.php');
exit;
