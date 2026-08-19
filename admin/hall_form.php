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

$uploadError = null;

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
        'photo'        => $hall['photo'] ?? null,
    ];

    try {
        $newPhoto = handle_image_upload('photo', 'halls');
        if ($newPhoto) {
            if (!empty($data['photo'])) delete_upload('halls', $data['photo']);
            $data['photo'] = $newPhoto;
        }
        if (!empty($_POST['remove_photo']) && empty($newPhoto)) {
            delete_upload('halls', $data['photo']);
            $data['photo'] = null;
        }

        if ($id) {
            $sql = "UPDATE halls SET name_si=?, name_en=?, capacity_min=?, capacity_max=?, price_ac=?, price_non_ac=?, has_ac=?, description=?, is_active=?, sort_order=?, photo=? WHERE id=?";
            $pdo->prepare($sql)->execute([...array_values($data), $id]);
        } else {
            $sql = "INSERT INTO halls (name_si,name_en,capacity_min,capacity_max,price_ac,price_non_ac,has_ac,description,is_active,sort_order,photo) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
            $pdo->prepare($sql)->execute(array_values($data));
        }
        flash_set('success', 'Hall saved successfully.');
        redirect('halls.php');
    } catch (RuntimeException $ex) {
        $uploadError = $ex->getMessage();
        $hall = array_merge($hall, $data);
    }
}

include __DIR__ . '/includes/admin_header.php';
?>

<?php if ($uploadError): ?><div class="alert alert-error"><?= e($uploadError) ?></div><?php endif; ?>

<form method="POST" class="card" style="max-width:720px;" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="form-grid">
        <div class="field full">
            <label>Hall photo</label>
            <?php if (!empty($hall['photo'])): ?>
                <img src="<?= e(upload_url('halls', $hall['photo'])) ?>" alt="" style="width:220px; border-radius:10px; margin-bottom:10px; display:block;">
                <label style="display:flex; align-items:center; gap:8px; font-weight:400; font-size:.85rem; margin-bottom:8px;">
                    <input type="checkbox" name="remove_photo" style="width:auto;"> Remove current photo
                </label>
            <?php endif; ?>
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp,image/gif">
            <span class="hint">JPG, PNG, WEBP or GIF, up to 4MB. Uploading a new photo replaces the current one.</span>
        </div>

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
