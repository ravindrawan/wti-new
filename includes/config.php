<?php
/**
 * Database configuration & PDO Connection
 */

define('DB_HOST', 'wti-db');
define('DB_NAME', 'wti_db');
define('DB_USER', 'db_user');
define('DB_PASS', 'db_password');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

define('SITE_NAME', 'Wayamba Training Institute - Wariyapola');
define('SITE_URL', '/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . 'uploads/');

date_default_timezone_set('Asia/Colombo');

// Define maximum upload file size in bytes (e.g., 5MB)
define('UPLOAD_MAX_BYTES', 5 * 1024 * 1024);
