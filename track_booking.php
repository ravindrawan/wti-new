<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Track Booking';
$ref = trim($_GET['ref'] ?? '');
$phone = trim($_GET['phone'] ?? '');
$booking = null;
$searched = $ref !== '' || $phone !== '';

if ($searched && $ref !== '') {
    $stmt = $pdo->prepare("
        SELECT b.*, h.name_en AS hall_name_en, h.name_si AS hall_name_si
        FROM hall_bookings b JOIN halls h ON h.id = b.hall_id
        WHERE b.id = ? " . ($phone !== '' ? "AND b.phone = ?" : "")
    );
    $params = [$ref];
    if ($phone !== '') $params[] = $phone;
    $stmt->execute($params);
    $booking = $stmt->fetch();
}

include __DIR__ . '/includes/header.php';
?>

<section class="section--tight">
    <div class="container">
        <div class="eyebrow">Booking Status</div>
        <h1>Track Your Booking</h1>
        <p class="lede">Enter the reference number you received after submitting your request (found in your confirmation page/email).</p>
    </div>
</section>

<section class="section" style="padding-top:0;">
    <div class="container" style="max-width:640px;">
        <div class="card">
            <form method="GET" action="<?= SITE_URL ?>track_booking.php">
                <div class="form-grid">
                    <div class="field">
                        <label for="ref">Reference number *</label>
                        <input type="number" id="ref" name="ref" required placeholder="e.g. 12" value="<?= e($ref) ?>">
                    </div>
                    <div class="field">
                        <label for="phone">Phone used at booking (optional)</label>
                        <input type="tel" id="phone" name="phone" value="<?= e($phone) ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top:16px;">Check Status</button>
            </form>
        </div>

        <?php if ($searched): ?>
            <?php if ($booking): ?>
                <div class="ledger" style="margin-top:24px;">
                    <div class="ledger-head"><span class="num">#</span><h3>WTI-<?= str_pad($booking['id'], 5, '0', STR_PAD_LEFT) ?> — <?= e($booking['hall_name_en']) ?></h3></div>
                    <table>
                        <tr><th>Requester</th><td><?= e($booking['requester_name']) ?></td></tr>
                        <tr><th>Event date</th><td><?= format_date($booking['event_date']) ?></td></tr>
                        <tr><th>Time</th><td><?= $booking['start_time'] ? e(substr($booking['start_time'],0,5)) . ' – ' . e(substr($booking['end_time'],0,5)) : 'Not specified' ?></td></tr>
                        <tr><th>Status</th><td>
                            <?php $s = $booking['status']; ?>
                            <span class="badge badge-<?= e($s) ?>"><?= ucfirst(e($s)) ?></span>
                        </td></tr>
                        <?php if ($booking['admin_remark']): ?>
                        <tr><th>Note from admin</th><td><?= e($booking['admin_remark']) ?></td></tr>
                        <?php endif; ?>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-error" style="margin-top:24px;">No booking found for that reference (and phone, if provided). Please check and try again.</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
