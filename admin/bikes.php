<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/uploads.php';
require_once __DIR__ . '/../includes/bike_images.php';
require_once __DIR__ . '/helpers.php';
requireRole('admin');

$adminPage = 'bikes';
$adminTitle = 'Manage Bikes';
$types = ['Mountain', 'Road', 'City', 'Electric', 'Folding', 'BMX'];
$statuses = ['available', 'rented', 'maintenance'];
$message = '';
$error = '';

if (empty($_SESSION['csrf_admin'])) {
    $_SESSION['csrf_admin'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $action = $_POST['action'] ?? '';

    try {
        if (!is_string($token) || !hash_equals($_SESSION['csrf_admin'], $token)) {
            throw new RuntimeException('Security check failed. Refresh the page.');
        }

        $id = (int) ($_POST['id'] ?? 0);

        if ($action === 'save') {
            $name = trim($_POST['name'] ?? '');
            $type = $_POST['type'] ?? '';
            $color = $_POST['color'] ?? '#ff6a1a';
            $hour = filter_var($_POST['rate_per_hour'] ?? null, FILTER_VALIDATE_FLOAT);
            $day = filter_var($_POST['rate_per_day'] ?? null, FILTER_VALIDATE_FLOAT);
            $status = $_POST['status'] ?? 'available';
            $gears = trim($_POST['gears'] ?? '');
            $frame = trim($_POST['frame'] ?? '');
            $weight = trim($_POST['weight'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $mainPath = trim($_POST['image_path'] ?? '');

            // PHP validation: do not depend on required/min/max attributes.
            if (strlen($name) < 2 || strlen($name) > 100 ||
                !in_array($type, $types, true) ||
                !in_array($status, $statuses, true) ||
                !preg_match('/^#[a-f0-9]{6}$/i', $color) ||
                $hour === false || $hour < 0 || $hour > 100000 ||
                $day === false || $day < 0 || $day > 100000) {
                throw new RuntimeException('Enter a valid bike name, category, status, color and prices.');
            }

            if ($mainPath !== '' && (!validBikeImagePath($mainPath) || !bikePhotoExists($mainPath))) {
                throw new RuntimeException('Main image not found. Use e.g. assets/images/bike.jpg.');
            }

            $existing = null;
            if ($id > 0) {
                $stmt = $pdo->prepare('SELECT * FROM bikes WHERE id = ?');
                $stmt->execute([$id]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$existing) throw new RuntimeException('Bike was not found.');
            }

            if ($id > 0 && $status !== 'rented') {
                $check = $pdo->prepare("SELECT COUNT(*) FROM rentals WHERE bike_id = ? AND status = 'Ongoing'");
                $check->execute([$id]);
                if ((int) $check->fetchColumn() > 0) {
                    throw new RuntimeException('Complete the ongoing rental before changing this bike from rented.');
                }
            }

            $pictures = $existing ? bikeImages($existing) : [];
            $removePaths = $_POST['remove_images'] ?? [];
            if (!is_array($removePaths)) $removePaths = [];
            $pictures = array_values(array_diff($pictures, $removePaths));

            // Manually copied images inside assets/images are accepted here.
            $manual = preg_split('/\R/', trim($_POST['manual_images'] ?? ''));
            foreach ($manual as $path) {
                $path = trim($path);
                if ($path === '') continue;
                if (!validBikeImagePath($path) || !bikePhotoExists($path)) {
                    throw new RuntimeException('Cannot find gallery photo: ' . $path);
                }
                $pictures[] = $path;
            }

            // Admin uploads: one or many photos (same user-friendly field).
            $files = $_FILES['photos'] ?? null;
            if ($files && is_array($files['name'])) {
                $provided = count(array_filter($files['name'], fn($name) => $name !== ''));
                if ($provided > 6) throw new RuntimeException('Upload a maximum of 6 photos at a time.');

                foreach ($files['name'] as $i => $filename) {
                    if ($filename === '') continue;
                    $file = [
                        'name' => $filename,
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i]
                    ];
                    $pictures[] = savePhoto($file, 'bikes');
                }
            }

            $pictures = array_values(array_unique($pictures));
            if (count($pictures) > 12) throw new RuntimeException('Maximum 12 photos per bike.');
            if ($mainPath === '' || in_array($mainPath, $removePaths, true)) {
                $mainPath = $pictures[0] ?? '';
            }
            if ($mainPath !== '' && !in_array($mainPath, $pictures, true)) {
                $pictures[] = $mainPath;
            }
            $gallery = array_values(array_diff($pictures, [$mainPath]));
            $galleryJson = json_encode($gallery);

            $values = [
                $name, $type, $color, $hour, $day, $gears, $frame,
                $weight, $description, $status, $mainPath ?: null, $galleryJson
            ];

            if ($id > 0) {
                $sql = 'UPDATE bikes SET name=?, type=?, color=?, rate_per_hour=?, rate_per_day=?,
                        gears=?, frame=?, weight=?, description=?, status=?, image_path=?, gallery_images=?
                        WHERE id=?';
                $pdo->prepare($sql)->execute([...$values, $id]);
                $message = 'Bike updated successfully.';
            } else {
                $sql = 'INSERT INTO bikes
                        (name,type,color,rate_per_hour,rate_per_day,gears,frame,weight,description,status,image_path,gallery_images)
                        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)';
                $pdo->prepare($sql)->execute($values);
                $message = 'New bike added successfully.';
            }
        } elseif ($action === 'status' || $action === 'delete') {
            if ($id < 1) throw new RuntimeException('Please select a bike.');
            $check = $pdo->prepare("SELECT COUNT(*) FROM rentals WHERE bike_id = ? AND status = 'Ongoing'");
            $check->execute([$id]);
            $ongoing = (int) $check->fetchColumn();

            if ($action === 'delete') {
                $history = $pdo->prepare('SELECT COUNT(*) FROM rentals WHERE bike_id = ?');
                $history->execute([$id]);
                if ((int) $history->fetchColumn() > 0) {
                    throw new RuntimeException('This bike has booking history. Set it to maintenance instead of deleting it.');
                }
                $pdo->prepare('DELETE FROM bikes WHERE id = ?')->execute([$id]);
                $message = 'Bike deleted.';
            } else {
                $status = $_POST['status'] ?? '';
                if (!in_array($status, $statuses, true)) throw new RuntimeException('Invalid bike status.');
                if ($ongoing && $status !== 'rented') {
                    throw new RuntimeException('Bike has an ongoing rental. Finish it first.');
                }
                $pdo->prepare('UPDATE bikes SET status = ? WHERE id = ?')->execute([$status, $id]);
                $message = 'Bike status updated.';
            }
        }
    } catch (RuntimeException $exception) {
        $error = $exception->getMessage();
    } catch (PDOException $exception) {
        error_log($exception->getMessage());
        $error = 'Database update failed. Check migration and table columns.';
    }
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM bikes WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
$images = $edit ? bikeImages($edit) : [];
$search = trim($_GET['q'] ?? '');
$filter = $_GET['status'] ?? '';
$sql = 'SELECT b.*, (SELECT COUNT(*) FROM rentals r WHERE r.bike_id = b.id) AS booking_count
        FROM bikes b WHERE 1 = 1';
$params = [];
if ($search !== '') {
    $sql .= ' AND (b.name LIKE ? OR b.type LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
if (in_array($filter, $statuses, true)) {
    $sql .= ' AND b.status = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY b.id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bikes = $stmt->fetchAll(PDO::FETCH_ASSOC);
require __DIR__ . '/_header.php';
?>

<?php if ($message): ?><div class="bm-admin-alert success">✓ <?= adminEscape($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="bm-admin-alert error">⚠ <?= adminEscape($error) ?></div><?php endif; ?>

<div class="bm-admin-page-intro">
    <div><h2>Your fleet</h2><p>Manage listings, images, pricing and availability in one place.</p></div>
    <a href="bikes.php#bike-form" class="btn btn-primary">＋ Add bike</a>
</div>

<section class="bm-admin-panel bm-admin-form-panel" id="bike-form">
    <div class="bm-admin-panel-head">
        <div>
            <h3><?= $edit ? 'Edit bike details' : 'Add a bike to your fleet' ?></h3>
            <p>Save an image from the web in assets/images, or upload images below.</p>
        </div>
        <?php if ($edit): ?><a href="bikes.php" class="bm-admin-text-link">Cancel edit</a><?php endif; ?>
    </div>

    <form method="post" enctype="multipart/form-data" class="bm-admin-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?= adminEscape($_SESSION['csrf_admin']) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">

        <div class="bm-admin-form-grid">
            <div class="field"><label for="bike-name">Bike name *</label>
                <input id="bike-name" name="name" value="<?= adminEscape($edit['name'] ?? '') ?>" placeholder="e.g. Trailblazer X1"></div>
            <div class="field"><label>Category *</label><select name="type">
                <?php foreach ($types as $type): ?>
                    <option value="<?= $type ?>" <?= ($edit['type'] ?? 'Mountain') === $type ? 'selected' : '' ?>><?= $type ?></option>
                <?php endforeach; ?>
            </select></div>
            <div class="field"><label>Hourly rate (₱) *</label><input name="rate_per_hour" inputmode="decimal" value="<?= adminEscape($edit['rate_per_hour'] ?? '80.00') ?>"></div>
            <div class="field"><label>Daily rate (₱) *</label><input name="rate_per_day" inputmode="decimal" value="<?= adminEscape($edit['rate_per_day'] ?? '450.00') ?>"></div>
            <div class="field"><label>Status</label><select name="status">
                <?php foreach ($statuses as $status): ?><option value="<?= $status ?>" <?= ($edit['status'] ?? 'available') === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?>
            </select></div>
            <div class="field"><label>Bike accent color</label><input type="color" name="color" value="<?= adminEscape($edit['color'] ?? '#ff6a1a') ?>"></div>
            <div class="field"><label>Gears</label><input name="gears" value="<?= adminEscape($edit['gears'] ?? '') ?>" placeholder="21-speed"></div>
            <div class="field"><label>Frame material</label><input name="frame" value="<?= adminEscape($edit['frame'] ?? '') ?>" placeholder="Aluminum"></div>
            <div class="field"><label>Weight</label><input name="weight" value="<?= adminEscape($edit['weight'] ?? '') ?>" placeholder="14 kg"></div>
            <div class="field bm-admin-field-wide"><label>Description</label><textarea name="description" rows="3"><?= adminEscape($edit['description'] ?? '') ?></textarea></div>
      
            <div class="field bm-admin-field-wide"><label>Upload bike photos — one or multiple (JPG, PNG, WebP; up to 6 at once)</label>
                <input name="photos[]" type="file" accept="image/jpeg,image/png,image/webp" multiple>
            </div>
        </div>

        <?php if ($images): ?>
        <div class="bm-photo-strip">
            <?php foreach ($images as $path): ?>
                <label class="bike-photo-choice">
                    <img src="../<?= adminEscape($path) ?>" alt="Existing bike photo" loading="lazy">
                    <span><input type="checkbox" name="remove_images[]" value="<?= adminEscape($path) ?>"> Remove</span>
                </label>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="bm-admin-form-actions">
            <button class="btn btn-primary" type="submit"><?= $edit ? 'Save changes' : '＋ Add bike' ?></button>
            <?php if ($edit): ?><a href="bikes.php" class="btn btn-outline">Cancel</a><?php endif; ?>
        </div>
    </form>
</section>

<section class="bm-admin-panel bm-admin-list-panel">
    <div class="bm-admin-panel-head"><div><h3>Bike inventory <span class="bm-admin-count"><?= count($bikes) ?></span></h3><p>Everything here comes from the bikes database table.</p></div></div>
    <form class="bm-admin-filters" method="get">
        <input type="search" name="q" value="<?= adminEscape($search) ?>" placeholder="Search bikes...">
        <select name="status"><option value="">All statuses</option>
            <?php foreach ($statuses as $status): ?><option value="<?= $status ?>" <?= $filter === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?>
        </select>
        <button class="btn btn-dark btn-sm">Filter</button><a class="btn btn-outline btn-sm" href="bikes.php">Reset</a>
    </form>
    <div class="tbl-wrap"><table><thead><tr><th>Bike</th><th>Type</th><th>Rates</th><th>Status</th><th>Bookings</th><th>Actions</th></tr></thead><tbody>
    <?php foreach ($bikes as $bike): ?>
        <?php $photos = bikeImages($bike); ?>
        <tr>
            <td><div class="bm-admin-bike-cell">
                <?php if ($photos && bikePhotoExists($photos[0])): ?>
                    <img class="bm-admin-bike-thumb" src="../<?= adminEscape($photos[0]) ?>" alt="<?= adminEscape($bike['name']) ?>">
                <?php else: ?><span class="bm-admin-bike-dot" style="background:<?= adminEscape($bike['color']) ?>">🚲</span><?php endif; ?>
                <span><b><?= adminEscape($bike['name']) ?></b><small class="bm-admin-cell-sub"><?= adminEscape($bike['gears']) ?> · <?= adminEscape($bike['frame']) ?></small></span></div>
            </td>
            <td><?= adminEscape($bike['type']) ?></td>
            <td><b>₱<?= number_format((float) $bike['rate_per_hour'], 2) ?></b><small class="bm-admin-cell-sub">₱<?= number_format((float) $bike['rate_per_day'], 2) ?>/day</small></td>
            <td><form method="post" class="bm-admin-inline-form">
                <input type="hidden" name="csrf_token" value="<?= adminEscape($_SESSION['csrf_admin']) ?>">
                <input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= (int) $bike['id'] ?>">
                <select name="status" class="bm-admin-status-select" aria-label="Status for <?= adminEscape($bike['name']) ?>">
                    <?php foreach ($statuses as $status): ?><option value="<?= $status ?>" <?= $bike['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option><?php endforeach; ?>
                </select><button type="submit" class="btn btn-primary btn-sm">Save</button>
            </form></td>
            <td><?= (int) $bike['booking_count'] ?></td>
            <td><div class="bm-admin-actions">
                <a class="bm-admin-action edit" href="bikes.php?edit=<?= (int) $bike['id'] ?>#bike-form" title="Edit">✎</a>
                <form method="post" onsubmit="return confirm('Delete this bike? Only bikes with no bookings can be deleted.');">
                    <input type="hidden" name="csrf_token" value="<?= adminEscape($_SESSION['csrf_admin']) ?>">
                    <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $bike['id'] ?>">
                    <button class="bm-admin-action delete" title="Delete" type="submit">×</button>
                </form>
            </div></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$bikes): ?><tr><td colspan="6"><div class="bm-admin-empty"><b>No bikes found.</b><p>Add a bike or change the filter.</p></div></td></tr><?php endif; ?>
    </tbody></table></div>
</section>

<?php require __DIR__ . '/_footer.php'; ?>
