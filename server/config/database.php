<?php
/**
 * Aarunya Healthcare - Database Configuration
 * Centralized database connection with PDO
 * Reads credentials from .env file via Environment loader
 */

// Load environment variables if not already loaded
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile) && !defined('ENV_LOADED')) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        // Strip surrounding quotes
        if (preg_match('/^(["\'])(.*)\\1$/', $value, $m)) {
            $value = $m[2];
        }
        if (!isset($_ENV[$key])) {
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
            putenv("$key=$value");
        }
    }
    define('ENV_LOADED', true);
}

// Database credentials — read from .env, fall back to defaults
define('DB_HOST',    $_ENV['DB_HOST']    ?? 'localhost');
define('DB_PORT',    $_ENV['DB_PORT']    ?? '3307');
define('DB_NAME',    $_ENV['DB_NAME']    ?? 'aarunya_db');
define('DB_USER',    $_ENV['DB_USER']    ?? 'root');
define('DB_PASS',    $_ENV['DB_PASS']    ?? '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get Database Connection
 * Returns a PDO instance with proper configuration
 */
function getDB() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST
                 . ";port="    . DB_PORT
                 . ";dbname="  . DB_NAME
                 . ";charset=" . DB_CHARSET;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET,
            ];

            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

            // Match MySQL timezone to PHP timezone
            try {
                $pdo->exec("SET time_zone = '" . date('P') . "'");
            } catch (PDOException $e) {
                error_log("Could not set MySQL timezone: " . $e->getMessage());
            }

        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed. Please try again later.");
        }
    }

    return $pdo;
}

/**
 * Test Database Connection
 */
function testDBConnection() {
    try {
        $pdo = getDB();
        $pdo->query("SELECT 1");
        return true;
    } catch (Exception $e) {
        error_log("Database connection test failed: " . $e->getMessage());
        return false;
    }
}
?>
