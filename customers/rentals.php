<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('user');
require_once __DIR__ . '/../includes/db.php';

$e = fn($text) => htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
$peso = fn($value) => '₱' . number_format((float) $value, 2);
$userId = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare(
    'SELECT r.code, r.start_time, r.end_time, r.qty, r.unit,
            r.total, r.payment, r.status, r.created_at, b.id AS bike_id, b.name AS bike_name
     FROM rentals r JOIN bikes b ON b.id = r.bike_id
     WHERE r.user_id = ? ORDER BY r.created_at DESC'
);
$stmt->execute([$userId]);
$rentals = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ongoing = 0;
$completed = 0;
$totalSpent = 0;

foreach ($rentals as $rental) {
    if ($rental['status'] === 'Ongoing') {
        $ongoing++;
    }
    if ($rental['status'] === 'Completed') {
        $completed++;
    }
    $totalSpent += (float) $rental['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Rentals | BikeMo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/main.js" defer></script>
</head>
<body>

<?php require __DIR__ . '/../includes/customer_nav.php'; ?>

<main class="page customer-page">
    <div class="container">
        <section class="customer-hero">
            <small>YOUR RIDES AT A GLANCE</small>
            <h1>My rental history.</h1>
            <p>All ongoing and completed rentals tied to your BikeMo account.</p>
        </section>

        <div class="customer-grid" style="grid-template-columns:repeat(auto-fit,minmax(190px,1fr));margin-bottom:24px">
            <div class="customer-panel">
                <small>ONGOING</small>
                <h2 style="color:var(--orange);font-size:2.2rem"><?= $ongoing ?></h2>
            </div>
            <div class="customer-panel">
                <small>COMPLETED</small>
                <h2 style="color:var(--navy);font-size:2.2rem"><?= $completed ?></h2>
            </div>
            <div class="customer-panel">
                <small>TOTAL RENTAL COST</small>
                <h2 style="color:var(--navy);font-size:2rem"><?= $peso($totalSpent) ?></h2>
            </div>
        </div>

        <section class="customer-panel">
            <div class="bm-rental-filterbar">
                <h2>Booking history</h2>
                <select id="rentalFilter" aria-label="Filter rentals">
                    <option value="">All bookings</option>
                    <option value="Ongoing">Ongoing</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
            
            <div class="customer-table-wrap">
                <table class="customer-table">
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Bike</th>
                            <th>Pick-up / Return</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="rentalRows">
                        <?php foreach ($rentals as $rental): ?>
                            <tr data-status="<?= $e($rental['status']) ?>">
                                <td><b><?= $e($rental['code']) ?></b></td>
                                <td>
                                    <b><?= $e($rental['bike_name']) ?></b><br>
                                    <a href="review.php?bike_id=<?= (int) $rental['bike_id'] ?>">Review bike →</a>
                                </td>
                                <td>
                                    <?= $e(date('M j, Y g:i A', strtotime($rental['start_time']))) ?><br>
                                    <small>Due: <?= $e(date('M j, Y g:i A', strtotime($rental['end_time']))) ?></small><br>
                                    <small><?= (int) $rental['qty'] ?> <?= $e($rental['unit']) ?></small>
                                </td>
                                <td>
                                    <b><?= $peso($rental['total']) ?></b><br>
                                    <small><?= $e($rental['payment']) ?></small>
                                </td>
                                <td>
                                    <span class="badge <?= $rental['status'] === 'Completed' ? 'completed' : 'ongoing' ?>">
                                        <?= $e($rental['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (!$rentals): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="customer-empty">
                                        No rentals yet. <a href="bikes.php">Browse bikes →</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<?php 
$assetPrefix = '../'; 
require __DIR__ . '/../includes/footer.php'; 
?>