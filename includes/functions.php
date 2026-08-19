<?php
/**
 * Shared helper functions used across the public site and admin CMS.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header('Location: ' . $path);
    exit;
}

function is_logged_in() {
    return !empty($_SESSION['admin_id']);
}

function require_login() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function current_admin() {
    return [
        'id'   => $_SESSION['admin_id']   ?? null,
        'name' => $_SESSION['admin_name'] ?? null,
        'role' => $_SESSION['admin_role'] ?? null,
    ];
}

/** Simple CSRF token helpers */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf() {
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(400);
        die('Invalid form submission (CSRF check failed). Please go back and try again.');
    }
}

/** Flash messages (one-time notices shown after redirect) */
function flash_set($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function format_lkr($amount) {
    if ($amount === null || $amount === '') return '-';
    return 'Rs. ' . number_format((float)$amount, 2);
}

function format_date($date) {
    if (!$date) return '-';
    return date('d M Y', strtotime($date));
}
