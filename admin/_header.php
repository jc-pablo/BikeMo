<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['csrf_admin'])) {
    $_SESSION['csrf_admin'] = bin2hex(random_bytes(32));
}

$adminPage = $adminPage ?? 'dashboard';
$adminTitle = $adminTitle ?? 'Dashboard';
$adminId = (int) ($_SESSION['user_id'] ?? 0);
$adminProfileQuery = $pdo->prepare('SELECT name, photo FROM users WHERE id = ?');
$adminProfileQuery->execute([$adminId]);
$adminProfile = $adminProfileQuery->fetch(PDO::FETCH_ASSOC) ?: [];
$adminName = $adminProfile['name'] ?? ($_SESSION['name'] ?? 'BikeMo Admin');
$adminPhoto = $adminProfile['photo'] ?? '';
$adminHasPhoto = is_string($adminPhoto)
    && preg_match('~^uploads/users/[a-zA-Z0-9_-]+\.(jpg|jpeg|png|webp)$~i', $adminPhoto)
    && is_file(__DIR__ . '/../' . $adminPhoto);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= adminEscape($adminTitle) ?> | BikeMo Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=profile-receipt-spacing-20261102">
</head>
<body class="bm-admin-body">
    <div class="bm-admin-shell">
        <aside class="bm-admin-sidebar">
            <a class="bm-admin-logo" href="dashboard.php">
                <span class="bm-admin-logo-icon">🚲</span>
                <span>Bike<b>Mo</b><small>ADMIN CONSOLE</small></span>
            </a>

            <div class="bm-admin-nav-label">WORKSPACE</div>
            <nav class="bm-admin-nav">
                <a class="<?= $adminPage === 'dashboard' ? 'active' : '' ?>" href="dashboard.php"><span>▦</span> Dashboard</a>
                <a class="<?= $adminPage === 'bikes' ? 'active' : '' ?>" href="bikes.php"><span>🚲</span> Manage Bikes</a>
                <a class="<?= $adminPage === 'rentals' ? 'active' : '' ?>" href="rentals.php"><span>▤</span> Rentals & Bookings</a>
                <a class="<?= $adminPage === 'expenses' ? 'active' : '' ?>" href="expenses.php"><span>₱</span> Expenses</a>
                <a class="<?= $adminPage === 'users' ? 'active' : '' ?>" href="users.php"><span>♙</span> Manage Users</a>
            </nav>

            <div class="bm-admin-sidebar-bottom">
                <a class="bm-admin-profile" href="profile.php" aria-label="Edit administrator profile">
                    <?php if ($adminHasPhoto): ?>
                        <span class="bm-admin-avatar-frame"><img class="bm-admin-avatar-photo" width="38" height="38" src="../<?= adminEscape($adminPhoto) ?>" alt="<?= adminEscape($adminName) ?> profile photo"></span>
                    <?php else: ?>
                        <span class="bm-admin-avatar"><?= adminEscape(mb_strtoupper(mb_substr(trim($adminName), 0, 1, 'UTF-8'))) ?></span>
                    <?php endif; ?>
                    <span><b><?= adminEscape($adminName) ?></b><small>Edit my profile</small></span>
                </a>
                <a class="bm-admin-logout" href="../logout.php">↪ &nbsp; Log out</a>
            </div>
        </aside>

        <main class="bm-admin-main">
            <header class="bm-admin-topbar">
                <div>
                    <span class="bm-admin-eyebrow">BIKEMO MANAGEMENT</span>
                    <h1><?= adminEscape($adminTitle) ?></h1>
                </div>
                <a href="../index.php" class="bm-admin-viewsite" target="_blank" rel="noopener">View website ↗</a>
            </header>

            <div class="bm-admin-content">
