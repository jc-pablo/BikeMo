<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/helpers.php';

requireRole('admin');

$adminPage = 'dashboard'; 
$adminTitle = 'Dashboard';

$stats = [
    'bikes' => 0,
    'available' => 0,
    'maintenance' => 0,
    'rentals' => 0,
    'customers' => 0,
    'revenue' => 0
];

try {
    $stats['bikes'] = (int)$pdo->query('SELECT COUNT(*) FROM bikes')->fetchColumn();
    $stats['available'] = (int)$pdo->query("SELECT COUNT(*) FROM bikes WHERE status = 'available'")->fetchColumn();
    $stats['maintenance'] = (int)$pdo->query("SELECT COUNT(*) FROM bikes WHERE status = 'maintenance'")->fetchColumn();
    $stats['rentals'] = (int)$pdo->query("SELECT COUNT(*) FROM rentals WHERE status = 'Ongoing'")->fetchColumn();
    $stats['customers'] = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
    $stats['revenue'] = (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM rentals WHERE status = 'Completed'")->fetchColumn();
    
    $recent = $pdo->query("SELECT t.code, t.status, t.total, t.created_at, u.name AS customer_name, b.name AS bike_name FROM rentals t 
                          JOIN users u ON u.id=t.user_id JOIN bikes b ON b.id=t.bike_id ORDER BY t.created_at   
                          DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage()); 
    $pageError = 'Hindi mabasa ang dashboard data. I-check kung kumpleto ang tables sa BikeMo database.'; 
    $recent = [];
}

function peso(float|int|string $amount): string
{
    return '₱' . number_format((float) $amount, 2);
}

require __DIR__ . '/_header.php';
?>

<?php if (!empty($pageError)): ?>
    <div class="bm-admin-alert error"><?= adminEscape($pageError) ?></div>
<?php endif; ?>

<section class="bm-admin-welcome">
    <div>
        <span class="bm-admin-pill">LIVE OVERVIEW</span>
        <h2>Good to see you, <?= adminEscape(explode(' ', trim($_SESSION['name'] ?? 'Admin'))[0]) ?>.</h2>
        <p>Here's what's happening with your bike rental business today.</p>
    </div>
    <a class="btn btn-primary" href="bikes.php#bike-form">＋ Add a bike</a>
</section>

<div class="bm-admin-stat-grid">
    <article class="bm-admin-stat">
        <div class="bm-admin-stat-icon orange">🚲</div>
        <span>Total bikes</span>
        <strong><?= $stats['bikes'] ?></strong>
        <small>In your fleet</small>
    </article>
    <article class="bm-admin-stat">
        <div class="bm-admin-stat-icon green">✓</div>
        <span>Available bikes</span>
        <strong><?= $stats['available'] ?></strong>
        <small>Ready to rent</small>
    </article>
    <article class="bm-admin-stat">
        <div class="bm-admin-stat-icon blue">▤</div>
        <span>Ongoing rentals</span>
        <strong><?= $stats['rentals'] ?></strong>
        <small>Currently active</small>
    </article>
    <article class="bm-admin-stat">
        <div class="bm-admin-stat-icon purple">♙</div>
        <span>Customers</span>
        <strong><?= $stats['customers'] ?></strong>
        <small>Registered accounts</small>
    </article>
</div>

<div class="bm-admin-dashboard-grid">
    <section class="bm-admin-panel bm-admin-recent">
        <div class="bm-admin-panel-head">
            <div>
                <h3>Recent bookings</h3>
                <p>Latest activity from your customers</p>
            </div>
            <a href="rentals.php">View all →</a>
        </div>
        
        <?php if (!$recent): ?>
            <div class="bm-admin-empty">
                <span>▤</span>
                <b>No bookings yet</b>
                <p>New reservations will appear here.</p>
            </div>
        <?php else: ?>
            <div class="tbl-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Customer / Bike</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $r): ?>
                            <tr>
                                <td>
                                    <b><?= adminEscape($r['code']) ?></b>
                                    <small class="bm-admin-cell-sub"><?= adminEscape(date('M j, Y', strtotime($r['created_at']))) ?></small>
                                </td>
                                <td>
                                    <?= adminEscape($r['customer_name']) ?>
                                    <small class="bm-admin-cell-sub"><?= adminEscape($r['bike_name']) ?></small>
                                </td>
                                <td><b><?= peso($r['total']) ?></b></td>
                                <td>
                                    <span class="bm-admin-status <?= strtolower($r['status']) ?>">
                                        <?= adminEscape($r['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="bm-admin-panel bm-admin-quick">
        <div class="bm-admin-panel-head">
            <div>
                <h3>Quick actions</h3>
                <p>Common admin tasks</p>
            </div>
        </div>
        
        <a href="bikes.php#bike-form" class="bm-admin-quick-link">
            <span class="quick-icon orange">＋</span>
            <span><b>Add a new bike</b><small>Create a bike listing and set its rates</small></span>
            <i>→</i>
        </a>
        
        <a href="bikes.php" class="bm-admin-quick-link">
            <span class="quick-icon blue">↻</span>
            <span><b>Update bike status</b><small>Available, rented or maintenance</small></span>
            <i>→</i>
        </a>
        
        <a href="rentals.php" class="bm-admin-quick-link">
            <span class="quick-icon green">✓</span>
            <span><b>Manage bookings</b><small>Review and complete rentals</small></span>
            <i>→</i>
        </a>
        
        <div class="bm-admin-revenue">
            <span>Completed rental value</span>
            <strong><?= peso($stats['revenue']) ?></strong>
            <small>Sum of completed transactions</small>
        </div>
    </section>
</div>

<?php require __DIR__ . '/_footer.php'; ?>