<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/uploads.php';
require_once __DIR__ . '/helpers.php';
requireRole('admin');
if (empty($_SESSION['csrf_admin'])) {
    $_SESSION['csrf_admin'] = bin2hex(random_bytes(32));
}

$adminPage = 'profile';
$adminTitle = 'My Profile';
$message = '';
$error = '';
$id = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (!is_string($token) || !hash_equals($_SESSION['csrf_admin'] ?? '', $token)) {
        $error = 'Security check failed. Refresh the page and try again.';
    } elseif (mb_strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($phone) < 5) {
        $error = 'Enter a valid name, email address, and phone number.';
    } else {
        try {
            $photo = savePhoto($_FILES['photo'] ?? [], 'users');
            if ($photo !== '') {
                $save = $pdo->prepare('UPDATE users SET name = ?, email = ?, phone = ?, photo = ? WHERE id = ?');
                $save->execute([$name, $email, $phone, $photo, $id]);
            } else {
                $save = $pdo->prepare('UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?');
                $save->execute([$name, $email, $phone, $id]);
            }
            $_SESSION['name'] = $name;
            $message = 'Admin profile saved.';
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $error = 'That email address is already used by another account.';
            } else {
                error_log($e->getMessage());
                $error = 'Could not save the profile. Check the database connection.';
            }
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        }
    }
}

$profile = $pdo->prepare('SELECT name, email, phone, photo FROM users WHERE id = ?');
$profile->execute([$id]);
$adminUser = $profile->fetch(PDO::FETCH_ASSOC);
$profilePhoto = $adminUser['photo'] ?? '';
$profilePhotoExists = is_string($profilePhoto)
    && preg_match('~^uploads/users/[a-zA-Z0-9_-]+\.(jpg|jpeg|png|webp)$~i', $profilePhoto)
    && is_file(__DIR__ . '/../' . $profilePhoto);
$e = fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
require __DIR__ . '/_header.php';
?>

<section class="bm-admin-panel bm-admin-profile-editor">
    <h2>Edit administrator profile</h2>
    <?php if ($message): ?><div class="bm-admin-alert success"><?= adminEscape($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="bm-admin-alert error"><?= adminEscape($error) ?></div><?php endif; ?>

    <div class="bm-profile-summary">
        <?php if ($profilePhotoExists): ?>
            <img class="bm-profile-photo" width="88" height="88" src="../<?= $e($profilePhoto) ?>" alt="<?= $e($adminUser['name']) ?> profile photo">
        <?php else: ?>
            <div class="bm-profile-photo bm-profile-photo--fallback"><?= $e(mb_strtoupper(mb_substr(trim($adminUser['name']), 0, 1, 'UTF-8'))) ?></div>
        <?php endif; ?>
        <div><strong><?= $e($adminUser['name']) ?></strong><span><?= $e($adminUser['email']) ?></span></div>
    </div>

    <form method="post" enctype="multipart/form-data" class="bm-admin-form bm-admin-profile-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= $e($_SESSION['csrf_admin']) ?>">
        <div class="field"><label for="admin-name">Name</label><input id="admin-name" name="name" value="<?= $e($adminUser['name']) ?>"></div>
        <div class="field"><label for="admin-email">Email</label><input id="admin-email" name="email" type="text" value="<?= $e($adminUser['email']) ?>"></div>
        <div class="field"><label for="admin-phone">Phone</label><input id="admin-phone" name="phone" value="<?= $e($adminUser['phone']) ?>"></div>
        <div class="field"><label for="admin-photo">Change profile photo</label><input id="admin-photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG, or WebP; maximum 3 MB. Leave empty to keep the current photo.</small></div>
        <button class="btn btn-primary" type="submit">Save profile</button>
    </form>
</section>

<?php require __DIR__ . '/_footer.php'; ?>
