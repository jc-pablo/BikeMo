<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('user');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/bike_images.php';

if (empty($_SESSION['csrf_customer'])) {
    $_SESSION['csrf_customer'] = bin2hex(random_bytes(32));
}

$name = $_SESSION['name'] ?? 'Rider';
$initial = strtoupper(substr($name, 0, 1));
$current = 'bikes.php';
$e = fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$peso = fn($amount) => '₱' . number_format((float) $amount, 2);
$navItems = [
    'dashboard.php' => ['⌂', 'Dashboard'],
    'bikes.php' => ['🚲', 'Browse Bikes'],
    'cart.php' => ['🛒', 'My Cart'],
    'rentals.php' => ['▤', 'My Rentals']
];

function bmHeader($name, $initial, $current, $navItems, $e) { ?>
<header class="bm-header"><div class="container bm-header__inner">
    <a class="bm-brand" href="dashboard.php"><span class="bm-brand__icon">🚲</span><span class="bm-brand__text">Bike<b>Mo</b></span></a>
    <nav class="bm-nav" aria-label="Customer navigation">
        <?php foreach ($navItems as $file => [$icon, $label]): ?>
            <a href="<?= $file ?>" class="bm-nav__link<?= $current === $file ? ' is-active' : '' ?>"><span><?= $icon ?></span> <?= $label ?></a>
        <?php endforeach; ?>
    </nav>
    <div class="bm-user"><span class="bm-user__avatar"><?= $e($initial) ?></span>
        <div class="bm-user__info"><small>Signed in as</small><strong><?= $e($name) ?></strong></div>
        <a href="profile.php" class="bm-customer-profile-link">Profile</a>
        <a class="bm-user__logout" href="../logout.php">Logout ⏻</a>
    </div>
</div></header>
<?php }

$notice = '';
$error = '';
$limits = ['hours' => 72, 'days' => 30, 'weeks' => 8];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_bike'])) {
    $token = $_POST['csrf_token'] ?? '';
    $bikeId = (int) ($_POST['bike_id'] ?? 0);
    $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT);
    $unit = $_POST['unit'] ?? '';
    $pickup = trim($_POST['start_at'] ?? '');
    $startTime = strtotime($pickup);

    if (!is_string($token) || !hash_equals($_SESSION['csrf_customer'], $token)) {
        $error = 'Refresh the page and try again.';
    } elseif (!isset($limits[$unit]) || $quantity === false ||
              $quantity < 1 || $quantity > $limits[$unit]) {
        $error = 'Choose a valid duration (maximum 72 hours, 30 days or 8 weeks).';
    } elseif (!$startTime || $startTime < time()) {
        $error = 'Choose a future pick-up time.';
    } else {
        $stmt = $pdo->prepare('SELECT id, name, status FROM bikes WHERE id = ?');
        $stmt->execute([$bikeId]);
        $bike = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$bike || $bike['status'] !== 'available') {
            $error = 'This bike is no longer available.';
        } else {
            if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            $_SESSION['cart'][$bikeId] = [
                'bike_id' => $bikeId,
                'start_at' => date('Y-m-d H:i:s', $startTime),
                'quantity' => $quantity,
                'unit' => $unit
            ];
            $notice = $bike['name'] . ' added to cart.';
        }
    }
}

$bikes = $pdo->query("SELECT * FROM bikes ORDER BY status = 'available' DESC, name ASC")
    ->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Browse Bikes | BikeMo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/main.js" defer></script>
</head>
<body>
<?php bmHeader($name, $initial, $current, $navItems, $e); ?>
<main class="page customer-page"><div class="container">
    <section class="customer-hero">
        <small>YOUR NEXT ADVENTURE STARTS HERE</small>
        <h1>Find your kind of ride.</h1>
        <p>See real photos, compare rental rates, and choose an available bike.</p>
    </section>

    <?php if ($notice): ?><div class="customer-alert"><?= $e($notice) ?> <a href="cart.php">View cart →</a></div><?php endif; ?>
    <?php if ($error): ?><div class="customer-alert error"><?= $e($error) ?></div><?php endif; ?>

    <div class="customer-toolbar">
        <input id="bikeSearch" type="search" placeholder="Search bike name or category..." aria-label="Search bikes">
        <select id="bikeType" aria-label="Filter by category">
            <option value="">All bike types</option>
            <?php foreach (['Mountain', 'Road', 'City', 'Electric', 'Folding', 'BMX'] as $type): ?>
                <option value="<?= $type ?>"><?= $type ?></option>
            <?php endforeach; ?>
        </select>
        <span><?= count($bikes) ?> bikes in our fleet</span>
    </div>

    <div class="customer-grid" id="customerBikeGrid">
        <?php foreach ($bikes as $bike): ?>
            <?php $images = bikeImages($bike); ?>
            <article class="customer-bike" data-name="<?= $e(strtolower($bike['name'] . ' ' . $bike['type'])) ?>" data-type="<?= $e($bike['type']) ?>">
                <div class="customer-bike-art">
                    <?php if ($images && bikePhotoExists($images[0])): ?>
                        <img class="bm-bike-photo" src="../<?= $e($images[0]) ?>" alt="<?= $e($bike['name']) ?>" loading="lazy">
                    <?php else: ?>
                        <div class="bike-art-fallback">🚲</div>
                    <?php endif; ?>
                </div>
                <div class="customer-bike-body">
                    <div class="customer-pills"><span><?= $e($bike['type']) ?></span><span class="customer-status <?= $bike['status'] === 'available' ? '' : 'ongoing' ?>"><?= $e(ucfirst($bike['status'])) ?></span></div>
                    <h2><?= $e($bike['name']) ?></h2>
                    <a href="review.php?bike_id=<?= (int) $bike['id'] ?>">View details &amp; reviews →</a>
                    <p><?= $e($bike['description'] ?: 'Ready for a memorable ride.') ?></p>
                    <div class="customer-pills">
                        <span>⚙ <?= $e($bike['gears']) ?></span>
                        <span><?= $e($bike['frame']) ?></span>
                        <span><?= $e($bike['weight']) ?></span>
                    </div>
                    <div class="customer-price">
                        <div><strong><?= $peso($bike['rate_per_hour']) ?></strong><small> / hour</small></div>
                        <div><strong><?= $peso($bike['rate_per_day']) ?></strong><small> / day</small></div>
                    </div>

                    <?php if ($bike['status'] === 'available'): ?>
                        <form method="post" class="customer-form" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= $e($_SESSION['csrf_customer']) ?>">
                            <input type="hidden" name="bike_id" value="<?= (int) $bike['id'] ?>">
                            <label>Pick-up date/time
                                <input type="datetime-local" name="start_at" value="<?= date('Y-m-d\TH:i', time() + 3600) ?>">
                            </label>
                            <label>Duration unit
                                <select name="unit"><option value="hours">Hours</option><option value="days">Days</option><option value="weeks">Weeks (15% off)</option></select>
                            </label>
                            <label>Quantity<input type="number" name="quantity" value="2"></label>
                            <button class="btn btn-primary" name="add_bike" value="1" type="submit">Add to cart →</button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-outline" disabled>Unavailable</button>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (!$bikes): ?><div class="customer-empty"><h2>No bikes yet</h2><p>The admin can add the first bike from Manage Bikes.</p></div><?php endif; ?>
    </div>
</div></main>
<?php $assetPrefix = '../'; require __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
