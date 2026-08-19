<?php
require_once __DIR__ . '/includes/auth.php';
$active = 'bookings';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT b.*, h.name_en AS hall_name, h.name_si AS hall_name_si, h.price_ac, h.price_non_ac FROM hall_bookings b JOIN halls h ON h.id = b.hall_id WHERE b.id = ?");
$stmt->execute([$id]);
$booking = $stmt->fetch();

if (!$booking) {
    flash_set('error', 'Booking not found.');
    redirect('bookings.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $status = $_POST['status'] ?? 'pending';
    $remark = trim($_POST['admin_remark'] ?? '');
    $allowed = ['pending','approved','rejected','cancelled'];
    if (in_array($status, $allowed, true)) {
        $pdo->prepare("UPDATE hall_bookings SET status = ?, admin_remark = ? WHERE id = ?")
            ->execute([$status, $remark ?: null, $id]);
        flash_set('success', 'Booking status updated.');
        redirect('booking_view.php?id=' . $id);
    }
}

$page_title = 'Booking WTI-' . str_pad($booking['id'], 5, '0', STR_PAD_LEFT);
include __DIR__ . '/includes/admin_header.php';
?>

<a href="bookings.php" class="btn btn-ghost btn-sm" style="margin-bottom:18px;">← Back to bookings</a>

<div class="grid" style="grid-template-columns: 1fr 340px; gap:26px; align-items:flex-start;">
    <div class="ledger">
        <div class="ledger-head"><span class="num">#</span><h3><?= e($booking['hall_name']) ?> — <?= format_date($booking['event_date']) ?></h3></div>
        <table>
            <tr><th>Requester</th><td><?= e($booking['requester_name']) ?></td></tr>
            <tr><th>Organisation</th><td><?= e($booking['organization'] ?: '—') ?></td></tr>
            <tr><th>Phone</th><td><?= e($booking['phone']) ?></td></tr>
            <tr><th>Email</th><td><?= e($booking['email'] ?: '—') ?></td></tr>
            <tr><th>NIC</th><td><?= e($booking['nic'] ?: '—') ?></td></tr>
            <tr><th>Event / programme</th><td><?= e($booking['event_title'] ?: '—') ?></td></tr>
            <tr><th>Date</th><td><?= format_date($booking['event_date']) ?></td></tr>
            <tr><th>Time</th><td><?= $booking['start_time'] ? e(substr($booking['start_time'],0,5)).' – '.e(substr($booking['end_time'],0,5)) : 'Not specified' ?></td></tr>
            <tr><th>Participants</th><td><?= (int)$booking['participants'] ?: '—' ?></td></tr>
            <tr><th>A/C requested</th><td><?= $booking['ac_required'] ? 'Yes' : 'No' ?></td></tr>
            <tr><th>Notes</th><td><?= nl2br(e($booking['notes'] ?: '—')) ?></td></tr>
            <tr><th>Submitted</th><td><?= date('d M Y, h:i A', strtotime($booking['created_at'])) ?></td></tr>
        </table>
    </div>

    <div class="card">
        <h3>Update status</h3>
        <p style="font-size:.85rem; color:var(--ink-soft);">Current: <span class="badge badge-<?= e($booking['status']) ?>"><?= ucfirst(e($booking['status'])) ?></span></p>
        <form method="POST">
            <?= csrf_field() ?>
            <div class="field" style="margin-bottom:12px;">
                <label>Status</label>
                <select name="status">
                    <?php foreach (['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','cancelled'=>'Cancelled'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $booking['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field" style="margin-bottom:16px;">
                <label>Remark to requester (optional)</label>
                <textarea name="admin_remark" placeholder="e.g. Confirmed, please arrive by 8.30am."><?= e($booking['admin_remark'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Save Status</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
