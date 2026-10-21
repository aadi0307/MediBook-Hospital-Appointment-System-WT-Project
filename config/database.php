<?php
/**
 * Single place that knows how to connect to the MySQL database.
 *
 * The default values match a standard XAMPP installation:
 * MySQL running on 127.0.0.1:3306, user "root", no password,
 * database "medibook_db" (created from database/schema.sql).
 *
 * Change the constants below if your local setup is different.
 */

const DB_HOST = '127.0.0.1';
const DB_PORT = '3306';
const DB_NAME = 'medibook_db';
const DB_USER = 'root';
const DB_PASS = '';

/**
 * Open (and cache) the database connection using PDO.
 *
 * We use PDO with prepared statements, which protects the project
 * from SQL injection - this is mentioned in the README.
 *
 * Returns null when the database cannot be reached, so pages can
 * show a friendly message instead of a PHP error screen.
 */
function tryGetDB(): ?PDO
{
    static $pdo = null;
    static $alreadyFailed = false;

    if ($alreadyFailed) {
        return null;
    }
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    try {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT
            . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $exception) {
        // Keep the real error in the server log, show something friendly below.
        error_log('MediBook database error: ' . $exception->getMessage());
        $alreadyFailed = true;
        return null;
    }

    return $pdo;
}
