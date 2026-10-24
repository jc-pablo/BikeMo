
<?php
require_once __DIR__ . '/includes/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid admin email.';
    } elseif (strlen($newPassword) < 8) {
        $message = 'Password must be at least 8 characters.';
    } else {
        $hashedPassword = password_hash(
            $newPassword,
            PASSWORD_DEFAULT
        );

        $stmt = $pdo->prepare(
            "UPDATE users
             SET password = ?
             WHERE email = ? AND role = 'admin'"
        );

        $stmt->execute([$hashedPassword, $email]);

        if ($stmt->rowCount() > 0) {
            $message = 'Admin password updated successfully!';
        } else {
            $check = $pdo->prepare(
                "SELECT id FROM users
                 WHERE email = ? AND role = 'admin'"
            );
            $check->execute([$email]);

            $message = $check->fetch()
                ? 'Password already matches this hash, or no change was made.'
                : 'No admin account found with that email.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset BikeMo Admin Password</title>
</head>
<body>
    <h2>Reset BikeMo Admin Password</h2>

    <?php if ($message !== ''): ?>
        <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Admin Email</label><br>
        <input type="email" name="email" required><br><br>

        <label>New Password</label><br>
        <input
            type="password"
            name="new_password"
            minlength="8"
            required
        ><br><br>

        <button type="submit">Reset Admin Password</button>
    </form>
</body>
</html>
