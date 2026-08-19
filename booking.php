<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Book a Hall';
$halls = $pdo->query("SELECT * FROM halls WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
$preselect = isset($_GET['hall']) ? (int)$_GET['hall'] : 0;

$errors = [];
$old = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $hall_id       = (int)($_POST['hall_id'] ?? 0);
    $name          = trim($_POST['requester_name'] ?? '');
    $organization  = trim($_POST['organization'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $nic           = trim($_POST['nic'] ?? '');
    $event_title   = trim($_POST['event_title'] ?? '');
    $event_date    = $_POST['event_date'] ?? '';
    $start_time    = $_POST['start_time'] ?? '';
    $end_time      = $_POST['end_time'] ?? '';
    $participants  = (int)($_POST['participants'] ?? 0);
    $ac_required   = isset($_POST['ac_required']) ? 1 : 0;
    $notes         = trim($_POST['notes'] ?? '');

    if (!$hall_id) $errors[] = 'Please select a hall.';
    if (!$name) $errors[] = 'Please enter your full name.';
    if (!$phone) $errors[] = 'Please enter a contact phone number.';
    if (!$event_date || strtotime($event_date) < strtotime(date('Y-m-d'))) $errors[] = 'Please select a valid, upcoming event date.';
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';

    if (!$errors) {
        $stmt = $pdo->prepare("
            INSERT INTO hall_bookings
                (hall_id, requester_name, organization, phone, email, nic, event_title, event_date, start_time, end_time, participants, ac_required, notes)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $hall_id, $name, $organization ?: null, $phone, $email ?: null, $nic ?: null,
            $event_title ?: null, $event_date, $start_time ?: null, $end_time ?: null,
            $participants ?: null, $ac_required, $notes ?: null,
        ]);
        $bookingId = $pdo->lastInsertId();
        redirect(SITE_URL . 'booking_success.php?ref=' . $bookingId);
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="section--tight">
    <div class="container">
        <div class="eyebrow">Online Booking</div>
        <h1>Book a Lecture Hall</h1>
        <p class="lede">Submit your request below. Our administration team will review it and confirm availability — you'll be able to track the status with the reference number shown after submitting.</p>
    </div>
</section>

<section class="section" style="padding-top:0;">
    <div class="container">
        <div class="grid" style="grid-template-columns: 1fr 320px; gap:32px; align-items:flex-start;">

            <div class="card">
                <?php if ($errors): ?>
                    <div class="alert alert-error">
                        <strong>Please fix the following:</strong>
                        <ul style="margin:8px 0 0 18px;">
                            <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= SITE_URL ?>booking.php">
                    <?= csrf_field() ?>
                    <div class="form-grid">
                        <div class="field full">
                            <label for="hall_id">Select hall *</label>
                            <select id="hall_id" name="hall_id" required>
                                <option value="">— Choose a hall —</option>
                                <?php foreach ($halls as $h): ?>
                                <option value="<?= (int)$h['id'] ?>"
                                    data-ac="<?= $h['price_ac'] ? number_format($h['price_ac'], 2) : '' ?>"
                                    data-nonac="<?= $h['price_non_ac'] ? number_format($h['price_non_ac'], 2) : '' ?>"
                                    data-cap="<?= (int)$h['capacity_max'] ?>"
                                    data-hasac="<?= (int)$h['has_ac'] ?>"
                                    <?= ($preselect === (int)$h['id'] || (isset($old['hall_id']) && (int)$old['hall_id'] === (int)$h['id'])) ? 'selected' : '' ?>>
                                    <?= e($h['name_en']) ?> (<?= e($h['name_si']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="hall-price-preview"></div>
                        </div>

                        <div class="field">
                            <label for="requester_name">Full name *</label>
                            <input type="text" id="requester_name" name="requester_name" required value="<?= e($old['requester_name'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="organization">Organisation</label>
                            <input type="text" id="organization" name="organization" value="<?= e($old['organization'] ?? '') ?>">
                        </div>

                        <div class="field">
                            <label for="phone">Phone number *</label>
                            <input type="tel" id="phone" name="phone" required value="<?= e($old['phone'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= e($old['email'] ?? '') ?>">
                        </div>

                        <div class="field">
                            <label for="nic">NIC number</label>
                            <input type="text" id="nic" name="nic" value="<?= e($old['nic'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="participants">Expected participants</label>
                            <input type="number" id="participants" name="participants" min="1" value="<?= e($old['participants'] ?? '') ?>">
                        </div>

                        <div class="field full">
                            <label for="event_title">Event / training programme title</label>
                            <input type="text" id="event_title" name="event_title" value="<?= e($old['event_title'] ?? '') ?>">
                        </div>

                        <div class="field">
                            <label for="event_date">Event date *</label>
                            <input type="date" id="event_date" name="event_date" required value="<?= e($old['event_date'] ?? '') ?>">
                        </div>
                        <div class="field" id="ac_required_wrap" style="display:flex; flex-direction:row; align-items:center; gap:8px; margin-top:26px;">
                            <input type="checkbox" id="ac_required" name="ac_required" style="width:auto;" <?= !empty($old['ac_required']) ? 'checked' : '' ?>>
                            <label for="ac_required" style="margin:0;">I need A/C</label>
                        </div>

                        <div class="field">
                            <label for="start_time">Start time</label>
                            <input type="time" id="start_time" name="start_time" value="<?= e($old['start_time'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="end_time">End time</label>
                            <input type="time" id="end_time" name="end_time" value="<?= e($old['end_time'] ?? '') ?>">
                        </div>

                        <div class="field full">
                            <label for="notes">Additional notes</label>
                            <textarea id="notes" name="notes" placeholder="Catering needs, equipment requests, etc."><?= e($old['notes'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top:22px;">Submit Booking Request</button>
                </form>
            </div>

            <div class="card" style="background:var(--sage); border:none;">
                <h3>Before you book</h3>
                <ul style="padding-left:18px; color:var(--ink-soft); font-size:.9rem;">
                    <li>Halls are available 9.00am–5.00pm; after-hours use is charged extra.</li>
                    <li>Bookings are <strong>requests</strong> — you'll receive a reference number now, and confirmation once the administration team reviews it.</li>
                    <li>For accommodation and catering, mention your requirements in the notes field or contact us directly.</li>
                    <li>Damage to institute property may incur additional charges.</li>
                </ul>
                <p style="font-size:.85rem;">Need help? Call <strong>037 2267370</strong> or email <a href="mailto:wtiwariyapola@gmail.com">wtiwariyapola@gmail.com</a>.</p>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
