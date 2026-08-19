<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Home';

$halls = $pdo->query("SELECT * FROM halls WHERE is_active = 1 ORDER BY sort_order LIMIT 4")->fetchAll();
$categoryCount = $pdo->query("SELECT COUNT(*) FROM price_categories WHERE is_active = 1")->fetchColumn();
$hallCount = $pdo->query("SELECT COUNT(*) FROM halls WHERE is_active = 1")->fetchColumn();

include __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container hero-grid">
        <div>
            <div class="eyebrow" style="color:var(--gold-500)">Government Training Institute · Wariyapola</div>
            <h1>Halls, accommodation and catering — booked online, ready when you arrive.</h1>
            <p class="lede">Wayamba Training Institute Wariyapola supports government and private organisations with fully equipped lecture halls, guest rooms, hostel accommodation and full-board catering. Check rates and reserve a hall in minutes.</p>
            <div class="hero-actions">
                <a href="<?= SITE_URL ?>booking.php" class="btn btn-primary">Book a Lecture Hall</a>
                <a href="<?= SITE_URL ?>facilities.php" class="btn btn-outline">View All Rates</a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat"><b><?= (int)$hallCount ?></b><span>Lecture Halls</span></div>
                <div class="hero-stat"><b>150</b><span>Max Seating (Main Hall)</span></div>
                <div class="hero-stat"><b><?= (int)$categoryCount ?></b><span>Rate Categories</span></div>
            </div>
        </div>
        <div class="hero-card">
            <h3>Quick Rate Guide</h3>
            <p class="muted">Government institution rates, per day</p>
            <ul>
                <li>Main Hall (up to 150, A/C) <b><?= format_lkr(10000) ?></b></li>
                <li>Hall No. 03 (70–80, A/C) <b><?= format_lkr(8000) ?></b></li>
                <li>Hall No. 07 (up to 80, A/C) <b><?= format_lkr(9000) ?></b></li>
                <li>Computer Lecture Hall (15) <b><?= format_lkr(8000) ?></b></li>
            </ul>
            <a href="<?= SITE_URL ?>booking.php" class="btn btn-teal btn-block">Check Availability &amp; Book</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="eyebrow">Facilities</div>
        <div class="flex-between" style="flex-wrap:wrap; gap:12px;">
            <h2 class="mb-0">Popular lecture halls</h2>
            <a href="<?= SITE_URL ?>halls.php" class="btn btn-ghost btn-sm">View all halls →</a>
        </div>
        <p class="lede">All halls are available from 9.00am to 5.00pm. Rates below are shown for government institutions and include general upkeep of the hall.</p>

        <div class="grid grid-4" style="margin-top:26px;">
            <?php foreach ($halls as $h): ?>
            <div class="card hall-card">
                <div class="hall-photo"><?= e(mb_substr($h['name_en'], 0, 1)) ?></div>
                <span class="tag"><?= $h['has_ac'] ? 'A/C available' : 'Non A/C' ?></span>
                <h3 style="margin-bottom:2px;"><?= e($h['name_en']) ?></h3>
                <div class="cap">Up to <?= (int)$h['capacity_max'] ?> persons</div>
                <?php if ($h['price_ac']): ?><div class="price-row"><span>With A/C</span><b><?= format_lkr($h['price_ac']) ?></b></div><?php endif; ?>
                <?php if ($h['price_non_ac']): ?><div class="price-row"><span>Without A/C</span><b><?= format_lkr($h['price_non_ac']) ?></b></div><?php endif; ?>
                <a href="<?= SITE_URL ?>booking.php?hall=<?= (int)$h['id'] ?>" class="btn btn-teal btn-sm" style="margin-top:14px;">Book this hall</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--sage">
    <div class="container">
        <div class="eyebrow">How it works</div>
        <h2>Reserve a hall in three steps</h2>
        <div class="grid grid-3" style="margin-top:24px;">
            <div class="card">
                <div class="card-icon">1</div>
                <h3>Choose a hall &amp; date</h3>
                <p>Compare capacity, A/C availability and daily rate, then pick the date and time you need the hall.</p>
            </div>
            <div class="card">
                <div class="card-icon">2</div>
                <h3>Submit your request</h3>
                <p>Fill in your details and event information. You'll receive a booking reference number instantly.</p>
            </div>
            <div class="card">
                <div class="card-icon">3</div>
                <h3>Get confirmation</h3>
                <p>Our administration team reviews and confirms your booking. Track its status anytime with your reference number.</p>
            </div>
        </div>
    </div>
</section>

<section class="section section--teal">
    <div class="container">
        <div class="grid grid-2" style="align-items:center;">
            <div>
                <div class="eyebrow" style="color:var(--gold-500)">Full-board options</div>
                <h2>Accommodation &amp; catering for residential training</h2>
                <p>Guest rooms, dormitory-style hostel accommodation, and a full catering menu — from breakfast rice-and-curry to fresh juices and short eats — are available for institutions running multi-day programmes.</p>
                <a href="<?= SITE_URL ?>facilities.php" class="btn btn-primary">See full rate list</a>
            </div>
            <div class="grid grid-2">
                <div class="card" style="background:rgba(255,255,255,.06); border-color:rgba(255,255,255,.14);">
                    <h3 style="color:#fff;">Guest Rooms</h3>
                    <p style="color:#C9D6D0;">From Rs. 3,000/night</p>
                </div>
                <div class="card" style="background:rgba(255,255,255,.06); border-color:rgba(255,255,255,.14);">
                    <h3 style="color:#fff;">Hostel</h3>
                    <p style="color:#C9D6D0;">From Rs. 375/person/night</p>
                </div>
                <div class="card" style="background:rgba(255,255,255,.06); border-color:rgba(255,255,255,.14);">
                    <h3 style="color:#fff;">Meals</h3>
                    <p style="color:#C9D6D0;">Breakfast, lunch &amp; dinner packages</p>
                </div>
                <div class="card" style="background:rgba(255,255,255,.06); border-color:rgba(255,255,255,.14);">
                    <h3 style="color:#fff;">Equipment</h3>
                    <p style="color:#C9D6D0;">Multimedia, sound &amp; internet</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
>>>>>>> c4c51eb70c6d5173b77b37aa880754854b5adf1e
