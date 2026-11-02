<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('user');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/uploads.php';

$error = '';
$message = '';
$id = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if (strlen($name) < 2 || strlen($phone) < 5) {
        $error = 'Please enter a valid name and phone number.';
    } else {
        try {
            $photo = savePhoto($_FILES['photo'] ?? [], 'users');
            if ($photo) {
                $stmt = $pdo->prepare('UPDATE users SET name=?, phone=?, photo=? WHERE id=?');
                $stmt->execute([$name, $phone, $photo, $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE users SET name=?, phone=? WHERE id=?');
                $stmt->execute([$name, $phone, $id]);
            }
            
            $_SESSION['name'] = $name;
            $message = 'Profile saved.';
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }
    }
}

$stmt = $pdo->prepare('SELECT name, email, phone, photo FROM users WHERE id=?');
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$profilePhoto = $user['photo'] ?? '';
$profilePhotoExists = is_string($profilePhoto)
    && preg_match('~^uploads/users/[a-zA-Z0-9_-]+\.(jpg|jpeg|png|webp)$~i', $profilePhoto)
    && is_file(__DIR__ . '/../' . $profilePhoto);

$e = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Profile | BikeMo</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=profile-receipt-spacing-20261102">
</head>
<body class="customer-page">
    <?php require __DIR__ . '/../includes/customer_nav.php'; ?>
    <main class="customer-page">
        <div class="container" style="padding-top:55px; max-width:760px">
            <a href="dashboard.php">← Dashboard</a>
            
            <section class="customer-panel">
                <h1>My Profile</h1>
                <p><?= $e($message ?: $error) ?></p>

                <div class="bm-profile-summary">
                    <?php if ($profilePhotoExists): ?>
                        <img class="bm-profile-photo" width="88" height="88" src="../<?= $e($profilePhoto) ?>" alt="<?= $e($user['name']) ?> profile photo">
                    <?php else: ?>
                        <div class="bm-profile-photo bm-profile-photo--fallback"><?= $e(mb_strtoupper(mb_substr(trim($user['name']), 0, 1, 'UTF-8'))) ?></div>
                    <?php endif; ?>
                    <div><strong><?= $e($user['name']) ?></strong><span><?= $e($user['email']) ?></span></div>
                </div>

                <form method="post" enctype="multipart/form-data" class="customer-form bm-profile-form" novalidate>
                    <label>Name
                        <input name="name" value="<?= $e($user['name']) ?>">
                    </label>
                    
                    <label>Email
                        <input value="<?= $e($user['email']) ?>" readonly>
                    </label>
                    
                    <label>Phone
                        <input name="phone" value="<?= $e($user['phone']) ?>">
                    </label>
                    
                    <label>New photo
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
                    </label>
                    
                    <button class="btn btn-primary">Save profile</button>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
