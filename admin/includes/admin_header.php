<?php
// Expects $page_title and $active (nav key) to be set by the including page.
$admin = current_admin();
$flash = flash_get();
$active = $active ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title ?? 'Admin') ?> · WTI Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="brand">
            <span class="brand-seal">WTI</span>
            <span class="brand-text">
                <span class="en">WTI Admin</span>
                <span class="si">CMS &amp; Bookings</span>
            </span>
        </div>
        <nav class="admin-nav">
            <a href="dashboard.php" class="<?= $active === 'dashboard' ? 'active' : '' ?>">Dashboard</a>

            <div class="group-label">Bookings</div>
            <a href="bookings.php" class="<?= $active === 'bookings' ? 'active' : '' ?>">Hall Bookings</a>

            <div class="group-label">Facilities</div>
            <a href="halls.php" class="<?= $active === 'halls' ? 'active' : '' ?>">Lecture Halls</a>
            <a href="categories.php" class="<?= $active === 'categories' ? 'active' : '' ?>">Rate Categories</a>
            <a href="items.php" class="<?= $active === 'items' ? 'active' : '' ?>">Rate Items</a>

            <div class="group-label">Content</div>
            <a href="announcements.php" class="<?= $active === 'announcements' ? 'active' : '' ?>">Announcements &amp; Gallery</a>
            <a href="pages.php" class="<?= $active === 'pages' ? 'active' : '' ?>">Site Pages</a>
            <a href="enquiries.php" class="<?= $active === 'enquiries' ? 'active' : '' ?>">Enquiries</a>

            <?php if ($admin['role'] === 'super_admin'): ?>
            <div class="group-label">Administration</div>
            <a href="admins.php" class="<?= $active === 'admins' ? 'active' : '' ?>">Admin Users</a>
            <?php endif; ?>

            <div class="group-label">&nbsp;</div>
            <a href="../index.php" target="_blank">View Public Site ↗</a>
            <a href="logout.php">Log Out</a>
        </nav>
    </aside>

    <div class="admin-main">
        <div class="admin-topbar">
            <div class="flex gap-1" style="align-items:center;">
                <button class="admin-toggle btn btn-ghost btn-sm" onclick="document.getElementById('adminSidebar').classList.toggle('open')">☰</button>
                <h1><?= e($page_title ?? 'Admin') ?></h1>
            </div>
            <div style="font-size:.85rem; color:var(--ink-soft);">Signed in as <strong><?= e($admin['name']) ?></strong></div>
        </div>
        <div class="admin-content">
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endif; ?>
