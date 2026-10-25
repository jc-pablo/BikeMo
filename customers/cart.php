<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('user');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../PHPMailer/mailer.php';

if (empty($_SESSION['csrf_customer'])) {
    $_SESSION['csrf_customer'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$e = fn($text) => htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
$peso = fn($value) => '₱' . number_format((float) $value, 2);
$limits = ['hours' => 72, 'days' => 30, 'weeks' => 8];
$notice = '';
$error = '';

// Only server-side PHP calculates the final rental amount.
function rentalPrice(array $bike, int $quantity, string $unit): float
{
    $rate = $unit === 'hours' ? (float) $bike['rate_per_hour'] : (float) $bike['rate_per_day'];
    $days = $unit === 'weeks' ? 7 : 1;
    $subtotal = $rate * $quantity * $days;
    return round($unit === 'weeks' ? $subtotal * 0.85 : $subtotal, 2);
}

function validCartDetails(array $data, array $limits): bool
{
    $unit = $data['unit'] ?? '';
    $quantity = $data['quantity'] ?? 0;
    $pickup = strtotime($data['start_at'] ?? '');
    
    return isset($limits[$unit]) &&
        is_numeric($quantity) && (int) $quantity == $quantity &&
        (int) $quantity >= 1 && (int) $quantity <= $limits[$unit] &&
        $pickup !== false && $pickup >= time();
}

// Load records from MySQL. Session only stores the customer's selections.
function cartBikes(PDO $pdo, array $cart): array
{
    $items = [];
    $find = $pdo->prepare('SELECT * FROM bikes WHERE id = ?');
    
    foreach ($cart as $bikeId => $selection) {
        $find->execute([(int) $bikeId]);
        $bike = $find->fetch(PDO::FETCH_ASSOC);
        if (!$bike) continue;
        
        $bike['cart'] = $selection;
        $bike['rental_total'] = rentalPrice($bike, (int) $selection['quantity'], $selection['unit']);
        $items[] = $bike;
    }
    
    return $items;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    
    if (!is_string($token) || !hash_equals($_SESSION['csrf_customer'], $token)) {
        $error = 'Security check failed. Refresh and try again.';
    } elseif (isset($_POST['remove'])) {
        unset($_SESSION['cart'][(int) $_POST['remove']]);
        $notice = 'Bike removed from your cart.';
    } elseif (isset($_POST['update_cart'])) {
        $id = (int) ($_POST['bike_id'] ?? 0);
        $selection = [
            'bike_id'  => $id,
            'start_at' => trim($_POST['start_at'] ?? ''),
            'quantity' => $_POST['quantity'] ?? '',
            'unit'     => $_POST['unit'] ?? ''
        ];
        
        if (!isset($_SESSION['cart'][$id]) || !validCartDetails($selection, $limits)) {
            $error = 'Enter a future pick-up time and valid duration.';
        } else {
            $selection['quantity'] = (int) $selection['quantity'];
            $selection['start_at'] = date('Y-m-d H:i:s', strtotime($selection['start_at']));
            $_SESSION['cart'][$id] = $selection;
            $notice = 'Cart updated.';
        }
    } elseif (isset($_POST['checkout'])) {
        if (!$_SESSION['cart']) {
            $error = 'Your cart is empty.';
        } else {
            try {
                $bookingDetails = [];
                $pdo->beginTransaction();
                
                $find = $pdo->prepare('SELECT * FROM bikes WHERE id = ? FOR UPDATE');
                $insert = $pdo->prepare(
                    "INSERT INTO rentals (code, user_id, bike_id, start_time, end_time, qty, unit, total, payment, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Ongoing')"
                );
                $update = $pdo->prepare("UPDATE bikes SET status = 'rented' WHERE id = ? AND status = 'available'");

                foreach ($_SESSION['cart'] as $bikeId => $selection) {
                    if (!validCartDetails($selection, $limits)) {
                        throw new RuntimeException('Your cart has an invalid date or duration. Please update it.');
                    }

                    $find->execute([(int) $bikeId]);
                    $bike = $find->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$bike || $bike['status'] !== 'available') {
                        throw new RuntimeException('One bike is no longer available. Please review your cart.');
                    }

                    $quantity = (int) $selection['quantity'];
                    $unit = $selection['unit'];
                    $start = strtotime($selection['start_at']);
                    $seconds = ['hours' => 3600, 'days' => 86400, 'weeks' => 604800][$unit];
                    $end = $start + ($quantity * $seconds);
                    $code = 'BM' . date('ymd') . strtoupper(bin2hex(random_bytes(3)));
                    $total = rentalPrice($bike, $quantity, $unit);

                    $insert->execute([
                        $code, 
                        (int) $_SESSION['user_id'], 
                        (int) $bike['id'],
                        date('Y-m-d H:i:s', $start), 
                        date('Y-m-d H:i:s', $end),
                        $quantity, 
                        $unit, 
                        $total, 
                        'Cash on pick-up'
                    ]);
                    
                    $update->execute([(int) $bike['id']]);
                    if ($update->rowCount() !== 1) {
                        throw new RuntimeException('Someone just reserved this bike. Please try again.');
                    }
                    
                    // Store a readable summary for the confirmation email.
                    $bookingDetails[] =
                        "Booking code: {$code}\n" .
                        "Bike: {$bike['name']}\n" .
                        "Pickup: " . date('M j, Y g:i A', $start) . "\n" .
                        "Return: " . date('M j, Y g:i A', $end) . "\n" .
                        "Duration: {$quantity} {$unit}\n" .
                        "Total: PHP " . number_format($total, 2);              
                }
                
                // First save the booking, then attempt to send an email.
                $pdo->commit();
                $_SESSION['cart'] = [];
                $notice = 'Booking confirmed! You can now view your rental history.';

                // Email failure must NOT change a successfully saved booking.
                try {
                    $userQuery = $pdo->prepare('SELECT name, email FROM users WHERE id = ?');
                    $userQuery->execute([(int) $_SESSION['user_id']]);
                    $customer = $userQuery->fetch(PDO::FETCH_ASSOC);

                    if ($customer) {
                        $message =
                            "Hello {$customer['name']}!\n\n" .
                            "Your BikeMo booking has been confirmed!\n\n" .
                            implode("\n\n", $bookingDetails) .
                            "\n\nPayment: Cash on pick-up\n\n" .
                            "Thank you for choosing BikeMo!";

                        $emailSent = sendBikeMoEmail(
                            $customer['email'],
                            $customer['name'],
                            'BikeMo Booking Confirmed',
                            $message
                        );

                        if (!$emailSent) {
                            error_log('BikeMo: booking saved but email was not sent.');
                        }
                    }
                } catch (Throwable $mailError) {
                    error_log('BikeMo email error: ' . $mailError->getMessage());
                }

            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                if ($exception instanceof PDOException) {
                    error_log($exception->getMessage());
                }
                $error = $exception instanceof RuntimeException ? $exception->getMessage() : 'Checkout failed. Please try again.';
            }
        }
    }
}

$items = cartBikes($pdo, $_SESSION['cart']);
$grandTotal = array_sum(array_column($items, 'rental_total'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Cart | BikeMo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php require __DIR__ . '/../includes/customer_nav.php'; ?>

<main class="page customer-page">
    <div class="container">
        <section class="customer-hero">
            <small>ONE STEP CLOSER TO THE OPEN ROAD</small>
            <h1>Your ride, lined up.</h1>
            <p>Update your bike, pick-up schedule or duration before you confirm.</p>
        </section>

        <?php if ($notice): ?>
            <div class="customer-alert"><?= $e($notice) ?> <a href="rentals.php">View my rentals →</a></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="customer-alert error"><?= $e($error) ?></div>
        <?php endif; ?>

        <?php if (!$items): ?>
            <div class="customer-empty">
                <div class="emoji">🛒</div>
                <h2>Your cart is empty.</h2>
                <p>Choose a bike to get started.</p>
                <a class="btn btn-primary" href="bikes.php">Explore Bikes →</a>
            </div>
        <?php else: ?>
            <section class="customer-panel">
                <div class="customer-table-wrap">
                    <table class="customer-table">
                        <thead>
                            <tr>
                                <th>Bike</th>
                                <th>Pick-up</th>
                                <th>Duration</th>
                                <th>Rental total</th>
                                <th>Edit or remove</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $bike): ?>
                                <tr>
                                    <td>
                                        <b><?= $e($bike['name']) ?></b><br>
                                        <small><?= $e($bike['type']) ?></small>
                                    </td>
                                    <td><?= $e(date('M j, Y g:i A', strtotime($bike['cart']['start_at']))) ?></td>
                                    <td><?= (int) $bike['cart']['quantity'] ?> <?= $e($bike['cart']['unit']) ?></td>
                                    <td><strong><?= $peso($bike['rental_total']) ?></strong></td>
                                    <td>
                                        <form method="post" class="bm-cart-edit" novalidate>
                                            <input type="hidden" name="csrf_token" value="<?= $e($_SESSION['csrf_customer']) ?>">
                                            <input type="hidden" name="bike_id" value="<?= (int) $bike['id'] ?>">
                                            <input type="datetime-local" name="start_at" aria-label="Pickup date" value="<?= $e(date('Y-m-d\TH:i', strtotime($bike['cart']['start_at']))) ?>">
                                            <input type="number" name="quantity" aria-label="Quantity" value="<?= (int) $bike['cart']['quantity'] ?>">
                                            <select name="unit" aria-label="Duration unit">
                                                <?php foreach (['hours', 'days', 'weeks'] as $unit): ?>
                                                    <option value="<?= $unit ?>" <?= $bike['cart']['unit'] === $unit ? 'selected' : '' ?>><?= ucfirst($unit) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button class="btn btn-primary btn-sm" name="update_cart" value="1">Update</button>
                                        </form>
                                        
                                        <form method="post">
                                            <input type="hidden" name="csrf_token" value="<?= $e($_SESSION['csrf_customer']) ?>">
                                            <button class="btn btn-outline btn-sm" name="remove" value="<?= (int) $bike['id'] ?>">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="customer-total">
                    <div>
                        <small>TOTAL RENTAL COST</small><br>
                        <strong><?= $peso($grandTotal) ?></strong>
                    </div>
                    
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= $e($_SESSION['csrf_customer']) ?>">
                        <button class="btn btn-primary" name="checkout" value="1">Confirm booking →</button>
                        <p style="font-size:.75rem;color:var(--muted);margin-top:8px">Cash on pick-up · Weeks receive 15% off</p>
                    </form>
                </div>
            </section>
            
            <a class="btn btn-outline" href="bikes.php">← Add another bike</a>
        <?php endif; ?>
    </div>
</main>

<?php 
$assetPrefix = '../'; 
require __DIR__ . '/../includes/footer.php'; 
?>