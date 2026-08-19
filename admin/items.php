<?php
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Rate Items';
$active = 'items';

$categories = $pdo->query("SELECT * FROM price_categories ORDER BY sort_order")->fetchAll();
$catId = isset($_GET['category']) ? (int)$_GET['category'] : ($categories[0]['id'] ?? 0);

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM price_items WHERE id = ?")->execute([(int)$_GET['delete']]);
    flash_set('success', 'Rate item deleted.');
    redirect('items.php?category=' . $catId);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    $data = [
        (int)$_POST['category_id'],
        trim($_POST['code']),
        trim($_POST['name_si']),
        trim($_POST['name_en']),
        trim($_POST['unit']),
        (float)$_POST['price'],
        (int)$_POST['sort_order'],
        isset($_POST['is_active']) ? 1 : 0,
    ];
    if ($id) {
        $pdo->prepare("UPDATE price_items SET category_id=?, code=?, name_si=?, name_en=?, unit=?, price=?, sort_order=?, is_active=? WHERE id=?")
            ->execute([...$data, $id]);
    } else {
        $pdo->prepare("INSERT INTO price_items (category_id, code, name_si, name_en, unit, price, sort_order, is_active) VALUES (?,?,?,?,?,?,?,?)")
            ->execute($data);
    }
    flash_set('success', 'Rate item saved.');
    redirect('items.php?category=' . (int)$_POST['category_id']);
}

$stmt = $pdo->prepare("SELECT * FROM price_items WHERE category_id = ? ORDER BY sort_order, id");
$stmt->execute([$catId]);
$items = $stmt->fetchAll();

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editing = null;
if ($editId) { foreach ($items as $it) { if ($it['id'] === $editId) { $editing = $it; break; } } }

include __DIR__ . '/includes/admin_header.php';
?>

<div class="tabs">
    <?php foreach ($categories as $c): ?>
        <a class="tab-link <?= $c['id'] === $catId ? 'active' : '' ?>" href="items.php?category=<?= (int)$c['id'] ?>"><?= e($c['name_en']) ?></a>
    <?php endforeach; ?>
</div>

<div class="grid" style="grid-template-columns: 1fr 340px; gap:26px; align-items:flex-start;">
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Code</th><th>Name (EN)</th><th>Name (SI)</th><th>Unit</th><th>Price</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (!$items): ?><tr><td colspan="6">No items in this category yet — add one using the form.</td></tr><?php endif; ?>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><?= e($it['code'] ?: '—') ?></td>
                    <td><?= e($it['name_en'] ?: '—') ?></td>
                    <td><?= e($it['name_si']) ?></td>
                    <td><?= e($it['unit'] ?: '—') ?></td>
                    <td><?= format_lkr($it['price']) ?></td>
                    <td class="action-links">
                        <a href="items.php?category=<?= $catId ?>&edit=<?= (int)$it['id'] ?>">Edit</a>
                        <a href="items.php?category=<?= $catId ?>&delete=<?= (int)$it['id'] ?>" onclick="return confirm('Delete this rate item?');" style="color:var(--rust);">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3><?= $editing ? 'Edit Item' : 'Add Item' ?></h3>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $editing ? (int)$editing['id'] : 0 ?>">
            <div class="field" style="margin-bottom:12px;">
                <label>Category</label>
                <select name="category_id">
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= (($editing['category_id'] ?? $catId) == $c['id']) ? 'selected' : '' ?>><?= e($c['name_en']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field" style="margin-bottom:12px;"><label>Code (optional, e.g. SD 1)</label><input type="text" name="code" value="<?= e($editing['code'] ?? '') ?>"></div>
            <div class="field" style="margin-bottom:12px;"><label>Name (Sinhala)</label><input type="text" name="name_si" required value="<?= e($editing['name_si'] ?? '') ?>"></div>
            <div class="field" style="margin-bottom:12px;"><label>Name (English)</label><input type="text" name="name_en" value="<?= e($editing['name_en'] ?? '') ?>"></div>
            <div class="field" style="margin-bottom:12px;"><label>Unit (optional)</label><input type="text" name="unit" placeholder="e.g. per day, 300ml" value="<?= e($editing['unit'] ?? '') ?>"></div>
            <div class="field" style="margin-bottom:12px;"><label>Price (Rs.)</label><input type="number" step="0.01" name="price" required value="<?= e($editing['price'] ?? '') ?>"></div>
            <div class="field" style="margin-bottom:12px;"><label>Sort order</label><input type="number" name="sort_order" value="<?= e($editing['sort_order'] ?? 0) ?>"></div>
            <label style="display:flex; align-items:center; gap:8px; font-weight:600; margin-bottom:16px;">
                <input type="checkbox" name="is_active" style="width:auto;" <?= (!$editing || $editing['is_active']) ? 'checked' : '' ?>> Visible on public site
            </label>
            <div class="flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm"><?= $editing ? 'Update' : 'Add' ?> Item</button>
                <?php if ($editing): ?><a href="items.php?category=<?= $catId ?>" class="btn btn-ghost btn-sm">Cancel</a><?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
