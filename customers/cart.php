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
$notice='';$error='';
if(!isset($_SESSION['cart'])||!is_array($_SESSION['cart']))$_SESSION['cart']=[];
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['remove'])){unset($_SESSION['cart'][(int)$_POST['remove']]);$notice='Bike removed from your cart.';}
$items=[];$grand=0;
try{foreach($_SESSION['cart'] as $id=>$item){$q=$pdo->prepare("SELECT id,name,type,color,rate_per_hour,rate_per_day,status FROM bikes WHERE id=?");$q->execute([(int)$id]);$b=$q->fetch(PDO::FETCH_ASSOC);if(!$b){unset($_SESSION['cart'][$id]);continue;}$qty=max(1,(int)$item['quantity']);$unit=$item['unit'];$base=$unit==='hours'?$qty*(float)$b['rate_per_hour']:$qty*(float)$b['rate_per_day']*($unit==='weeks'?7:1);$discount=$unit==='weeks'?$base*.15:0;$total=$base-$discount;$b['cart']=$item;$b['qty']=$qty;$b['base']=$base;$b['discount']=$discount;$b['total']=$total;$items[]=$b;$grand+=$total;}}catch(PDOException $ex){$error='Unable to read your cart right now.';}
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['checkout']) && !$error){
 if(!$items)$error='Your cart is empty.';
 else{
  try{$pdo->beginTransaction();$ins=$pdo->prepare("INSERT INTO transactions (booking_code,user_id,bike_id,start_at,end_at,quantity,unit,total,payment_method,status) VALUES (?,?,?,?,?,?,?,?,?, 'Ongoing')");
   foreach($items as $b){if($b['status']!=='available')throw new RuntimeException($b['name'].' is no longer available. Remove it and choose another bike.');$start=strtotime($b['cart']['start_at']);$factor=$b['cart']['unit']==='hours'?3600:($b['cart']['unit']==='days'?86400:604800);$end=$start+$b['qty']*$factor;$code='BM'.date('ymd').strtoupper(bin2hex(random_bytes(3)));$ins->execute([$code,(int)$_SESSION['user_id'],$b['id'],date('Y-m-d H:i:s',$start),date('Y-m-d H:i:s',$end),$b['qty'],$b['cart']['unit'],$b['total'],'Pay at pickup']);$up=$pdo->prepare("UPDATE bikes SET status='rented' WHERE id=? AND status='available'");$up->execute([$b['id']]);if(!$up->rowCount())throw new RuntimeException($b['name'].' was just booked by someone else.');}
   $pdo->commit();$_SESSION['cart']=[];$notice='Booking confirmed! Your rentals are now listed below.';$items=[];$grand=0;
  }catch(Throwable $ex){if($pdo->inTransaction())$pdo->rollBack();$error=$ex instanceof RuntimeException?$ex->getMessage():'Checkout failed. Please try again.';}
 }
}
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>My Cart | BikeMo</title><link rel="stylesheet" href="../assets/css/style.css"><script src="../assets/js/main.js" defer></script></head><body><?php bmHeader($name,$initial,$current,$navItems,$e); ?><main class="page customer-page"><div class="container"><section class="customer-hero"><small>ONE STEP CLOSER TO THE OPEN ROAD</small><h1>Your ride, lined up.</h1><p>Double-check your bike, pickup time and rental duration before confirming your booking.</p></section>
<?php if($notice): ?><div class="customer-alert"><?= $e($notice) ?> <?php if(!$items): ?><a href="rentals.php"><strong>View my rentals →</strong></a><?php endif; ?></div><?php endif; ?><?php if($error): ?><div class="customer-alert error"><?= $e($error) ?></div><?php endif; ?>
<?php if(!$items): ?><div class="customer-empty"><div class="emoji">🛒</div><h2>Your cart is taking a breather.</h2><p>Choose a bike and add it here. Your next ride is waiting.</p><a class="btn btn-primary" href="bikes.php">Explore bikes →</a></div><?php else: ?>
<section class="customer-panel"><div class="customer-table-wrap"><table class="customer-table"><thead><tr><th>Bike</th><th>Pickup</th><th>Duration</th><th>Rental total</th><th></th></tr></thead><tbody>
<?php foreach($items as $b): ?><tr><td><strong><?= $e($b['name']) ?></strong><br><small><?= $e($b['type']) ?></small></td><td><?= $e(date('M j, Y g:i A',strtotime($b['cart']['start_at']))) ?></td><td><?= (int)$b['qty'] ?> <?= $e($b['cart']['unit']) ?></td><td><strong><?= $peso($b['total']) ?></strong><?php if($b['discount']): ?><br><small style="color:var(--green)">15% weekly discount</small><?php endif; ?></td><td><form method="post"><button class="btn btn-outline btn-sm" name="remove" value="<?= (int)$b['id'] ?>">Remove</button></form></td></tr><?php endforeach; ?>
</tbody></table></div><div class="customer-total"><div><small style="color:var(--muted)">TOTAL FOR YOUR SELECTED RIDES</small><br><strong><?= $peso($grand) ?></strong></div><form method="post"><button class="btn btn-primary" name="checkout" value="1">Confirm booking →</button><p style="font-size:.75rem;color:var(--muted);margin-top:8px">Payment method: Pay at pickup</p></form></div></section><a href="bikes.php" class="btn btn-outline">← Add another bike</a><?php endif; ?></div></main><?php require_once __DIR__ . '/../includes/footer.php'; ?></body></html>