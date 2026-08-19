<?php
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Site Pages';
$active = 'pages';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)$_POST['id'];
    $pdo->prepare("UPDATE pages SET title_en=?, title_si=?, content_en=?, content_si=? WHERE id=?")
        ->execute([trim($_POST['title_en']), trim($_POST['title_si']), trim($_POST['content_en']), trim($_POST['content_si']), $id]);
    flash_set('success', 'Page content updated.');
    redirect('pages.php');
}

$pages = $pdo->query("SELECT * FROM pages ORDER BY id")->fetchAll();
include __DIR__ . '/includes/admin_header.php';
?>

<p style="color:var(--ink-soft); margin-bottom:20px;">Edit text blocks used on the public site (e.g. About, Contact intro).</p>

<?php foreach ($pages as $p): ?>
<div class="card" style="margin-bottom:20px;">
    <h3>/<?= e($p['slug']) ?></h3>
    <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
        <div class="form-grid">
            <div class="field"><label>Title (English)</label><input type="text" name="title_en" value="<?= e($p['title_en']) ?>"></div>
            <div class="field"><label>Title (Sinhala)</label><input type="text" name="title_si" value="<?= e($p['title_si']) ?>"></div>
            <div class="field full"><label>Content (English)</label><textarea name="content_en"><?= e($p['content_en']) ?></textarea></div>
            <div class="field full"><label>Content (Sinhala)</label><textarea name="content_si"><?= e($p['content_si']) ?></textarea></div>
        </div>
        <button type="submit" class="btn btn-primary btn-sm" style="margin-top:14px;">Save</button>
    </form>
</div>
<?php endforeach; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
