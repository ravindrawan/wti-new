<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Lecture Halls';
$halls = $pdo->query("SELECT * FROM halls WHERE is_active = 1 ORDER BY sort_order")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<section class="section--tight">
    <div class="container">
        <div class="eyebrow">Facilities</div>
        <h1>Lecture Halls</h1>
        <p class="lede">Nine halls of varying capacity, from the 150-seat Main Hall to the 15-seat Computer Lecture Hall. Rates shown are for government institutions and apply per day, 9.00am–5.00pm.</p>
    </div>
</section>

<section class="section" style="padding-top:0;">
    <div class="container">
        <div class="grid grid-3">
            <?php foreach ($halls as $h): ?>
            <div class="card hall-card">
                <div class="hall-photo"><?= e(mb_substr($h['name_en'], 0, 1)) ?></div>
                <span class="tag"><?= $h['has_ac'] ? 'A/C available' : 'Non A/C only' ?></span>
                <h3 style="margin-bottom:2px;"><?= e($h['name_en']) ?></h3>
                <div class="si" style="color:var(--ink-soft); font-size:.85rem; margin-bottom:8px;"><?= e($h['name_si']) ?></div>
                <div class="cap">
                    Capacity: <?= $h['capacity_min'] ? (int)$h['capacity_min'] . '–' : 'up to ' ?><?= (int)$h['capacity_max'] ?> persons
                </div>
                <?php if ($h['price_ac']): ?><div class="price-row"><span>With A/C</span><b><?= format_lkr($h['price_ac']) ?></b></div><?php endif; ?>
                <?php if ($h['price_non_ac']): ?><div class="price-row"><span>Without A/C</span><b><?= format_lkr($h['price_non_ac']) ?></b></div><?php endif; ?>
                <?php if ($h['description']): ?><p style="font-size:.85rem; margin-top:8px;"><?= e($h['description']) ?></p><?php endif; ?>
                <a href="<?= SITE_URL ?>booking.php?hall=<?= (int)$h['id'] ?>" class="btn btn-teal btn-sm" style="margin-top:14px;">Book this hall</a>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="notice" style="margin-top:32px;">
            <strong>Please note</strong>
            <ul>
                <li>Halls are available 9.00am – 5.00pm only; a per-hour fee applies for use after 5.00pm.</li>
                <li>The institute may charge for any damage caused to buildings or equipment.</li>
                <li>See the <a href="<?= SITE_URL ?>facilities.php">full facilities &amp; rates page</a> for equipment, accommodation and catering charges.</li>
            </ul>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
