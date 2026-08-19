<?php
require_once __DIR__ . '/includes/auth.php';

$page_title = 'Dashboard';
$active = 'dashboard';

$totalHalls   = $pdo->query("SELECT COUNT(*) FROM halls WHERE is_active = 1")->fetchColumn();
$pendingCount = $pdo->query("SELECT COUNT(*) FROM hall_bookings WHERE status = 'pending'")->fetchColumn();
$approvedCount= $pdo->query("SELECT COUNT(*) FROM hall_bookings WHERE status = 'approved' AND event_date >= CURDATE()")->fetchColumn();
$enquiryCount = $pdo->query("SELECT COUNT(*) FROM enquiries WHERE is_read = 0")->fetchColumn();

$recentBookings = $pdo->query("
    SELECT b.*, h.name_en AS hall_name
    FROM hall_bookings b JOIN halls h ON h.id = b.hall_id
    ORDER BY b.created_at DESC LIMIT 8
")->fetchAll();

include __DIR__ . '/includes/admin_header.php';
?>

<div class="stat-grid">
    <div class="stat-card"><div class="n"><?= (int)$pendingCount ?></div><div class="l">Pending Bookings</div></div>
    <div class="stat-card"><div class="n"><?= (int)$approvedCount ?></div><div class="l">Upcoming Confirmed</div></div>
    <div class="stat-card"><div class="n"><?= (int)$totalHalls ?></div><div class="l">Active Halls</div></div>
    <div class="stat-card"><div class="n"><?= (int)$enquiryCount ?></div><div class="l">Unread Enquiries</div></div>
</div>

<div class="flex-between" style="margin-bottom:14px;">
    <h3 class="mb-0">Recent booking requests</h3>
    <a href="bookings.php" class="btn btn-ghost btn-sm">View all →</a>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr><th>Ref</th><th>Hall</th><th>Requester</th><th>Date</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
        <?php if (!$recentBookings): ?>
            <tr><td colspan="6">No bookings yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($recentBookings as $b): ?>
            <tr>
                <td>WTI-<?= str_pad($b['id'], 5, '0', STR_PAD_LEFT) ?></td>
                <td><?= e($b['hall_name']) ?></td>
                <td><?= e($b['requester_name']) ?></td>
                <td><?= format_date($b['event_date']) ?></td>
                <td><span class="badge badge-<?= e($b['status']) ?>"><?= ucfirst(e($b['status'])) ?></span></td>
                <td><a href="booking_view.php?id=<?= (int)$b['id'] ?>">Review →</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
