<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('user');
require_once __DIR__ . '/../includes/db.php';
$bikeId = (int) ($_GET['bike_id'] ?? $_POST['bike_id'] ?? 0);
$userId = (int) $_SESSION['user_id'];
$check = $pdo->prepare('SELECT COUNT(*) FROM rentals WHERE bike_id=? AND user_id=?');
$check->execute([$bikeId, $userId]);
$eligible = (int) $check->fetchColumn() > 0;
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $eligible) {
    $rating = (int) ($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    $comment = preg_replace('/\b(fuck|shit|bitch|putangina|gago|tangina)\b/iu', '****', $comment);
    if ($rating < 1 || $rating > 5 || strlen($comment) < 3 || strlen($comment) > 1000) {
        $message = 'Choose 1-5 stars and write 3-1000 characters.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO bike_reviews (bike_id,user_id,rating,comment) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE rating=VALUES(rating), comment=VALUES(comment)');
        $stmt->execute([$bikeId,$userId,$rating,$comment]);
        $message = 'Review saved. You can edit it any time.';
    }
}
$stmt = $pdo->prepare('SELECT b.name, r.rating, r.comment, u.name AS reviewer FROM bikes b LEFT JOIN bike_reviews r ON r.bike_id=b.id LEFT JOIN users u ON u.id=r.user_id WHERE b.id=?');
$stmt->execute([$bikeId]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
$e = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Bike Reviews | BikeMo</title><link rel="stylesheet" href="../assets/css/style.css"></head><body><main class="customer-page"><div class="container" style="padding-top:55px;max-width:760px"><a href="bikes.php">← Browse bikes</a><section class="customer-panel"><h1><?= $e($reviews[0]['name'] ?? 'Bike') ?> — Reviews</h1><p><?= $e($message) ?></p>
<?php if ($eligible): ?><form method="post" class="customer-form"><input type="hidden" name="bike_id" value="<?= $bikeId ?>"><label>Rating<select name="rating"><?php for($i=5;$i>=1;$i--): ?><option value="<?= $i ?>"><?= $i ?> stars</option><?php endfor; ?></select></label><label>Review<textarea name="comment" rows="4"></textarea></label><button class="btn btn-primary">Save or update review</button></form><?php else: ?><p>Rent this bike first to leave a review.</p><?php endif; ?>
<?php foreach($reviews as $review): ?><?php if ($review['rating']): ?><div style="padding:18px 0;border-bottom:1px solid #ddd"><b><?= $e($review['reviewer']) ?> — <?= (int)$review['rating'] ?>/5</b><p><?= nl2br($e($review['comment'])) ?></p></div><?php endif; ?><?php endforeach; ?></section></div></main></body></html>
