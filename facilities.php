<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Facilities & Rates';

$categories = $pdo->query("SELECT * FROM price_categories WHERE is_active = 1 ORDER BY sort_order")->fetchAll();

$activeSlug = $_GET['cat'] ?? ($categories[0]['slug'] ?? '');
$items = [];
if ($activeSlug) {
    $stmt = $pdo->prepare("
        SELECT i.* FROM price_items i
        JOIN price_categories c ON c.id = i.category_id
        WHERE c.slug = ? AND i.is_active = 1
        ORDER BY i.sort_order, i.id
    ");
    $stmt->execute([$activeSlug]);
    $items = $stmt->fetchAll();
}

include __DIR__ . '/includes/header.php';
?>

<section class="section--tight">
    <div class="container">
        <div class="eyebrow">Rate Card</div>
        <h1>Facilities &amp; Rates</h1>
        <p class="lede">All charges below are official government-institution rates maintained by the institute administration. Browse by category.</p>
    </div>
</section>

<section class="section" style="padding-top:0;">
    <div class="container">
        <div class="tabs">
            <?php foreach ($categories as $c): ?>
                <a class="tab-link <?= $c['slug'] === $activeSlug ? 'active' : '' ?>"
                   href="<?= SITE_URL ?>facilities.php?cat=<?= e($c['slug']) ?>">
                    <?= e($c['name_en']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php
        $activeCat = null;
        foreach ($categories as $c) { if ($c['slug'] === $activeSlug) { $activeCat = $c; break; } }
        ?>

        <?php if ($activeCat): ?>
        <div class="ledger">
            <div class="ledger-head">
                <span class="num"><?= (int)array_search($activeCat, $categories) + 1 ?></span>
                <h3><?= e($activeCat['name_en']) ?> — <?= e($activeCat['name_si']) ?></h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width:80px;">Code</th>
                        <th>Item (English)</th>
                        <th>අයිතමය (සිංහල)</th>
                        <th style="width:130px;">Unit</th>
                        <th style="width:130px; text-align:right;">Rate</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$items): ?>
                    <tr><td colspan="5">No items have been added to this category yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td class="code"><?= e($it['code'] ?: '—') ?></td>
                        <td><?= e($it['name_en'] ?: '—') ?></td>
                        <td><?= e($it['name_si']) ?></td>
                        <td><?= e($it['unit'] ?: '—') ?></td>
                        <td class="price"><?= format_lkr($it['price']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p>No rate categories have been published yet.</p>
        <?php endif; ?>

        <div class="notice" style="margin-top:28px;">
            <strong>Special conditions</strong>
            <ul>
                <li>Requests for training equipment are honoured on a first-come, first-served basis.</li>
                <li>Use of training equipment after 5.00pm incurs an additional Rs. 250 per day.</li>
                <li>The institute may recover costs for damage to buildings, furnishings or equipment.</li>
                <li>Trainees expecting to use hostel accommodation must report to the institute before 7.00pm.</li>
                <li>The institute premises are alcohol- and smoking-free.</li>
            </ul>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
