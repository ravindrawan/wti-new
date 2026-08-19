<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Notices & Gallery';
$announcements = $pdo->query("SELECT * FROM announcements WHERE is_active = 1 ORDER BY sort_order, id DESC")->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<section class="section--tight">
    <div class="container">
        <div class="eyebrow">Updates</div>
        <h1>Notices &amp; Gallery</h1>
        <p class="lede">Photos and announcements from the institute.</p>
    </div>
</section>

<section class="section" style="padding-top:0;">
    <div class="container">
        <?php if (!$announcements): ?>
            <p>No announcements have been published yet.</p>
        <?php else: ?>
        <div class="grid grid-3">
            <?php foreach ($announcements as $a): ?>
            <div class="card" style="padding:0; overflow:hidden;">
                <?php if ($a['image']): ?>
                    <img src="<?= e(upload_url('announcements', $a['image'])) ?>" alt="<?= e($a['title_en'] ?: $a['title_si']) ?>" style="width:100%; height:190px; object-fit:cover;">
                <?php endif; ?>
                <div style="padding:18px 20px;">
                    <h3 style="margin-bottom:2px;"><?= e($a['title_en'] ?: $a['title_si']) ?></h3>
                    <?php if ($a['title_en']): ?><div style="font-size:.85rem; color:var(--ink-soft); margin-bottom:8px;"><?= e($a['title_si']) ?></div><?php endif; ?>
                    <?php if ($a['description_en'] || $a['description_si']): ?>
                        <p style="font-size:.9rem; margin-bottom:0;"><?= nl2br(e($a['description_en'] ?: $a['description_si'])) ?></p>
                    <?php endif; ?>
                    <?php if ($a['link_url']): ?><a href="<?= e($a['link_url']) ?>" class="btn btn-ghost btn-sm" style="margin-top:12px;" target="_blank" rel="noopener">Learn more →</a><?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
