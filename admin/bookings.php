<?php
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Hall Bookings';
$active = 'bookings';

$statusFilter = $_GET['status'] ?? 'all';
$sql = "SELECT b.*, h.name_en AS hall_name FROM hall_bookings b JOIN halls h ON h.id = b.hall_id";
$params = [];
if ($statusFilter !== 'all') {
    $sql .= " WHERE b.status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY b.event_date ASC, b.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$counts = $pdo->query("SELECT status, COUNT(*) c FROM hall_bookings GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

include __DIR__ . '/includes/admin_header.php';
?>

<div class="tabs">
    <a class="tab-link <?= $statusFilter === 'all' ? 'active' : '' ?>" href="bookings.php?status=all">All (<?= array_sum($counts) ?>)</a>
    <a class="tab-link <?= $statusFilter === 'pending' ? 'active' : '' ?>" href="bookings.php?status=pending">Pending (<?= $counts['pending'] ?? 0 ?>)</a>
    <a class="tab-link <?= $statusFilter === 'approved' ? 'active' : '' ?>" href="bookings.php?status=approved">Approved (<?= $counts['approved'] ?? 0 ?>)</a>
    <a class="tab-link <?= $statusFilter === 'rejected' ? 'active' : '' ?>" href="bookings.php?status=rejected">Rejected (<?= $counts['rejected'] ?? 0 ?>)</a>
    <a class="tab-link <?= $statusFilter === 'cancelled' ? 'active' : '' ?>" href="bookings.php?status=cancelled">Cancelled (<?= $counts['cancelled'] ?? 0 ?>)</a>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead><tr><th>Ref</th><th>Hall</th><th>Requester</th><th>Phone</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (!$bookings): ?><tr><td colspan="7">No bookings found.</td></tr><?php endif; ?>
        <?php foreach ($bookings as $b): ?>
            <tr>
                <td>WTI-<?= str_pad($b['id'], 5, '0', STR_PAD_LEFT) ?></td>
                <td><?= e($b['hall_name']) ?></td>
                <td><?= e($b['requester_name']) ?></td>
                <td><?= e($b['phone']) ?></td>
                <td><?= format_date($b['event_date']) ?></td>
                <td><span class="badge badge-<?= e($b['status']) ?>"><?= ucfirst(e($b['status'])) ?></span></td>
                <td><a href="booking_view.php?id=<?= (int)$b['id'] ?>">Review →</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
