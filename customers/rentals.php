<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('user');
require_once __DIR__ . '/../includes/db.php';
$name = $_SESSION['name'] ?? 'Customer';
$initial = strtoupper(mb_substr(trim($name), 0, 1, 'UTF-8'));
$current = basename($_SERVER['SCRIPT_NAME']);
$e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
$peso = fn($n) => '₱' . number_format((float)$n, 2);
$navItems = ['dashboard.php'=>['⌂','Dashboard'], 'bikes.php'=>['🚲','Browse Bikes'], 'cart.php'=>['🛒','My Cart'], 'rentals.php'=>['▤','My Rentals']];
function bmHeader($name,$initial,$current,$navItems,$e) { ?>
<header class="bm-header"><div class="container bm-header__inner">
<a class="bm-brand" href="dashboard.php"><span class="bm-brand__icon">🚲</span><span class="bm-brand__text">Bike<b>Mo</b></span></a>
<nav class="bm-nav" aria-label="Main navigation"><?php foreach($navItems as $file=>$item): ?><a href="<?= $file ?>" class="bm-nav__link<?= $current===$file?' is-active':'' ?>" <?= $current===$file?'aria-current="page"':'' ?>><span class="bm-nav__icon"><?= $item[0] ?></span><span><?= $item[1] ?></span></a><?php endforeach; ?></nav>
<div class="bm-user"><div class="bm-user__avatar"><?= $e($initial) ?></div><div class="bm-user__info"><small>Signed in as</small><strong><?= $e($name) ?></strong></div>
<a class="bm-user__logout" href="../logout.php">
    Logout ⏻
</a>
</header>
<?php }
?>
<?php
$userId=(int)($_SESSION['user_id']??0);$error='';$rentals=[];$ongoing=0;$completed=0;$spent=0;
try{$q=$pdo->prepare("SELECT t.id,t.booking_code,t.start_at,t.end_at,t.quantity,t.unit,t.total,t.payment_method,t.status,t.created_at,b.name AS bike_name,b.type AS bike_type FROM transactions t JOIN bikes b ON b.id=t.bike_id WHERE t.user_id=? ORDER BY t.created_at DESC");$q->execute([$userId]);$rentals=$q->fetchAll(PDO::FETCH_ASSOC);foreach($rentals as $r){if(strtolower($r['status'])==='ongoing')$ongoing++;if(strtolower($r['status'])==='completed')$completed++;$spent+=(float)$r['total'];}}catch(PDOException $ex){$error='Could not load your rental history right now.';}
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>My Rentals | BikeMo</title><link rel="stylesheet" href="../assets/css/style.css"><script src="../assets/js/main.js" defer></script></head><body><?php bmHeader($name,$initial,$current,$navItems,$e); ?><main class="page customer-page"><div class="container"><section class="customer-hero"><small>EVERY RIDE HAS A STORY</small><h1>Your rides, all in one place.</h1><p>Keep track of active bookings, review your past rides and see your total rental spend.</p></section>
<?php if($error): ?><div class="customer-alert error"><?= $e($error) ?></div><?php endif; ?>
<div class="customer-grid" style="grid-template-columns:repeat(auto-fit,minmax(190px,1fr));margin-bottom:26px"><div class="customer-panel"><small style="color:var(--muted)">ACTIVE RIDES</small><h2 style="font-size:2.4rem;color:var(--orange);margin-top:8px"><?= $ongoing ?></h2><p style="color:var(--muted)">Bookings in progress</p></div><div class="customer-panel"><small style="color:var(--muted)">COMPLETED</small><h2 style="font-size:2.4rem;color:var(--navy);margin-top:8px"><?= $completed ?></h2><p style="color:var(--muted)">Rides finished</p></div><div class="customer-panel"><small style="color:var(--muted)">TOTAL RENTAL VALUE</small><h2 style="font-size:2.1rem;color:var(--orange);margin-top:8px"><?= $peso($spent) ?></h2><p style="color:var(--muted)">Across all bookings</p></div></div>
<?php if(!$rentals): ?><div class="customer-empty"><div class="emoji">🛣️</div><h2>No rides yet — let's change that.</h2><p>Your confirmed bookings will appear here, ready for you to track.</p><a class="btn btn-primary" href="bikes.php">Find your first ride →</a></div><?php else: ?><section class="customer-panel"><div class="customer-toolbar" style="margin-top:0"><div><h2 style="font-size:2rem;color:var(--navy)">Rental history</h2><p style="color:var(--muted)">Your bookings and their current status.</p></div><select id="rentalFilter"><option value="">All rentals</option><option value="Ongoing">Ongoing</option><option value="Completed">Completed</option></select></div><div class="customer-table-wrap"><table class="customer-table"><thead><tr><th>Booking</th><th>Bike</th><th>Rental period</th><th>Total</th><th>Status</th></tr></thead><tbody id="rentalRows"><?php foreach($rentals as $r): ?><tr data-status="<?= $e($r['status']) ?>"><td><strong><?= $e($r['booking_code']) ?></strong><br><small><?= $e(date('M j, Y',strtotime($r['created_at']))) ?></small></td><td><strong><?= $e($r['bike_name']) ?></strong><br><small><?= $e($r['bike_type']) ?></small></td><td><?= $e(date('M j, g:i A',strtotime($r['start_at']))) ?><br><small>to <?= $e(date('M j, g:i A',strtotime($r['end_at']))) ?></small><br><small><?= (int)$r['quantity'] ?> <?= $e($r['unit']) ?></small></td><td><strong><?= $peso($r['total']) ?></strong></td><td><span class="customer-status <?= strtolower($r['status'])==='ongoing'?'ongoing':'' ?>"><?= $e($r['status']) ?></span></td></tr><?php endforeach; ?></tbody></table></div></section><?php endif; ?>
<div style="margin-top:24px"><a class="btn btn-outline" href="bikes.php">＋ Book another ride</a></div></div></main><?php require_once __DIR__ . '/../includes/footer.php'; ?></body></html>