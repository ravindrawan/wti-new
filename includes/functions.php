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

/**
 * Handle a single uploaded image file (from an <input type="file">).
 * Validates type & size, saves it under uploads/{$subdir}/, and
 * returns the generated filename on success, or null if no file was
 * submitted. Throws a RuntimeException with a friendly message on
 * validation failure so the caller can show it to the admin.
 */
function handle_image_upload($fieldName, $subdir) {
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // nothing submitted — not an error
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed (error code ' . $file['error'] . '). Please try again.');
    }
    if ($file['size'] > UPLOAD_MAX_BYTES) {
        throw new RuntimeException('Image is too large. Maximum size is ' . (UPLOAD_MAX_BYTES / 1024 / 1024) . 'MB.');
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Unsupported image type. Please upload a JPG, PNG, WEBP or GIF file.');
    }

    $ext = $allowed[$mime];
    $filename = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

    $targetDir = rtrim(UPLOAD_DIR, '/') . '/' . trim($subdir, '/') . '/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $targetDir . $filename)) {
        throw new RuntimeException('Could not save the uploaded image. Check that the uploads/ folder is writable.');
    }

    return $filename;
}

/** Delete a previously uploaded image (ignores missing files). */
function delete_upload($subdir, $filename) {
    if (!$filename) return;
    $path = rtrim(UPLOAD_DIR, '/') . '/' . trim($subdir, '/') . '/' . $filename;
    if (is_file($path)) {
        @unlink($path);
    }
}

/** Build a public URL for an uploaded image. */
function upload_url($subdir, $filename) {
    return UPLOAD_URL . trim($subdir, '/') . '/' . rawurlencode($filename);
}
