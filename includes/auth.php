<?php
/**
 * Simple session-based login helpers used by every page.
 *
 * A logged-in user is stored in $_SESSION['user'] as a small array:
 * ['id' => ..., 'name' => ..., 'email' => ..., 'role' => ...]
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** The user who is currently logged in, or null for guests. */
function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function isLoggedIn(): bool
{
    return currentUser() !== null;
}

/** Put a user into the session after a successful login or registration. */
function loginUser(array $user): void
{
    $_SESSION['user'] = [
        'id'    => (int) $user['id'],
        'name'  => $user['name'],
        'email' => $user['email'],
        'role'  => $user['role'],
    ];
}

/** Log out and clear the session data. */
function logoutUser(): void
{
    unset($_SESSION['user']);
    session_regenerate_id(true);
}

/**
 * Guard for patient pages: sends guests to the login page.
 * $baseUrl is "" at the project root and "../" inside the patient folder.
 */
function requireLogin(string $baseUrl = ''): void
{
    if (!isLoggedIn()) {
        $redirect = $_SERVER['REQUEST_URI'] ?? ($baseUrl . 'patient/appointment.php');
        header('Location: ' . $baseUrl . 'login.php?redirect=' . urlencode($redirect));
        exit;
    }
}
