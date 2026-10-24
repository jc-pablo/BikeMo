<?php
session_start();
require_once __DIR__ . '/includes/db.php';

$errors = [];
$name = '';
$email = '';
$phone = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate the form
    if (
        $name === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $phone === '' ||
        strlen($password) < 8 ||
        $password !== $confirmPassword
    ) {
        $errors[] = 'Check your name, email, phone, and password.';
    }

    // Save only when there are no validation errors
    if (empty($errors)) {
        $hashedPassword = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        try {
            $stmt = $pdo->prepare(
    'INSERT INTO users (name, email, phone, password, role)
     VALUES (?, ?, ?, ?, ?)'
);

            $stmt->execute([
                $name,
                $email,
                $phone,
                $hashedPassword,
                'user'
            ]);

            header('Location: login.php?role=user&registered=1');
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = 'That email address is already registered.';
            } else {
                error_log($e->getMessage());

$errors[] = 'Registration failed: ' .
    htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | BikeMo</title>

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
                Join BikeMo to explore bikes, manage your rentals,
                and keep track of your riding history.
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
                   style="text-align:center;text-decoration:none;padding:11px;border-radius:50px;color:var(--muted);font-weight:600">
                    🚲 Customer
                </a>
                <a href="login.php?role=admin"
                   style="text-align:center;text-decoration:none;padding:11px;border-radius:50px;color:var(--muted);font-weight:600">
                    🛠️ Admin
                </a>
            </div>

            <h1>JOIN BIKEMO</h1>
            <p class="sub">Create your account in under a minute.</p>

            <?php if ($errors): ?>
                <div role="alert"
                     style="background:#ffe8e8;color:#a51d2d;padding:12px 16px;border-radius:10px;margin-bottom:16px">
                    <ul style="padding-left:20px">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php" novalidate>
                <div class="field">
                    <label for="name">Full Name</label>
                    <input
                        id="name"
                        name="name"
                        placeholder="Juan Dela Cruz"
                        value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="name"
                        required>
                </div>

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
                    <label for="phone">Phone Number</label>
                    <input
                        id="phone"
                        name="phone"
                        type="tel"
                        placeholder="09XX XXX XXXX"
                        value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="tel"
                        required>
                </div>

                <div class="two">
                    <div class="field">
                        <label for="password">Password</label>
                        <div class="pw">
                            <input
                                id="password"
                                name="password"
                                type="password"
                                placeholder="••••••••"
                                autocomplete="new-password"
                                required>
                            <button type="button"
                                    onclick="togglePassword('password')"
                                    aria-label="Show or hide password">👁️</button>
                        </div>
                    </div>

                    <div class="field">
                        <label for="confirm_password">Confirm Password</label>
                        <input
                            id="confirm_password"
                            name="confirm_password"
                            type="password"
                            placeholder="••••••••"
                            autocomplete="new-password"
                            required>
                    </div>
                </div>

                <button class="btn btn-primary" type="submit">
                    Create Account →
                </button>
            </form>

            <p class="switch">
                Already registered?
                <a href="login.php">Log in</a>
            </p>

        </div>
    </main>
</div>

<script src="assets/js/auth.js"></script>

</body>
</html>
