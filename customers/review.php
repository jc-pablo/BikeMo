<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('user');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/bike_images.php';

$bikeId = (int) ($_GET['bike_id'] ?? $_POST['bike_id'] ?? 0);
$userId = (int) $_SESSION['user_id'];
$e = fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$stmt = $pdo->prepare('SELECT * FROM bikes WHERE id = ?');
$stmt->execute([$bikeId]);
$bike = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bike) {
    http_response_code(404);
    exit('Bike not found. <a href="bikes.php">Back to bikes</a>');
}

if (empty($_SESSION['csrf_customer'])) {
    $_SESSION['csrf_customer'] = bin2hex(random_bytes(32));
}

// Only customers who actually booked this bike can write a review.
$stmt = $pdo->prepare('SELECT COUNT(*) FROM rentals WHERE user_id = ? AND bike_id = ?');
$stmt->execute([$userId, $bikeId]);
$eligible = (int) $stmt->fetchColumn() > 0;

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_customer'], $_POST['csrf_token'] ?? '')) {
        $error = 'Security check failed. Refresh and try again.';
    } elseif (!$eligible) {
        $error = 'Rent this bike before leaving a review.';
    } else {
        $rating = filter_var($_POST['rating'] ?? null, FILTER_VALIDATE_INT);
        $comment = trim($_POST['comment'] ?? '');

        // MP4: regex masks foul words as they are saved.
        $comment = preg_replace('/\b(fuck|shit|bitch|gago|tangina|putangina)\b/iu', '****', $comment);

        if (
            $rating === false || $rating < 1 || $rating > 5 ||
            mb_strlen($comment) < 3 || mb_strlen($comment) > 1000
        ) {
            $error = 'Choose 1 to 5 stars and enter a review between 3 and 1000 characters.';
        } else {
            $sql = 'INSERT INTO bike_reviews (bike_id, user_id, rating, comment)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)';
            $pdo->prepare($sql)->execute([$bikeId, $userId, $rating, $comment]);
            $message = 'Your review has been saved.';
        }
    }
}

$stmt = $pdo->prepare('SELECT rating, comment FROM bike_reviews WHERE bike_id = ? AND user_id = ?');
$stmt->execute([$bikeId, $userId]);
$mine = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare('SELECT r.rating, r.comment, r.created_at, u.name AS reviewer
                       FROM bike_reviews r JOIN users u ON u.id = r.user_id
                       WHERE r.bike_id = ? ORDER BY r.created_at DESC');
$stmt->execute([$bikeId]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
$photos = bikeImages($bike);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($bike['name']) ?> | BikeMo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="customer-page">
    <main class="container bm-detail-page">
        <a href="bikes.php" class="bm-back-link">← Back to Browse Bikes</a>
        
        <section class="customer-panel bm-detail-panel">
            <span class="bm-detail-category"><?= $e($bike['type']) ?> bike</span>
            <h1><?= $e($bike['name']) ?></h1>
            <p><?= $e($bike['description'] ?: 'Explore the gallery and customer reviews.') ?></p>
            
            <div class="bm-gallery">
                <?php foreach ($photos as $path): ?>
                    <?php if (bikePhotoExists($path)): ?>
                        <img src="../<?= $e($path) ?>" alt="<?= $e($bike['name']) ?> bike photo" loading="lazy">
                    <?php endif; ?>
                <?php endforeach; ?>
                
                <?php if (!$photos): ?>
                    <div class="bike-art-fallback">🚲</div>
                <?php endif; ?>
            </div>
            
            <div class="bm-detail-rates">
                <b>₱<?= number_format((float) $bike['rate_per_hour'], 2) ?>/hour</b>
                <b>₱<?= number_format((float) $bike['rate_per_day'], 2) ?>/day</b>
                <a class="btn btn-primary btn-sm" href="bikes.php">Book a bike →</a>
            </div>
        </section>

        <section class="customer-panel bm-detail-panel">
            <h2>Customer reviews</h2>
            
            <?php if ($message): ?>
                <div class="customer-alert"><?= $e($message) ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="customer-alert error"><?= $e($error) ?></div>
            <?php endif; ?>
            
            <?php if ($eligible): ?>
                <form method="post" class="bm-review-form" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= $e($_SESSION['csrf_customer']) ?>">
                    <input type="hidden" name="bike_id" value="<?= $bikeId ?>">
                    
                    <label>Rating
                        <select name="rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>" <?= (int) ($mine['rating'] ?? 5) === $i ? 'selected' : '' ?>>
                                    <?= $i ?> star<?= $i === 1 ? '' : 's' ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </label>
                    
                    <label>Your review
                        <textarea name="comment" rows="4" placeholder="How was your ride?"><?= $e($mine['comment'] ?? '') ?></textarea>
                    </label>
                    
                    <button class="btn btn-primary" type="submit"><?= $mine ? 'Update review' : 'Post review' ?></button>
                </form>
            <?php else: ?>
                <p>Only customers who rented this bike can leave a review.</p>
            <?php endif; ?>

            <?php foreach ($reviews as $review): ?>
                <article class="bm-review-entry">
                    <b><?= $e($review['reviewer']) ?> · <?= (int) $review['rating'] ?>/5 ★</b>
                    <p><?= nl2br($e($review['comment'])) ?></p>
                </article>
            <?php endforeach; ?>
            
            <?php if (!$reviews): ?>
                <p>No reviews yet. Be the first after you rent this bike!</p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>