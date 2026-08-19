<?php
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Admin Users';
$active = 'admins';

if (current_admin()['role'] !== 'super_admin') {
    flash_set('error', 'Only super admins can manage admin users.');
    redirect('dashboard.php');
}

if (isset($_GET['delete'])) {
    if ((int)$_GET['delete'] !== (int)current_admin()['id']) {
        $pdo->prepare("DELETE FROM admins WHERE id = ?")->execute([(int)$_GET['delete']]);
        flash_set('success', 'Admin user removed.');
    } else {
        flash_set('error', 'You cannot delete your own account while logged in.');
    }
    redirect('admins.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username']);
    $fullName = trim($_POST['full_name']);
    $role = $_POST['role'] === 'super_admin' ? 'super_admin' : 'editor';
    $password = $_POST['password'];

    if (strlen($password) < 8) {
        flash_set('error', 'Password must be at least 8 characters.');
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO admins (username, password_hash, full_name, role) VALUES (?,?,?,?)")
            ->execute([$username, $hash, $fullName, $role]);
        flash_set('success', 'Admin user created.');
    }
    redirect('admins.php');
}

$admins = $pdo->query("SELECT * FROM admins ORDER BY id")->fetchAll();
include __DIR__ . '/includes/admin_header.php';
?>

<div class="grid" style="grid-template-columns: 1fr 340px; gap:26px; align-items:flex-start;">
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Username</th><th>Full name</th><th>Role</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($admins as $a): ?>
                <tr>
                    <td><?= e($a['username']) ?></td>
                    <td><?= e($a['full_name']) ?></td>
                    <td><?= e($a['role']) ?></td>
                    <td class="action-links">
                        <?php if ((int)$a['id'] !== (int)current_admin()['id']): ?>
                            <a href="admins.php?delete=<?= (int)$a['id'] ?>" onclick="return confirm('Remove this admin user?');" style="color:var(--rust);">Delete</a>
                        <?php else: ?>
                            <span style="color:var(--ink-soft);">(you)</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3>Add admin user</h3>
        <form method="POST">
            <?= csrf_field() ?>
            <div class="field" style="margin-bottom:12px;"><label>Username</label><input type="text" name="username" required></div>
            <div class="field" style="margin-bottom:12px;"><label>Full name</label><input type="text" name="full_name"></div>
            <div class="field" style="margin-bottom:12px;"><label>Password (min 8 chars)</label><input type="password" name="password" required minlength="8"></div>
            <div class="field" style="margin-bottom:16px;">
                <label>Role</label>
                <select name="role">
                    <option value="editor">Editor</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Create Admin</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
