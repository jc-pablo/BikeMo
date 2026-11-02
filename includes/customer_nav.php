<?php
// Shared customer header. Fetch the latest profile photo so replacements appear immediately.
$customerId = (int) ($_SESSION['user_id'] ?? 0);
$customerQuery = $pdo->prepare('SELECT name, photo FROM users WHERE id = ?');
$customerQuery->execute([$customerId]);
$customerAccount = $customerQuery->fetch(PDO::FETCH_ASSOC) ?: [];
$customerName = $customerAccount['name'] ?? ($_SESSION['name'] ?? 'Customer');
$customerInitial = strtoupper(mb_substr(trim($customerName), 0, 1, 'UTF-8'));
$customerPhoto = $customerAccount['photo'] ?? '';
$customerHasPhoto = is_string($customerPhoto)
    && preg_match('~^uploads/users/[a-zA-Z0-9_-]+\.(jpg|jpeg|png|webp)$~i', $customerPhoto)
    && is_file(__DIR__ . '/../' . $customerPhoto);
$customerPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$customerEscape = fn($text) => htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
$customerLinks = [
    'dashboard.php' => ['⌂', 'Dashboard'],
    'bikes.php'     => ['🚲', 'Browse Bikes'],
    'cart.php'      => ['🛒', 'My Cart'],
    'rentals.php'   => ['▤', 'My Rentals']
];
?>
<header class="bm-header">
    <div class="container bm-header__inner">
        <a class="bm-brand" href="dashboard.php">
            <span class="bm-brand__icon">🚲</span>
            <span class="bm-brand__text">Bike<b>Mo</b></span>
        </a>
        <nav class="bm-nav" aria-label="Customer navigation">
            <?php foreach ($customerLinks as $file => [$icon, $label]): ?>
                <a href="<?= $customerEscape($file) ?>" class="bm-nav__link<?= $customerPage === $file ? ' is-active' : '' ?>"<?= $customerPage === $file ? ' aria-current="page"' : '' ?>>
                    <span class="bm-nav__icon"><?= $icon ?></span><span><?= $customerEscape($label) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="bm-user">
            <a class="bm-user__avatar-link" href="profile.php" aria-label="View your profile">
                <?php if ($customerHasPhoto): ?>
                    <img class="bm-user__avatar-image" width="40" height="40" src="../<?= $customerEscape($customerPhoto) ?>" alt="<?= $customerEscape($customerName) ?> profile photo">
                <?php else: ?>
                    <span class="bm-user__avatar"><?= $customerEscape($customerInitial) ?></span>
                <?php endif; ?>
            </a>
            <div class="bm-user__info">
                <small>Signed in as</small>
                <strong><?= $customerEscape($customerName) ?></strong>
            </div>
            <a href="profile.php" class="bm-customer-profile-link">My Profile</a>
            <a class="bm-user__logout" href="../logout.php">Logout ⏻</a>
        </div>
    </div>
</header>
