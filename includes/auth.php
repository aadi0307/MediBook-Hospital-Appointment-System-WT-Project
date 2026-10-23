<?php
/**
 * Session-based authentication and role guards.
 *
 * A logged-in user is stored in $_SESSION['user'] as a small array:
 * ['id' => ..., 'name' => ..., 'email' => ..., 'role' => ...]
 *
 * Only the four values needed by the interface are stored. Every role page
 * calls requireRole(...) so access control happens on the server, never in
 * JavaScript or by hiding navigation links.
 */

const APP_ROLES = ['patient', 'doctor', 'admin'];

const APP_ROLE_LABELS = [
    'patient' => 'Patient',
    'doctor'  => 'Doctor',
    'admin'   => 'Admin',
];

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

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

/** The role stored in the session, or null for guests. */
function currentRole(): ?string
{
    $user = currentUser();

    return $user['role'] ?? null;
}

/** Human-readable role name, e.g. doctor -> Doctor. */
function roleLabel(?string $role): string
{
    return APP_ROLE_LABELS[$role] ?? 'User';
}

/** Where a role's dashboard lives, relative to the project root. */
function dashboardPath(string $role): string
{
    return $role . '/dashboard.php';
}

/**
 * Put a user into the session after a successful login.
 * A new session id prevents session fixation attacks.
 */
function loginUser(array $user): void
{
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id'    => (int) $user['id'],
        'name'  => (string) $user['name'],
        'email' => (string) $user['email'],
        'role'  => (string) $user['role'],
    ];
}

/** Securely remove all session data and the session cookie. */
function logoutUser(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

/** Send a guest to the login page, remembering the page they wanted. */
function requireLogin(string $baseUrl = ''): void
{
    if (isLoggedIn()) {
        return;
    }

    $target = $_SERVER['REQUEST_URI'] ?? ($baseUrl . 'index.php');
    header('Location: ' . $baseUrl . 'login.php?redirect=' . urlencode($target));
    exit;
}

/**
 * Server-side guard for a role-specific page.
 *
 * Guests are sent to login. Logged-in users with the wrong role are sent to
 * their own dashboard instead of being allowed to view this page.
 */
function requireRole(string $role, string $baseUrl = ''): void
{
    if (!in_array($role, APP_ROLES, true)) {
        throw new InvalidArgumentException('Unknown role guard: ' . $role);
    }

    if (!isLoggedIn()) {
        $target = $_SERVER['REQUEST_URI'] ?? ($baseUrl . dashboardPath($role));
        $login  = $baseUrl . 'login.php?role=' . urlencode($role)
                . '&redirect=' . urlencode($target);

        header('Location: ' . $login);
        exit;
    }

    if (currentRole() !== $role) {
        $ownRole = currentRole();

        if (!in_array($ownRole, APP_ROLES, true)) {
            logoutUser();
            header('Location: ' . $baseUrl . 'login.php');
            exit;
        }

        flashSet('warning', 'You do not have permission to open that page.');
        header('Location: ' . $baseUrl . dashboardPath($ownRole));
        exit;
    }
}

/** On the login page: already logged-in users go straight to their dashboard. */
function redirectLoggedInUser(string $baseUrl = ''): void
{
    $role = currentRole();

    if (isLoggedIn() && in_array($role, APP_ROLES, true)) {
        header('Location: ' . $baseUrl . dashboardPath($role));
        exit;
    }
}

/**
 * Accept only internal absolute paths. This prevents an open redirect where
 * an attacker sends someone to another website after login.
 */
function safeRedirectPath(?string $path, string $fallback): string
{
    $path = trim((string) $path);

    if ($path === ''
        || $path[0] !== '/'
        || str_starts_with($path, '//')
        || preg_match('/[\r\n]/', $path)
    ) {
        return $fallback;
    }

    return $path;
}

/** True when a redirect path belongs to the role's own area. */
function redirectMatchesRole(string $path, string $role): bool
{
    return in_array($role, APP_ROLES, true)
        && str_contains($path, '/' . $role . '/');
}

/* ---------------------------------------------------------------
 * CSRF protection for forms that change data
 * ------------------------------------------------------------- */

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/** Hidden input to place inside every POST form. */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

/** Check the token sent with a POST request. */
function csrfIsValid(): bool
{
    $sent = $_POST['csrf_token'] ?? '';

    return is_string($sent)
        && $sent !== ''
        && hash_equals(csrfToken(), $sent);
}
