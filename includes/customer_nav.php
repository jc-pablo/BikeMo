<?php
// Shared top navigation for customer pages.
$customerName = $_SESSION['name'] ?? 'Customer';
$customerPage = basename($_SERVER['SCRIPT_NAME']);
$customerEscape = fn($text) => htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
$customerLinks = [
    'dashboard.php' => ['⌂', 'Dashboard'],
    'bikes.php' => ['🚲', 'Browse Bikes'],
    'cart.php' => ['🛒', 'My Cart'],
    'rentals.php' => ['▤', 'My Rentals']
];
?>
<header class="bm-header">
    <div class="container bm-header__inner">
        <a class="bm-brand" href="dashboard.php"><span class="bm-brand__icon">🚲</span><span class="bm-brand__text">Bike<b>Mo</b></span></a>
        <nav class="bm-nav" aria-label="Customer navigation">
            <?php foreach ($customerLinks as $page => [$icon, $label]): ?>
                <a href="<?= $page ?>" class="bm-nav__link<?= $customerPage === $page ? ' is-active' : '' ?>">
                    <span><?= $icon ?></span> <?= $label ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="bm-user">
            <span class="bm-user__avatar"><?= $customerEscape(strtoupper(substr($customerName, 0, 1))) ?></span>
            <div class="bm-user__info"><small>Signed in as</small><strong><?= $customerEscape($customerName) ?></strong></div>
            <a href="profile.php" class="bm-customer-profile-link">Profile</a>
            <a class="bm-user__logout" href="../logout.php">Logout ⏻</a>
        </div>
    </div>
</header>
