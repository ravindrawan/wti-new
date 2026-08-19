<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
$current = basename($_SERVER['SCRIPT_NAME']);
$flash = flash_get();
?>
<!DOCTYPE html>
<html lang="si">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? e($page_title) . ' | ' : '' ?><?= e(SITE_NAME) ?></title>
<meta name="description" content="Wayamba Training Institute, Wariyapola — lecture halls, accommodation, catering and online hall booking.">
<link rel="stylesheet" href="<?= SITE_URL ?>assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container nav">
        <a class="brand" href="<?= SITE_URL ?>index.php">
            <span class="brand-seal">WTI</span>
            <span class="brand-text">
                <span class="en">Wayamba Training Institute</span>
                <span class="si">වයඹ පුහුණු ආයතනය - වාරියපොල</span>
            </span>
        </a>

        <nav class="nav-links" id="navLinks">
            <a href="<?= SITE_URL ?>index.php" class="<?= $current === 'index.php' ? 'active' : '' ?>">Home</a>
            <a href="<?= SITE_URL ?>halls.php" class="<?= $current === 'halls.php' ? 'active' : '' ?>">Lecture Halls</a>
            <a href="<?= SITE_URL ?>facilities.php" class="<?= $current === 'facilities.php' ? 'active' : '' ?>">Facilities &amp; Rates</a>
            <a href="<?= SITE_URL ?>announcements.php" class="<?= $current === 'announcements.php' ? 'active' : '' ?>">Notices &amp; Gallery</a>
            <a href="<?= SITE_URL ?>booking.php" class="<?= $current === 'booking.php' ? 'active' : '' ?>">Book a Hall</a>
            <a href="<?= SITE_URL ?>track_booking.php" class="<?= $current === 'track_booking.php' ? 'active' : '' ?>">Track Booking</a>
            <a href="<?= SITE_URL ?>contact.php" class="<?= $current === 'contact.php' ? 'active' : '' ?>">Contact</a>
        </nav>

        <div class="nav-cta">
            <a href="<?= SITE_URL ?>booking.php" class="btn btn-primary btn-sm"><span class="long">Book a</span> Hall</a>
            <button class="nav-toggle" aria-label="Menu" onclick="document.getElementById('navLinks').classList.toggle('open')">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

<?php if ($flash): ?>
<div class="container" style="padding-top:20px;">
    <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
</div>
<?php endif; ?>
