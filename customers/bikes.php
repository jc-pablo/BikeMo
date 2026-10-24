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
</div>
</div></header>
<?php }
?>
<?php
$notice=''; $error='';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_bike'])) {
 $bikeId=(int)($_POST['bike_id']??0); $start=trim($_POST['start_at']??''); $qty=(int)($_POST['quantity']??1); $unit=$_POST['unit']??'hours';
 if (!in_array($unit,['hours','days','weeks'],true) || $qty<1 || $qty>($unit==='hours'?72:($unit==='days'?30:8))) $error='Please choose a valid rental duration.';
 else {
  $q=$pdo->prepare("SELECT id,name,status FROM bikes WHERE id=?"); $q->execute([$bikeId]); $b=$q->fetch(PDO::FETCH_ASSOC);
  if (!$b || $b['status']!=='available') $error='Sorry, this bike is no longer available.';
  elseif (!$start || strtotime($start)===false || strtotime($start)<time()-300) $error='Please choose a valid future pick-up time.';
  else { if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) $_SESSION['cart']=[]; $_SESSION['cart'][$bikeId]=['bike_id'=>$bikeId,'start_at'=>date('Y-m-d H:i:s',strtotime($start)),'quantity'=>$qty,'unit'=>$unit]; $notice=$b['name'].' added to your cart!'; }
 }
}
try{$bikes=$pdo->query("SELECT id,name,type,color,rate_per_hour,rate_per_day,gears,frame,weight,description,status FROM bikes ORDER BY status='available' DESC, name")->fetchAll(PDO::FETCH_ASSOC);}catch(PDOException $ex){$bikes=[];$error='Could not load bikes right now.';}
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Browse Bikes | BikeMo</title><link rel="stylesheet" href="../assets/css/style.css"><script src="../assets/js/main.js" defer></script></head><body><?php bmHeader($name,$initial,$current,$navItems,$e); ?><main class="page customer-page"><div class="container">
<section class="customer-hero"><small>YOUR NEXT ADVENTURE STARTS HERE</small><h1>Find your kind of ride.</h1><p>From city cruising to weekend trails, explore our fleet and choose a bike that fits your pace.</p></section>
<?php if($notice): ?><div class="customer-alert"><?= $e($notice) ?> <a href="cart.php"><strong>View cart →</strong></a></div><?php endif; ?><?php if($error): ?><div class="customer-alert error"><?= $e($error) ?></div><?php endif; ?>
<div class="customer-toolbar"><input id="bikeSearch" type="search" placeholder="🔎 Search by bike name or type..."><select id="bikeType"><option value="">All bike types</option><?php foreach(['Mountain','Road','City','Electric','Folding','BMX'] as $t): ?><option><?= $t ?></option><?php endforeach; ?></select><span style="color:var(--muted)"><?= count($bikes) ?> bikes in our fleet</span></div>
<div class="customer-grid" id="customerBikeGrid">
<?php foreach($bikes as $b): ?><article class="customer-bike" data-name="<?= $e(strtolower($b['name'].' '.$b['type'])) ?>" data-type="<?= $e($b['type']) ?>">
<div class="customer-bike-art"><span><?= ['Mountain'=>'⛰️','Road'=>'🏁','City'=>'🚲','Electric'=>'⚡','Folding'=>'🛴','BMX'=>'🛞'][$b['type']]??'🚲' ?></span></div><div class="customer-bike-body"><div class="customer-pills"><span><?= $e($b['type']) ?></span><span class="customer-status <?= $b['status']==='available'?'':'ongoing' ?>"><?= $e(ucfirst($b['status'])) ?></span></div><h2><?= $e($b['name']) ?></h2><p><?= $e($b['description'] ?: 'A dependable ride for your next trip.') ?></p><div class="customer-pills"><span>⚙ <?= $e($b['gears']) ?></span><span>Frame: <?= $e($b['frame']) ?></span><span><?= $e($b['weight']) ?></span></div><div class="customer-price"><div><strong><?= $peso($b['rate_per_hour']) ?></strong><small> / hour</small></div><div><strong style="color:var(--navy)"><?= $peso($b['rate_per_day']) ?></strong><small> / day</small></div></div>
<?php if($b['status']==='available'): ?><form method="post" class="customer-form"><input type="hidden" name="bike_id" value="<?= (int)$b['id'] ?>"><label>Pick-up<input type="datetime-local" name="start_at" required min="<?= date('Y-m-d\TH:i') ?>" value="<?= date('Y-m-d\TH:i',time()+3600) ?>"></label><label>Duration<select name="unit"><option value="hours">Hours</option><option value="days">Days</option><option value="weeks">Weeks (15% off)</option></select></label><label>Quantity<input type="number" name="quantity" value="2" min="1" max="72" required></label><button class="btn btn-primary" name="add_bike" value="1">Add to cart →</button></form><?php else: ?><button class="btn btn-outline" disabled>Currently unavailable</button><?php endif; ?></div></article><?php endforeach; ?>
</div></div></main><?php require_once __DIR__ . '/../includes/footer.php'; ?></body></html>