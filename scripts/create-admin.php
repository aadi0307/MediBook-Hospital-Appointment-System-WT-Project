<?php
/**
 * Controlled admin account setup (command line only).
 *
 * Usage:
 *   php scripts/create-admin.php "Admin Name" "admin@example.com" "password"
 *
 * There is deliberately no public admin registration page. Only someone with
 * shell access to the project can run this script.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$name     = trim((string) ($argv[1] ?? ''));
$email    = trim((string) ($argv[2] ?? ''));
$password = (string) ($argv[3] ?? '');

if ($name === '' || $email === '' || $password === '') {
    fwrite(STDERR, "Usage: php scripts/create-admin.php \"Admin Name\" \"admin@example.com\" \"password\"\n");
    exit(1);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Error: invalid email address.\n");
    exit(1);
}

if (strlen($password) < 8) {
    fwrite(STDERR, "Error: password must be at least 8 characters.\n");
    exit(1);
}

$db = tryGetDB();
if (!$db) {
    fwrite(STDERR, "Error: could not connect to the database. Check config/database.php.\n");
    exit(1);
}

$check = $db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$check->execute([':email' => $email]);

if ($check->fetch()) {
    fwrite(STDERR, "Error: an account with that email already exists.\n");
    exit(1);
}

try {
    $statement = $db->prepare(
        'INSERT INTO users (name, email, password_hash, phone, role, is_active)
         VALUES (:name, :email, :password, NULL, \'admin\', 1)'
    );
    $statement->execute([
        ':name'     => $name,
        ':email'    => $email,
        ':password' => password_hash($password, PASSWORD_DEFAULT),
    ]);

    fwrite(STDOUT, "Admin account created for {$email}.\n");
    exit(0);
} catch (PDOException $exception) {
    fwrite(STDERR, "Error: the admin account could not be created.\n");
    exit(1);
}
