<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/helpers.php';
requireRole('admin');
if (empty($_SESSION['csrf_admin'])) {
    $_SESSION['csrf_admin'] = bin2hex(random_bytes(32));
}
$adminPage = 'users';
$adminTitle = 'Manage Users';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $role = $_POST['role'] ?? '';
    $active = (int) ($_POST['is_active'] ?? -1);
    if (!hash_equals($_SESSION['csrf_admin'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } elseif ($id === (int) $_SESSION['user_id']) {
        $error = 'You cannot change your own admin access here.';
    } elseif (!in_array($role, ['user', 'admin'], true) || !in_array($active, [0, 1], true)) {
        $error = 'Invalid role or account status.';
    } else {
        $stmt = $pdo->prepare('UPDATE users SET role = ?, is_active = ? WHERE id = ?');
        $stmt->execute([$role, $active, $id]);
        $message = 'User updated.';
    }
}
$users = $pdo->query('SELECT id, name, email, role, is_active, photo FROM users ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
require __DIR__ . '/_header.php';
?>
<div class="bm-admin-panel" style="padding:24px">
<h2>Customer and admin accounts</h2>
<p><?= adminEscape($message ?: $error) ?></p>
<div class="tbl-wrap"><table><thead><tr><th>Account</th><th>Email</th><th>Role</th><th>Active</th><th>Save</th></tr></thead><tbody>
<?php foreach ($users as $user): ?>
<tr><td><?= adminEscape($user['name']) ?></td><td><?= adminEscape($user['email']) ?></td>
<td colspan="3"><form method="post" class="bm-admin-inline-form">
<input type="hidden" name="csrf_token" value="<?= adminEscape($_SESSION['csrf_admin']) ?>">
<input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
<select name="role"><option value="user" <?= $user['role']==='user'?'selected':'' ?>>User</option><option value="admin" <?= $user['role']==='admin'?'selected':'' ?>>Admin</option></select>
<select name="is_active"><option value="1" <?= $user['is_active']?'selected':'' ?>>Active</option><option value="0" <?= !$user['is_active']?'selected':'' ?>>Deactivated</option></select>
<button class="btn btn-primary btn-sm">Save</button></form></td></tr>
<?php endforeach; ?>
</tbody></table></div></div>
<?php require __DIR__ . '/_footer.php'; ?>
