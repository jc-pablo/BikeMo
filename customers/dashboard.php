<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('user');
require_once __DIR__ . '/../includes/db.php';

$name      = $_SESSION['name'] ?? 'Customer';
$firstName = explode(' ', trim($name))[0];
$current   = basename($_SERVER['SCRIPT_NAME']);
$initial   = strtoupper(mb_substr(trim($name), 0, 1, 'UTF-8'));
$navItems  = [
    'dashboard.php' => ['🏠', 'Dashboard'],
    'bikes.php'     => ['🚲', 'Browse Bikes'],
    'cart.php'      => ['🛒', 'My Cart'],
    'rentals.php'   => ['📜', 'My Rentals'],
];
$e    = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$peso = fn($n) => '₱' . number_format((float) $n, 2);

$userId         = (int) ($_SESSION['user_id'] ?? 0);
$availableBikes = 0;
$minRate        = null;
$bikeTypes      = [];
$ongoing        = 0;
$completed      = 0;
$spent          = 0;
$nextReturn     = null;
$cartCount      = is_array($_SESSION['cart'] ?? null) ? count($_SESSION['cart']) : 0;

try {
    // Browse Bikes card
    $row = $pdo->query(
        "SELECT COUNT(*) AS total, MIN(rate_per_hour) AS min_rate
         FROM bikes WHERE status = 'available'"
    )->fetch(PDO::FETCH_ASSOC);
    
    $availableBikes = (int) $row['total'];
    $minRate        = $row['min_rate'];

    $bikeTypes = $pdo->query(
        "SELECT DISTINCT type FROM bikes WHERE status = 'available' ORDER BY type LIMIT 4"
    )->fetchAll(PDO::FETCH_COLUMN);

    // My Rentals card
    $stmt = $pdo->prepare(
        "SELECT SUM(status = 'Ongoing')   AS ongoing,
                SUM(status = 'Completed') AS completed,
                COALESCE(SUM(total), 0)   AS spent
         FROM rentals WHERE user_id = ?"
    );
    $stmt->execute([$userId]);
    
    $r         = $stmt->fetch(PDO::FETCH_ASSOC);
    $ongoing   = (int) $r['ongoing'];
    $completed = (int) $r['completed'];
    $spent     = (float) $r['spent'];

    $stmt = $pdo->prepare(
        "SELECT b.name, t.end_time
         FROM rentals t JOIN bikes b ON b.id = t.bike_id
         WHERE t.user_id = ? AND t.status = 'Ongoing'
         ORDER BY t.end_time ASC LIMIT 1"
    );
    $stmt->execute([$userId]);
    
    $nextReturn = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (PDOException $ex) {
    error_log($ex->getMessage()); // hindi mamamatay ang page kahit may DB error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard | BikeMo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/main.js" defer></script>
</head>
<body>

<header class="bm-header">
    <div class="container bm-header__inner">
        <a class="bm-brand" href="dashboard.php">
            <span class="bm-brand__icon">🚲</span>
            <span class="bm-brand__text">Bike<b>Mo</b></span>
        </a>

        <nav class="bm-nav" aria-label="Main navigation">
            <?php foreach ($navItems as $file => [$icon, $label]): ?>
                <a href="<?= $file ?>"
                   class="bm-nav__link<?= $current === $file ? ' is-active' : '' ?>"
                   <?= $current === $file ? 'aria-current="page"' : '' ?>>
                    <span class="bm-nav__icon"><?= $icon ?></span>
                    <span><?= $label ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="bm-user">
            <div class="bm-user__avatar"><?= htmlspecialchars($initial, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="bm-user__info">
                <small>Signed in as</small>
                <strong><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            
            <a href="profile.php" style="color:inherit;margin-right:12px">My Profile</a>
            <a class="bm-user__logout" href="../logout.php">
                Logout ⏻
            </a>
        </div>
    </div>
</header>

<main class="page">
    <div class="container">

        <!-- HERO -->
        <section class="bm-hero bm-reveal">
            <span class="bm-cloud c1">☁️</span>
            <span class="bm-cloud c2">☁️</span>

            <small>CUSTOMER PORTAL</small>
            <h1><span id="bm-greeting">Welcome back</span>,<br><?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>! 👋</h1>
            <p>Ready for your next ride? Pick a bike, hit the road, and let the city be your playground.</p>

            <a class="bm-cta" href="bikes.php">Find my ride <span class="arrow">→</span></a>

            <!-- Animated rider -->
            <svg class="bm-rider" viewBox="0 0 120 70" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <g class="bm-wheel">
                    <circle cx="24" cy="48" r="16" fill="none" stroke="#fff" stroke-width="4"/>
                    <line x1="24" y1="32" x2="24" y2="64" stroke="#fff" stroke-width="1.5"/>
                    <line x1="8" y1="48" x2="40" y2="48" stroke="#fff" stroke-width="1.5"/>
                </g>
                <g class="bm-wheel">
                    <circle cx="96" cy="48" r="16" fill="none" stroke="#fff" stroke-width="4"/>
                    <line x1="96" y1="32" x2="96" y2="64" stroke="#fff" stroke-width="1.5"/>
                    <line x1="80" y1="48" x2="112" y2="48" stroke="#fff" stroke-width="1.5"/>
                </g>
                <path d="M24 48 L48 24 L82 24 L96 48 M48 24 L58 48 L24 48 M58 48 L82 24" fill="none" stroke="#ffb703" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M82 24 L88 14 L100 14" fill="none" stroke="#fff" stroke-width="4" stroke-linecap="round"/>
                <path d="M44 24 L46 16 L56 16" fill="none" stroke="#fff" stroke-width="4" stroke-linecap="round"/>
                <circle cx="62" cy="6" r="6" fill="#ff5a1f"/>
                <path d="M62 12 L56 16 M62 12 L88 14" stroke="#ff5a1f" stroke-width="4" stroke-linecap="round"/>
            </svg>
            <div class="bm-road"></div>
        </section>

        <!-- QUICK PERKS -->
        <section class="bm-stats">
            <div class="bm-stat bm-reveal d1">
                <div class="ico">⚡</div>
                <div><b>Instant booking</b><span>Reserve in a few clicks</span></div>
            </div>
            <div class="bm-stat bm-reveal d2">
                <div class="ico">🛡️</div>
                <div><b>Well-maintained</b><span>Every bike road-checked</span></div>
            </div>
            <div class="bm-stat bm-reveal d3">
                <div class="ico">🌿</div>
                <div><b>Ride green</b><span>Zero emissions, all fun</span></div>
            </div>
        </section>

        <!-- ACTIONS -->
        <section class="section">
            <div class="bm-title bm-reveal d2">
                <h2>Your next ride starts here</h2>
                <p>Explore available bikes and manage your rentals.</p>
            </div>

            <div class="bm-cards">
                <a class="bm-card browse bm-reveal d2" href="bikes.php">
                    <div class="emoji">🚲</div>
                    <h3>BROWSE BIKES</h3>
                    <p>Explore the available bikes and rental rates.</p>

                    <div class="bm-card__meta">
                        <div class="bm-big">
                            <b data-bm-count="<?= $availableBikes ?>"><?= $availableBikes ?></b>
                            <span>bike<?= $availableBikes === 1 ? '' : 's' ?> ready to rent</span>
                        </div>
                        <?php if ($availableBikes > 0): ?>
                            <div class="bm-chips">
                                <?php foreach ($bikeTypes as $type): ?>
                                    <span class="bm-chip"><?= $e($type) ?></span>
                                <?php endforeach; ?>
                                <?php if ($minRate !== null): ?>
                                    <span class="bm-chip">From <?= $peso($minRate) ?>/hr</span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="bm-note">No bikes available right now. Check back soon!</div>
                        <?php endif; ?>
                    </div>

                    <span class="go">Start exploring <i>→</i></span>
                </a>

                <a class="bm-card cart bm-reveal d3" href="cart.php">
                    <div class="emoji">🛒</div>
                    <h3>MY CART</h3>
                    <p>Review your bike selections and checkout.</p>

                    <div class="bm-card__meta">
                        <div class="bm-big">
                            <b data-bm-count="<?= $cartCount ?>"><?= $cartCount ?></b>
                            <span>item<?= $cartCount === 1 ? '' : 's' ?> in your cart</span>
                        </div>
                        <div class="bm-note">
                            <?= $cartCount > 0
                                ? '✅ Ready for checkout whenever you are.'
                                : '🛍️ Your cart is empty. Pick a bike to get started.' ?>
                        </div>
                    </div>

                    <span class="go">Open cart <i>→</i></span>
                </a>

                <a class="bm-card rent bm-reveal d4" href="rentals.php">
                    <div class="emoji">📜</div>
                    <h3>MY RENTALS</h3>
                    <p>View your ongoing and completed rentals.</p>

                    <div class="bm-card__meta">
                        <div class="bm-mini">
                            <div><b data-bm-count="<?= $ongoing ?>"><?= $ongoing ?></b><small>Ongoing</small></div>
                            <div><b data-bm-count="<?= $completed ?>"><?= $completed ?></b><small>Completed</small></div>
                            <div><b><?= $peso($spent) ?></b><small>Total spent</small></div>
                        </div>
                        <div class="bm-note">
                            <?php if ($nextReturn): ?>
                                ⏰ Return <strong><?= $e($nextReturn['name']) ?></strong>
                                by <?= $e(date('M j, g:i A', strtotime($nextReturn['end_time']))) ?>
                            <?php else: ?>
                                🌤️ No active rentals. Ready when you are!
                            <?php endif; ?>
                        </div>
                    </div>

                    <span class="go">View history <i>→</i></span>
                </a>
            </div>
        </section>

        <!-- RIDE TIP -->
        <div class="bm-tip bm-reveal d4">
            <div class="bulb">💡</div>
            <div>
                <strong>RIDE TIP</strong>
                <span id="bm-tip-text">Adjust your saddle so your leg is almost fully extended at the bottom of the pedal stroke.</span>
            </div>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>