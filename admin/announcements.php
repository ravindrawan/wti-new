<?php
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Announcements & Gallery';
$active = 'announcements';

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("SELECT image FROM announcements WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    if ($row = $stmt->fetch()) {
        delete_upload('announcements', $row['image']);
    }
    $pdo->prepare("DELETE FROM announcements WHERE id = ?")->execute([(int)$_GET['delete']]);
    flash_set('success', 'Announcement deleted.');
    redirect('announcements.php');
}

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editing = null;
if ($editId) {
    $stmt = $pdo->prepare("SELECT * FROM announcements WHERE id = ?");
    $stmt->execute([$editId]);
    $editing = $stmt->fetch();
}

$uploadError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    $data = [
        'title_si'       => trim($_POST['title_si']),
        'title_en'       => trim($_POST['title_en']),
        'description_si' => trim($_POST['description_si']),
        'description_en' => trim($_POST['description_en']),
        'link_url'       => trim($_POST['link_url']),
        'is_active'      => isset($_POST['is_active']) ? 1 : 0,
        'sort_order'     => (int)$_POST['sort_order'],
        'image'          => $editing['image'] ?? null,
    ];

    try {
        $newImage = handle_image_upload('image', 'announcements');
        if ($newImage) {
            if (!empty($data['image'])) delete_upload('announcements', $data['image']);
            $data['image'] = $newImage;
        }
        if (!empty($_POST['remove_image']) && empty($newImage)) {
            delete_upload('announcements', $data['image']);
            $data['image'] = null;
        }

        if (!$id && empty($data['image'])) {
            throw new RuntimeException('Please upload a photo for this announcement.');
        }

        if ($id) {
            $sql = "UPDATE announcements SET title_si=?, title_en=?, description_si=?, description_en=?, link_url=?, is_active=?, sort_order=?, image=? WHERE id=?";
            $pdo->prepare($sql)->execute([...array_values($data), $id]);
        } else {
            $sql = "INSERT INTO announcements (title_si,title_en,description_si,description_en,link_url,is_active,sort_order,image) VALUES (?,?,?,?,?,?,?,?)";
            $pdo->prepare($sql)->execute(array_values($data));
        }
        flash_set('success', 'Announcement saved.');
        redirect('announcements.php');
    } catch (RuntimeException $ex) {
        $uploadError = $ex->getMessage();
        $editing = array_merge($editing ?: ['id' => $id], $data);
    }
}

$announcements = $pdo->query("SELECT * FROM announcements ORDER BY sort_order, id DESC")->fetchAll();
include __DIR__ . '/includes/admin_header.php';
?>

<p style="color:var(--ink-soft); margin-bottom:20px;">Upload real photos of the institute, notices, or event flyers here — they appear on the homepage and the public "Notices &amp; Gallery" page.</p>

<div class="grid" style="grid-template-columns: 1fr 360px; gap:26px; align-items:flex-start;">
    <div class="grid grid-2">
        <?php if (!$announcements): ?><p>No announcements yet — add your first one using the form.</p><?php endif; ?>
        <?php foreach ($announcements as $a): ?>
        <div class="card">
            <?php if ($a['image']): ?>
                <img src="<?= e(upload_url('announcements', $a['image'])) ?>" alt="" style="width:100%; height:150px; object-fit:cover; border-radius:8px; margin-bottom:12px;">
            <?php endif; ?>
            <h3 style="margin-bottom:2px;"><?= e($a['title_en'] ?: $a['title_si']) ?></h3>
            <div style="font-size:.82rem; color:var(--ink-soft); margin-bottom:10px;"><?= e($a['title_si']) ?></div>
            <?= $a['is_active'] ? '<span class="badge badge-approved">Visible</span>' : '<span class="badge badge-cancelled">Hidden</span>' ?>
            <div class="action-links" style="margin-top:12px;">
                <a href="announcements.php?edit=<?= (int)$a['id'] ?>">Edit</a>
                <a href="announcements.php?delete=<?= (int)$a['id'] ?>" onclick="return confirm('Delete this announcement?');" style="color:var(--rust);">Delete</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <h3><?= $editing ? 'Edit Announcement' : 'Add Announcement' ?></h3>
        <?php if ($uploadError): ?><div class="alert alert-error"><?= e($uploadError) ?></div><?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $editing ? (int)($editing['id'] ?? 0) : 0 ?>">

            <div class="field" style="margin-bottom:12px;">
                <label>Photo <?= $editing ? '' : '*' ?></label>
                <?php if (!empty($editing['image'])): ?>
                    <img src="<?= e(upload_url('announcements', $editing['image'])) ?>" alt="" style="width:100%; border-radius:8px; margin-bottom:8px;">
                    <label style="display:flex; align-items:center; gap:8px; font-weight:400; font-size:.85rem; margin-bottom:8px;">
                        <input type="checkbox" name="remove_image" style="width:auto;"> Remove current photo
                    </label>
                <?php endif; ?>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" <?= $editing ? '' : 'required' ?>>
                <span class="hint">JPG, PNG, WEBP or GIF, up to 4MB.</span>
            </div>

            <div class="field" style="margin-bottom:12px;"><label>Title (Sinhala) *</label><input type="text" name="title_si" required value="<?= e($editing['title_si'] ?? '') ?>"></div>
            <div class="field" style="margin-bottom:12px;"><label>Title (English)</label><input type="text" name="title_en" value="<?= e($editing['title_en'] ?? '') ?>"></div>
            <div class="field" style="margin-bottom:12px;"><label>Description (Sinhala)</label><textarea name="description_si"><?= e($editing['description_si'] ?? '') ?></textarea></div>
            <div class="field" style="margin-bottom:12px;"><label>Description (English)</label><textarea name="description_en"><?= e($editing['description_en'] ?? '') ?></textarea></div>
            <div class="field" style="margin-bottom:12px;"><label>Link URL (optional)</label><input type="text" name="link_url" placeholder="https://..." value="<?= e($editing['link_url'] ?? '') ?>"></div>
            <div class="field" style="margin-bottom:12px;"><label>Sort order</label><input type="number" name="sort_order" value="<?= e($editing['sort_order'] ?? 0) ?>"></div>
            <label style="display:flex; align-items:center; gap:8px; font-weight:600; margin-bottom:16px;">
                <input type="checkbox" name="is_active" style="width:auto;" <?= (!$editing || $editing['is_active']) ? 'checked' : '' ?>> Visible on public site
            </label>

            <div class="flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm"><?= $editing ? 'Update' : 'Add' ?> Announcement</button>
                <?php if ($editing): ?><a href="announcements.php" class="btn btn-ghost btn-sm">Cancel</a><?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
