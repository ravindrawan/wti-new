<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Contact';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$name) $errors[] = 'Please enter your name.';
    if (!$message) $errors[] = 'Please enter a message.';
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email.';

    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO enquiries (name, email, phone, message) VALUES (?,?,?,?)");
        $stmt->execute([$name, $email ?: null, $phone ?: null, $message]);
        flash_set('success', 'Thank you — your message has been sent. We will get back to you soon.');
        redirect(SITE_URL . 'contact.php');
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="section--tight">
    <div class="container">
        <div class="eyebrow">Get in touch</div>
        <h1>Contact Us</h1>
    </div>
</section>

<section class="section" style="padding-top:0;">
    <div class="container grid" style="grid-template-columns: 1fr 1fr; gap:32px; align-items:flex-start;">
        <div class="card">
            <?php if ($errors): ?>
                <div class="alert alert-error"><?php foreach ($errors as $e2) echo e($e2) . '<br>'; ?></div>
            <?php endif; ?>
            <form method="POST" action="<?= SITE_URL ?>contact.php">
                <?= csrf_field() ?>
                <div class="field" style="margin-bottom:14px;"><label for="name">Name *</label><input type="text" id="name" name="name" required></div>
                <div class="field" style="margin-bottom:14px;"><label for="email">Email</label><input type="email" id="email" name="email"></div>
                <div class="field" style="margin-bottom:14px;"><label for="phone">Phone</label><input type="tel" id="phone" name="phone"></div>
                <div class="field" style="margin-bottom:14px;"><label for="message">Message *</label><textarea id="message" name="message" required></textarea></div>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
        <div class="card" style="background:var(--sage); border:none;">
            <h3>Institute details</h3>
            <p><strong>Address:</strong> Wayamba Training Institute, Wariyapola, Sri Lanka</p>
            <p><strong>Telephone:</strong> 037 2267370</p>
            <p><strong>Fax:</strong> 037 2057547</p>
            <p><strong>Email:</strong> <a href="mailto:wtiwariyapola@gmail.com">wtiwariyapola@gmail.com</a></p>
            <p><strong>Hall hours:</strong> 9.00am – 5.00pm daily</p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
