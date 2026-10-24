<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/helpers.php';
requireRole('admin');
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['csrf_admin'])) $_SESSION['csrf_admin'] = bin2hex(random_bytes(32));
$adminPage = 'bikes'; $adminTitle = 'Manage Bikes';
$types = ['Mountain','Road','City','Electric','Folding','BMX'];
$statuses = ['available','rented','maintenance'];
$message = ''; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!hash_equals($_SESSION['csrf_admin'] ?? '', $_POST['csrf_token'] ?? '')) { $error = 'Security check failed. Refresh the page and try again.'; }
  else {
    $action = $_POST['action'] ?? '';
    try {
      if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0); $name = trim($_POST['name'] ?? ''); $type = $_POST['type'] ?? '';
        $color = trim($_POST['color'] ?? '#ff6a1a'); $hour = filter_var($_POST['rate_per_hour'] ?? null, FILTER_VALIDATE_FLOAT); $day = filter_var($_POST['rate_per_day'] ?? null, FILTER_VALIDATE_FLOAT);
        $gears = trim($_POST['gears'] ?? '—'); $frame = trim($_POST['frame'] ?? '—'); $weight = trim($_POST['weight'] ?? '—'); $description = trim($_POST['description'] ?? ''); $status = $_POST['status'] ?? 'available';
        if ($name === '' || !in_array($type, $types, true) || !preg_match('/^#[0-9a-fA-F]{6}$/', $color) || $hour === false || $hour < 0 || $day === false || $day < 0 || !in_array($status, $statuses, true)) throw new RuntimeException('Please fill in the required fields with valid values.');
        if ($id > 0) {
          $stmt = $pdo->prepare('UPDATE bikes SET name=?, type=?, color=?, rate_per_hour=?, rate_per_day=?, gears=?, frame=?, weight=?, description=?, status=? WHERE id=?');
          $stmt->execute([$name,$type,$color,$hour,$day,$gears,$frame,$weight,$description,$status,$id]); $message = 'Bike details updated successfully.';
        } else {
          $stmt = $pdo->prepare('INSERT INTO bikes (name,type,color,rate_per_hour,rate_per_day,gears,frame,weight,description,status) VALUES (?,?,?,?,?,?,?,?,?,?)');
          $stmt->execute([$name,$type,$color,$hour,$day,$gears,$frame,$weight,$description,$status]); $message = 'New bike added successfully.';
        }
      } elseif ($action === 'status') {
        $id = (int)($_POST['id'] ?? 0); $status = $_POST['status'] ?? '';
        if ($id < 1 || !in_array($status, $statuses, true)) throw new RuntimeException('Invalid bike or status.');
        if ($status === 'available') {
          $check = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE bike_id=? AND status='Ongoing'"); $check->execute([$id]);
          if ((int)$check->fetchColumn() > 0) throw new RuntimeException('This bike has an ongoing rental. Complete the rental before marking it available.');
        }
        $stmt = $pdo->prepare('UPDATE bikes SET status=? WHERE id=?'); $stmt->execute([$status,$id]); $message = 'Bike status updated.';
      } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0); if ($id < 1) throw new RuntimeException('Invalid bike selected.');
        $stmt = $pdo->prepare('DELETE FROM bikes WHERE id=?'); $stmt->execute([$id]);
        $message = $stmt->rowCount() ? 'Bike deleted.' : 'Bike was not found.';
      }
    } catch (RuntimeException $e) { $error = $e->getMessage(); }
      catch (PDOException $e) { error_log($e->getMessage()); $error = $action === 'delete' ? 'Hindi puwedeng burahin ang bike na may booking history. I-update na lang ang status nito.' : 'Database operation failed. I-check ang database schema at subukan ulit.'; }
  }
}
$edit = null;
if (isset($_GET['edit'])) { $stmt = $pdo->prepare('SELECT * FROM bikes WHERE id=?'); $stmt->execute([(int)$_GET['edit']]); $edit = $stmt->fetch(PDO::FETCH_ASSOC) ?: null; }
$search = trim($_GET['q'] ?? ''); $filter = $_GET['status'] ?? '';
$sql = 'SELECT b.*, (SELECT COUNT(*) FROM transactions t WHERE t.bike_id=b.id) AS booking_count FROM bikes b WHERE 1=1'; $params=[];
if ($search !== '') { $sql .= ' AND (b.name LIKE ? OR b.type LIKE ?)'; $params[]='%'.$search.'%'; $params[]='%'.$search.'%'; }
if (in_array($filter, $statuses, true)) { $sql .= ' AND b.status=?'; $params[]=$filter; }
$sql .= ' ORDER BY b.id DESC'; $stmt=$pdo->prepare($sql); $stmt->execute($params); $bikes=$stmt->fetchAll(PDO::FETCH_ASSOC);
require __DIR__ . '/_header.php';
?>
<?php if ($message): ?><div class="bm-admin-alert success">✓ <?= adminEscape($message) ?></div><?php endif; ?><?php if ($error): ?><div class="bm-admin-alert error">⚠ <?= adminEscape($error) ?></div><?php endif; ?>
<div class="bm-admin-page-intro"><div><h2>Your fleet</h2><p>Add bikes, maintain accurate rental rates, and control availability.</p></div><a href="#bike-form" class="btn btn-primary">＋ Add bike</a></div>
<div class="bm-admin-panel bm-admin-form-panel" id="bike-form"><div class="bm-admin-panel-head"><div><h3><?= $edit ? 'Edit bike details' : 'Add a bike to your fleet' ?></h3><p>Fields marked * are required. Changes are saved directly to MySQL.</p></div><?php if ($edit): ?><a href="bikes.php" class="bm-admin-text-link">Cancel edit</a><?php endif; ?></div>
<form method="post" class="bm-admin-form"><input type="hidden" name="csrf_token" value="<?= adminEscape($_SESSION['csrf_admin']) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
<div class="bm-admin-form-grid"><div class="field"><label for="name">Bike name *</label><input id="name" name="name" required maxlength="150" value="<?= adminEscape($edit['name'] ?? '') ?>" placeholder="e.g. Trailblazer X1"></div>
<div class="field"><label for="type">Category *</label><select id="type" name="type" required><?php foreach ($types as $type): ?><option value="<?= $type ?>" <?= ($edit['type'] ?? 'Mountain') === $type ? 'selected' : '' ?>><?= $type ?></option><?php endforeach; ?></select></div>
<div class="field"><label for="rate_per_hour">Rate per hour (₱) *</label><input id="rate_per_hour" name="rate_per_hour" type="number" min="0" step="0.01" required value="<?= adminEscape($edit['rate_per_hour'] ?? '80.00') ?>"></div>
<div class="field"><label for="rate_per_day">Rate per day (₱) *</label><input id="rate_per_day" name="rate_per_day" type="number" min="0" step="0.01" required value="<?= adminEscape($edit['rate_per_day'] ?? '450.00') ?>"></div>
<div class="field"><label for="status">Availability status</label><select id="status" name="status"><?php foreach ($statuses as $s): ?><option value="<?= $s ?>" <?= ($edit['status'] ?? 'available') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
<div class="field"><label for="color">Bike accent color</label><input id="color" name="color" type="color" value="<?= adminEscape($edit['color'] ?? '#ff6a1a') ?>"></div>
<div class="field"><label for="gears">Gears</label><input id="gears" name="gears" maxlength="80" value="<?= adminEscape($edit['gears'] ?? '') ?>" placeholder="e.g. 21-speed"></div>
<div class="field"><label for="frame">Frame material</label><input id="frame" name="frame" maxlength="80" value="<?= adminEscape($edit['frame'] ?? '') ?>" placeholder="e.g. Aluminum"></div>
<div class="field"><label for="weight">Weight</label><input id="weight" name="weight" maxlength="40" value="<?= adminEscape($edit['weight'] ?? '') ?>" placeholder="e.g. 14.5 kg"></div>
<div class="field bm-admin-field-wide"><label for="description">Description</label><textarea id="description" name="description" rows="3" placeholder="Describe this bike for customers..."><?= adminEscape($edit['description'] ?? '') ?></textarea></div></div>
<div class="bm-admin-form-actions"><button class="btn btn-primary" type="submit"><?= $edit ? 'Save changes' : '＋ Add bike' ?></button><?php if ($edit): ?><a class="btn btn-outline" href="bikes.php">Cancel</a><?php endif; ?></div></form></div>
<section class="bm-admin-panel bm-admin-list-panel"><div class="bm-admin-panel-head"><div><h3>Bike inventory <span class="bm-admin-count"><?= count($bikes) ?></span></h3><p>Manage each bike in your database.</p></div></div>
<form class="bm-admin-filters" method="get"><input type="search" name="q" value="<?= adminEscape($search) ?>" placeholder="Search bike name or category..."><select name="status"><option value="">All statuses</option><?php foreach ($statuses as $s): ?><option value="<?= $s ?>" <?= $filter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select><button class="btn btn-dark btn-sm">Filter</button><a class="btn btn-outline btn-sm" href="bikes.php">Reset</a></form>
<div class="tbl-wrap"><table><thead><tr><th>Bike</th><th>Category</th><th>Hourly / Daily rate</th><th>Status</th><th>Bookings</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($bikes as $bike): ?><tr><td><div class="bm-admin-bike-cell"><span class="bm-admin-bike-dot" style="background:<?= adminEscape($bike['color']) ?>">🚲</span><span><b><?= adminEscape($bike['name']) ?></b><small class="bm-admin-cell-sub"><?= adminEscape($bike['gears']) ?> · <?= adminEscape($bike['frame']) ?></small></span></div></td><td><?= adminEscape($bike['type']) ?></td><td><b>₱<?= number_format((float)$bike['rate_per_hour'],2) ?></b><small class="bm-admin-cell-sub">₱<?= number_format((float)$bike['rate_per_day'],2) ?>/day</small></td><td><form method="post" class="bm-admin-inline-form"><input type="hidden" name="csrf_token" value="<?= adminEscape($_SESSION['csrf_admin']) ?>"><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= (int)$bike['id'] ?>"><select aria-label="Change status for <?= adminEscape($bike['name']) ?>" name="status" class="bm-admin-status-select" onchange="this.form.submit()"><?php foreach ($statuses as $s): ?><option value="<?= $s ?>" <?= $bike['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></form></td><td><?= (int)$bike['booking_count'] ?></td><td><div class="bm-admin-actions"><a class="bm-admin-action edit" href="bikes.php?edit=<?= (int)$bike['id'] ?>#bike-form" title="Edit bike">✎</a><form method="post" onsubmit="return confirm('Delete this bike? Bikes with booking history cannot be deleted.');"><input type="hidden" name="csrf_token" value="<?= adminEscape($_SESSION['csrf_admin']) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$bike['id'] ?>"><button class="bm-admin-action delete" title="Delete bike" type="submit">×</button></form></div></td></tr><?php endforeach; ?>
<?php if (!$bikes): ?><tr><td colspan="6"><div class="bm-admin-empty"><span>🚲</span><b>No bikes found</b><p>Add a bike or change your search filter.</p></div></td></tr><?php endif; ?></tbody></table></div></section>
<?php require __DIR__ . '/_footer.php'; ?>
