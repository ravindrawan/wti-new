<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Booking Received';
$ref = (int)($_GET['ref'] ?? 0);

$booking = null;
if ($ref) {
    $stmt = $pdo->prepare("
        SELECT b.*, h.name_en AS hall_name_en, h.name_si AS hall_name_si
        FROM hall_bookings b JOIN halls h ON h.id = b.hall_id
        WHERE b.id = ?
    ");
    $stmt->execute([$ref]);
    $booking = $stmt->fetch();
}

include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container" style="max-width:640px;">
        <?php if ($booking): ?>
            <div class="card text-center">
                <div class="card-icon" style="margin:0 auto 16px;">✓</div>
                <h1>Booking request received</h1>
                <p class="lede" style="margin:0 auto 20px;">Thank you, <?= e($booking['requester_name']) ?>. Your request has been logged. Keep your reference number to track its status.</p>

                <div class="ledger" style="text-align:left; margin-bottom:22px;">
                    <div class="ledger-head"><span class="num">#</span><h3>Reference: WTI-<?= str_pad($booking['id'], 5, '0', STR_PAD_LEFT) ?></h3></div>
                    <table>
                        <tr><th>Hall</th><td><?= e($booking['hall_name_en']) ?></td></tr>
                        <tr><th>Date</th><td><?= format_date($booking['event_date']) ?></td></tr>
                        <tr><th>Status</th><td><span class="badge badge-pending">Pending review</span></td></tr>
                    </table>
                </div>

                <a href="<?= SITE_URL ?>track_booking.php?ref=<?= (int)$booking['id'] ?>" class="btn btn-teal">Track this booking</a>
                <a href="<?= SITE_URL ?>index.php" class="btn btn-ghost">Back to home</a>
            </div>
        <?php else: ?>
            <div class="alert alert-error">We could not find that booking reference.</div>
            <a href="<?= SITE_URL ?>booking.php" class="btn btn-teal">Make a booking</a>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
