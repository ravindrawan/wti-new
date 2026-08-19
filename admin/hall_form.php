<?php
require_once __DIR__ . '/includes/auth.php';
$active = 'halls';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$hall = ['name_si'=>'','name_en'=>'','capacity_min'=>'','capacity_max'=>'','price_ac'=>'','price_non_ac'=>'','has_ac'=>1,'description'=>'','is_active'=>1,'sort_order'=>0];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM halls WHERE id = ?");
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) $hall = $found;
}
$page_title = $id ? 'Edit Hall' : 'Add Hall';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $data = [
        'name_si'      => trim($_POST['name_si']),
        'name_en'      => trim($_POST['name_en']),
        'capacity_min' => $_POST['capacity_min'] !== '' ? (int)$_POST['capacity_min'] : null,
        'capacity_max' => (int)$_POST['capacity_max'],
        'price_ac'     => $_POST['price_ac'] !== '' ? (float)$_POST['price_ac'] : null,
        'price_non_ac' => $_POST['price_non_ac'] !== '' ? (float)$_POST['price_non_ac'] : null,
        'has_ac'       => isset($_POST['has_ac']) ? 1 : 0,
        'description'  => trim($_POST['description']),
        'is_active'    => isset($_POST['is_active']) ? 1 : 0,
        'sort_order'   => (int)$_POST['sort_order'],
    ];

    if ($id) {
        $sql = "UPDATE halls SET name_si=?, name_en=?, capacity_min=?, capacity_max=?, price_ac=?, price_non_ac=?, has_ac=?, description=?, is_active=?, sort_order=? WHERE id=?";
        $pdo->prepare($sql)->execute([...array_values($data), $id]);
    } else {
        $sql = "INSERT INTO halls (name_si,name_en,capacity_min,capacity_max,price_ac,price_non_ac,has_ac,description,is_active,sort_order) VALUES (?,?,?,?,?,?,?,?,?,?)";
        $pdo->prepare($sql)->execute(array_values($data));
    }
    flash_set('success', 'Hall saved successfully.');
    redirect('halls.php');
}

include __DIR__ . '/includes/admin_header.php';
?>

<form method="POST" class="card" style="max-width:720px;">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div class="field"><label>Name (English) *</label><input type="text" name="name_en" required value="<?= e($hall['name_en']) ?>"></div>
        <div class="field"><label>Name (Sinhala) *</label><input type="text" name="name_si" required value="<?= e($hall['name_si']) ?>"></div>

        <div class="field"><label>Min capacity</label><input type="number" name="capacity_min" value="<?= e($hall['capacity_min']) ?>"></div>
        <div class="field"><label>Max capacity *</label><input type="number" name="capacity_max" required value="<?= e($hall['capacity_max']) ?>"></div>

        <div class="field"><label>Price with A/C (Rs.)</label><input type="number" step="0.01" name="price_ac" value="<?= e($hall['price_ac']) ?>"></div>
        <div class="field"><label>Price without A/C (Rs.)</label><input type="number" step="0.01" name="price_non_ac" value="<?= e($hall['price_non_ac']) ?>"></div>

        <div class="field full">
            <label style="display:flex; align-items:center; gap:8px; font-weight:600;">
                <input type="checkbox" name="has_ac" style="width:auto;" <?= $hall['has_ac'] ? 'checked' : '' ?>> Hall has A/C option
            </label>
        </div>

        <div class="field full"><label>Description (optional)</label><textarea name="description"><?= e($hall['description']) ?></textarea></div>

        <div class="field"><label>Sort order</label><input type="number" name="sort_order" value="<?= e($hall['sort_order']) ?>"></div>
        <div class="field">
            <label style="display:flex; align-items:center; gap:8px; font-weight:600; margin-top:26px;">
                <input type="checkbox" name="is_active" style="width:auto;" <?= $hall['is_active'] ? 'checked' : '' ?>> Visible on public site
            </label>
        </div>
    </div>

    <div class="flex gap-1" style="margin-top:20px;">
        <button type="submit" class="btn btn-primary">Save Hall</button>
        <a href="halls.php" class="btn btn-ghost">Cancel</a>
    </div>
</form>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
