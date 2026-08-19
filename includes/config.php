<?php
/**
 * Database configuration
 * Update these 4 values to match your hosting environment.
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'wti_db');
define('DB_USER', 'root');
define('DB_PASS', '');

define('SITE_NAME', 'Wayamba Training Institute - Wariyapola');
define('SITE_URL', '/'); // change to sub-folder path if not installed at web root, e.g. '/wti-website/'
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . 'uploads/');

date_default_timezone_set('Asia/Colombo');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed. Please check includes/config.php — ' . htmlspecialchars($e->getMessage()));
}
