<?php
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Rate Categories';
$active = 'categories';

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM price_categories WHERE id = ?")->execute([(int)$_GET['delete']]);
    flash_set('success', 'Category deleted (its rate items were removed too).');
    redirect('categories.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    $data = [
        trim($_POST['name_en']),
        trim($_POST['name_si']),
        trim($_POST['slug']),
        (int)$_POST['sort_order'],
        isset($_POST['is_active']) ? 1 : 0,
    ];
    if ($id) {
        $pdo->prepare("UPDATE price_categories SET name_en=?, name_si=?, slug=?, sort_order=?, is_active=? WHERE id=?")
            ->execute([...$data, $id]);
    } else {
        $pdo->prepare("INSERT INTO price_categories (name_en, name_si, slug, sort_order, is_active) VALUES (?,?,?,?,?)")
            ->execute($data);
    }
    flash_set('success', 'Category saved.');
    redirect('categories.php');
}

$categories = $pdo->query("
    SELECT c.*, (SELECT COUNT(*) FROM price_items i WHERE i.category_id = c.id) AS item_count
    FROM price_categories c ORDER BY c.sort_order
")->fetchAll();

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editing = null;
if ($editId) {
    foreach ($categories as $c) { if ($c['id'] === $editId) { $editing = $c; break; } }
}

include __DIR__ . '/includes/admin_header.php';
?>

<div class="grid" style="grid-template-columns: 1fr 340px; gap:26px; align-items:flex-start;">
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Name (EN)</th><th>Name (SI)</th><th>Slug</th><th>Items</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($categories as $c): ?>
                <tr>
                    <td><?= e($c['name_en']) ?></td>
                    <td><?= e($c['name_si']) ?></td>
                    <td><code><?= e($c['slug']) ?></code></td>
                    <td><?= (int)$c['item_count'] ?></td>
                    <td><?= $c['is_active'] ? '<span class="badge badge-approved">Active</span>' : '<span class="badge badge-cancelled">Hidden</span>' ?></td>
                    <td class="action-links">
                        <a href="categories.php?edit=<?= (int)$c['id'] ?>">Edit</a>
                        <a href="items.php?category=<?= (int)$c['id'] ?>">Items</a>
                        <a href="categories.php?delete=<?= (int)$c['id'] ?>" onclick="return confirm('Delete this category and all its rate items?');" style="color:var(--rust);">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3><?= $editing ? 'Edit Category' : 'Add Category' ?></h3>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $editing ? (int)$editing['id'] : 0 ?>">
            <div class="field" style="margin-bottom:12px;"><label>Name (English)</label><input type="text" name="name_en" required value="<?= e($editing['name_en'] ?? '') ?>"></div>
            <div class="field" style="margin-bottom:12px;"><label>Name (Sinhala)</label><input type="text" name="name_si" required value="<?= e($editing['name_si'] ?? '') ?>"></div>
            <div class="field" style="margin-bottom:12px;"><label>Slug (URL-friendly)</label><input type="text" name="slug" required pattern="[a-z0-9\-]+" value="<?= e($editing['slug'] ?? '') ?>"><span class="hint">Lowercase letters, numbers, hyphens only.</span></div>
            <div class="field" style="margin-bottom:12px;"><label>Sort order</label><input type="number" name="sort_order" value="<?= e($editing['sort_order'] ?? 0) ?>"></div>
            <label style="display:flex; align-items:center; gap:8px; font-weight:600; margin-bottom:16px;">
                <input type="checkbox" name="is_active" style="width:auto;" <?= (!$editing || $editing['is_active']) ? 'checked' : '' ?>> Visible on public site
            </label>
            <div class="flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm"><?= $editing ? 'Update' : 'Add' ?> Category</button>
                <?php if ($editing): ?><a href="categories.php" class="btn btn-ghost btn-sm">Cancel</a><?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
