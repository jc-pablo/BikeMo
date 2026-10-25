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

$e = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Profile | BikeMo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="customer-page">
        <div class="container" style="padding-top:55px; max-width:760px">
            <a href="dashboard.php">← Dashboard</a>
            
            <section class="customer-panel">
                <h1>My Profile</h1>
                <p><?= $e($message ?: $error) ?></p>

                <?php if (!empty($user['photo'])): ?>
                    <img src="../<?= $e($user['photo']) ?>" alt="Profile photo" style="width:100px; height:100px; object-fit:cover; border-radius:50%"><br>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data" class="customer-form">
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