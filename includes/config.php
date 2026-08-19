<?php
/**
 * Database configuration
 * Updated for Environment Variables (OpenShift compatible)
 */

define('DB_HOST', getenv('MYSQL_HOST') ?: 'localhost');
define('DB_NAME', getenv('MYSQL_DATABASE') ?: 'wti_db');
define('DB_USER', getenv('MYSQL_USER') ?: 'root');
define('DB_PASS', getenv('MYSQL_PASSWORD') ?: '');

define('SITE_NAME', 'Wayamba Training Institute - Wariyapola');
define('SITE_URL', '/'); 
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . 'uploads/');

date_default_timezone_set('Asia/Colombo');
