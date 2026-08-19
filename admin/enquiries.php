<?php
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Enquiries';
$active = 'enquiries';

if (isset($_GET['read'])) {
    $pdo->prepare("UPDATE enquiries SET is_read = 1 WHERE id = ?")->execute([(int)$_GET['read']]);
    redirect('enquiries.php');
}
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM enquiries WHERE id = ?")->execute([(int)$_GET['delete']]);
    flash_set('success', 'Enquiry deleted.');
    redirect('enquiries.php');
}

$enquiries = $pdo->query("SELECT * FROM enquiries ORDER BY is_read ASC, created_at DESC")->fetchAll();
include __DIR__ . '/includes/admin_header.php';
?>

<div class="table-wrap">
    <table class="data-table">
        <thead><tr><th>Name</th><th>Contact</th><th>Message</th><th>Received</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (!$enquiries): ?><tr><td colspan="5">No enquiries yet.</td></tr><?php endif; ?>
        <?php foreach ($enquiries as $en): ?>
            <tr style="<?= $en['is_read'] ? '' : 'background:var(--gold-100);' ?>">
                <td><?= e($en['name']) ?></td>
                <td><?= e($en['email'] ?: '') ?><?= $en['phone'] ? '<br>'.e($en['phone']) : '' ?></td>
                <td style="max-width:340px;"><?= nl2br(e(mb_strimwidth($en['message'], 0, 160, '…'))) ?></td>
                <td><?= date('d M Y', strtotime($en['created_at'])) ?></td>
                <td class="action-links">
                    <?php if (!$en['is_read']): ?><a href="enquiries.php?read=<?= (int)$en['id'] ?>">Mark read</a><?php endif; ?>
                    <a href="enquiries.php?delete=<?= (int)$en['id'] ?>" onclick="return confirm('Delete this enquiry?');" style="color:var(--rust);">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
