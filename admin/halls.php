<?php
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Lecture Halls';
$active = 'halls';

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM halls WHERE id = ?")->execute([(int)$_GET['delete']]);
    flash_set('success', 'Hall deleted.');
    redirect('halls.php');
}

$halls = $pdo->query("SELECT * FROM halls ORDER BY sort_order")->fetchAll();
include __DIR__ . '/includes/admin_header.php';
?>

<div class="flex-between" style="margin-bottom:18px;">
    <p class="mb-0" style="color:var(--ink-soft);">Manage lecture halls shown on the public site and booking form.</p>
    <a href="hall_form.php" class="btn btn-primary btn-sm">+ Add Hall</a>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr><th>Name (EN)</th><th>Name (SI)</th><th>Capacity</th><th>Price A/C</th><th>Price Non-A/C</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($halls as $h): ?>
            <tr>
                <td><?= e($h['name_en']) ?></td>
                <td><?= e($h['name_si']) ?></td>
                <td><?= $h['capacity_min'] ? (int)$h['capacity_min'].'–' : '' ?><?= (int)$h['capacity_max'] ?></td>
                <td><?= format_lkr($h['price_ac']) ?></td>
                <td><?= format_lkr($h['price_non_ac']) ?></td>
                <td><?= $h['is_active'] ? '<span class="badge badge-approved">Active</span>' : '<span class="badge badge-cancelled">Hidden</span>' ?></td>
                <td class="action-links">
                    <a href="hall_form.php?id=<?= (int)$h['id'] ?>">Edit</a>
                    <a href="halls.php?delete=<?= (int)$h['id'] ?>" onclick="return confirm('Delete this hall? This cannot be undone.');" style="color:var(--rust);">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
