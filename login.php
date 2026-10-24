<?php
session_start();
require_once __DIR__ . '/includes/db.php';

$role = $_POST['role'] ?? $_GET['role'] ?? 'user';
$error = '';
$email = '';

if (!in_array($role, ['user', 'admin'], true)) {
    $role = 'user';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

   
$stmt = $pdo->prepare(
    'SELECT id, name, email, password AS password_hash, role
     FROM users
     WHERE email = ?
     LIMIT 1'
);


    $stmt->execute([$email]);
    $user = $stmt->fetch();

    
if (
    $user &&
    password_verify($password, $user['password_hash']) &&
    $user['role'] === $role
) {
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];

    if ($user['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: customers/dashboard.php');
    }

    exit;
} else {
    $error = 'Invalid email, password, or account type.';
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In | BikeMo</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="auth-wrap">
    <aside class="auth-side">
        <a class="logo" href="index.php" style="text-decoration:none">
            Bike<b>Mo</b>
        </a>

        <div>
            <div class="auth-bike" aria-hidden="true">
                🚲
            </div>
            <h2>EVERY RIDE <span>STARTS HERE.</span></h2>
            <p>
                Sign in to rent bikes, manage your cart, and track your
                rental history. Admins can manage the whole fleet.
            </p>
        </div>

        <small style="opacity:.6">© 2026 BikeMo</small>
    </aside>

    <main class="auth-main">
        <div class="auth-box">

            <a class="back-link" href="index.php"
               style="display:inline-block;text-decoration:none">
                ← Back to home
            </a>

            <div class="role-tabs">
               <a href="login.php?role=user"
                   class="<?= $role === 'user' ? 'on' : '' ?>"
                   style="text-align:center;text-decoration:none;padding:11px;border-radius:50px;font-weight:600;background:<?= $role === 'user' ? 'var(--navy)' : 'transparent' ?>;color:<?= $role === 'user' ? '#fff' : 'var(--muted)' ?>">
                    🚲 Customer
</a>
                <a href="login.php?role=admin"
                   class="<?= $role === 'admin' ? 'on' : '' ?>"
                   style="text-align:center;text-decoration:none;padding:11px;border-radius:50px;color:inherit;font-weight:600">
                    🛠️ Admin
                </a>
            </div>

            <h1>WELCOME BACK</h1>

            <p class="sub">
                <?= $role === 'admin'
                    ? 'Sign in to the admin panel to manage bikes and rentals.'
                    : 'Log in to rent a bike and view your history.' ?>
            </p>

            <?php if ($error !== ''): ?>
                <p role="alert"
                   style="background:#ffe8e8;color:#a51d2d;padding:12px;border-radius:10px;margin-bottom:16px">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <form method="POST" action="login.php" novalidate>
                <input type="hidden" name="role"
                       value="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>">

                <div class="field">
                    <label for="email">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        placeholder="you@email.com"
                        value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="email"
                        required>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="pw">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required>
                        <button type="button"
                                onclick="togglePassword()"
                                aria-label="Show or hide password">👁️</button>
                    </div>
                </div>

                <button class="btn btn-primary" type="submit">
                    Log In →
                </button>
            </form>

            <?php if ($role === 'user'): ?>
                <p class="switch">
                    Don't have an account?
                    <a href="register.php">Sign up</a>
                </p>
            <?php else: ?>
                <p class="switch">
                    Admin accounts are managed by the system.
                </p>
            <?php endif; ?>

        </div>
    </main>
</div>

<script src="assets/js/auth.js"></script>

</body>
</html>
