
<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/helpers.php';

requireRole('admin');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['csrf_admin'])) {
    $_SESSION['csrf_admin'] = bin2hex(random_bytes(32));
}

$adminPage = 'rentals';
$adminTitle = 'Rentals & Bookings';
$message = '';
$error = '';

/*
|--------------------------------------------------------------------------
| Update rental status
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_admin'] ?? '', $token)
    ) {
        $error = 'Security check failed. Refresh the page and try again.';
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if (
            $id < 1 ||
            !in_array($status, ['Ongoing', 'Completed'], true)
        ) {
            $error = 'Invalid booking or status.';
        } else {
            try {
                $pdo->beginTransaction();

                $find = $pdo->prepare(
                    'SELECT bike_id, status
                     FROM rentals
                     WHERE id = ?
                     FOR UPDATE'
                );
                $find->execute([$id]);

                $rental = $find->fetch(PDO::FETCH_ASSOC);

                if (!$rental) {
                    throw new RuntimeException('Booking not found.');
                }

                $update = $pdo->prepare(
                    'UPDATE rentals
                     SET status = ?, returned_at = ?
                     WHERE id = ?'
                );

                $returnedAt = $status === 'Completed'
                    ? date('Y-m-d H:i:s')
                    : null;

                $update->execute([$status, $returnedAt, $id]);

                /*
                 * Sync bike availability when a rental is completed.
                 * Only release the bike if no other ongoing rental
                 * references that bike.
                 */
                if (
                    $status === 'Completed' &&
                    $rental['status'] !== 'Completed'
                ) {
                    $other = $pdo->prepare(
                        "SELECT COUNT(*)
                         FROM rentals
                         WHERE bike_id = ?
                           AND status = 'Ongoing'
                           AND id <> ?"
                    );

                    $other->execute([$rental['bike_id'], $id]);

                    if ((int) $other->fetchColumn() === 0) {
                        $bike = $pdo->prepare(
                            "UPDATE bikes
                             SET status = 'available'
                             WHERE id = ?
                               AND status = 'rented'"
                        );

                        $bike->execute([$rental['bike_id']]);
                    }
                } elseif ($status === 'Ongoing') {
                    $bike = $pdo->prepare(
                        "UPDATE bikes
                         SET status = 'rented'
                         WHERE id = ?
                           AND status = 'available'"
                    );

                    $bike->execute([$rental['bike_id']]);
                }

                $pdo->commit();
                $message = 'Booking status updated successfully.';

            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                error_log($e->getMessage());

                $error = $e instanceof RuntimeException
                    ? $e->getMessage()
                    : 'Hindi na-update ang booking. I-check ang database columns at bike status values.';
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Search, filters, and rental records
|--------------------------------------------------------------------------
*/
$filter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');

$sql = "
    SELECT
        r.id,
        r.code,
        r.start_time,
        r.end_time,
        r.qty,
        r.unit,
        r.total,
        r.payment,
        r.status,
        r.created_at,
        r.returned_at,
        u.name AS customer_name,
        u.email AS customer_email,
        b.name AS bike_name
    FROM rentals r
    JOIN users u ON u.id = r.user_id
    JOIN bikes b ON b.id = r.bike_id
    WHERE 1 = 1
";

$params = [];

if (in_array($filter, ['Ongoing', 'Completed'], true)) {
    $sql .= ' AND r.status = ?';
    $params[] = $filter;
}

if ($search !== '') {
    $sql .= "
        AND (
            r.code LIKE ?
            OR u.name LIKE ?
            OR u.email LIKE ?
            OR b.name LIKE ?
        )
    ";

    $like = '%' . $search . '%';

    array_push($params, $like, $like, $like, $like);
}

$sql .= ' ORDER BY r.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$rentals = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ongoing = (int) $pdo->query(
    "SELECT COUNT(*) FROM rentals WHERE status = 'Ongoing'"
)->fetchColumn();

$completed = (int) $pdo->query(
    "SELECT COUNT(*) FROM rentals WHERE status = 'Completed'"
)->fetchColumn();

require __DIR__ . '/_header.php';
?>

<?php if ($message): ?>
    <div class="bm-admin-alert success">
        ✓ <?= adminEscape($message) ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="bm-admin-alert error">
        ⚠ <?= adminEscape($error) ?>
    </div>
<?php endif; ?>

<div class="bm-admin-page-intro">
    <div>
        <h2>Booking activity</h2>
        <p>
            Review customer reservations and keep rental statuses up to date.
        </p>
    </div>
</div>

<div class="bm-admin-mini-stats">
    <div>
        <span class="bm-admin-stat-icon blue">↻</span>
        <span>
            <small>Ongoing rentals</small>
            <b><?= $ongoing ?></b>
        </span>
    </div>

    <div>
        <span class="bm-admin-stat-icon green">✓</span>
        <span>
            <small>Completed rentals</small>
            <b><?= $completed ?></b>
        </span>
    </div>

    <div>
        <span class="bm-admin-stat-icon orange">▤</span>
        <span>
            <small>Total bookings shown</small>
            <b><?= count($rentals) ?></b>
        </span>
    </div>
</div>

<section class="bm-admin-panel bm-admin-list-panel">

    <div class="bm-admin-panel-head">
        <div>
            <h3>
                All bookings
                <span class="bm-admin-count"><?= count($rentals) ?></span>
            </h3>
            <p>Status changes are saved directly to your database.</p>
        </div>
    </div>

    <form class="bm-admin-filters" method="get">
        <input
            type="search"
            name="q"
            value="<?= adminEscape($search) ?>"
            placeholder="Search booking, customer or bike..."
        >

        <select name="status">
            <option value="">All statuses</option>

            <option
                value="Ongoing"
                <?= $filter === 'Ongoing' ? 'selected' : '' ?>
            >
                Ongoing
            </option>

            <option
                value="Completed"
                <?= $filter === 'Completed' ? 'selected' : '' ?>
            >
                Completed
            </option>
        </select>

        <button class="btn btn-dark btn-sm" type="submit">
            Filter
        </button>

        <a class="btn btn-outline btn-sm" href="rentals.php">
            Reset
        </a>
    </form>

    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>Booking</th>
                    <th>Customer</th>
                    <th>Bike &amp; schedule</th>
                    <th>Payment / Total</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($rentals as $r): ?>
                    <tr>
                        <td>
                            <b><?= adminEscape($r['code']) ?></b>

                            <small class="bm-admin-cell-sub">
                                Booked
                                <?= adminEscape(
                                    date('M j, Y', strtotime($r['created_at']))
                                ) ?>
                            </small>
                        </td>

                        <td>
                            <b><?= adminEscape($r['customer_name']) ?></b>

                            <small class="bm-admin-cell-sub">
                                <?= adminEscape($r['customer_email']) ?>
                            </small>
                        </td>

                        <td>
                            <b><?= adminEscape($r['bike_name']) ?></b>

                            <small class="bm-admin-cell-sub">
                                <?= adminEscape(
                                    date(
                                        'M j, Y g:i A',
                                        strtotime($r['start_time'])
                                    )
                                ) ?>
                                –
                                <?= adminEscape(
                                    date(
                                        'M j, Y g:i A',
                                        strtotime($r['end_time'])
                                    )
                                ) ?>
                            </small>

                            <small class="bm-admin-cell-sub">
                                <?= (int) $r['qty'] ?>
                                <?= adminEscape($r['unit']) ?>
                            </small>
                        </td>

                        <td>
                            <b>
                                ₱<?= number_format((float) $r['total'], 2) ?>
                            </b>

                            <small class="bm-admin-cell-sub">
                                <?= adminEscape($r['payment']) ?>
                            </small>
                        </td>

                        <td>
                            <form
                                method="post"
                                class="bm-admin-rental-status"
                            >
                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= adminEscape(
                                        $_SESSION['csrf_admin']
                                    ) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $r['id'] ?>"
                                >

                                <select
                                    name="status"
                                    class="bm-admin-status-select"
                                    aria-label="Rental status"
                                >
                                    <option
                                        value="Ongoing"
                                        <?= $r['status'] === 'Ongoing'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Ongoing
                                    </option>

                                    <option
                                        value="Completed"
                                        <?= $r['status'] === 'Completed'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Completed
                                    </option>
                                </select>

                                <button
                                    class="btn btn-primary btn-sm"
                                    type="submit"
                                >
                                    Save
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$rentals): ?>
                    <tr>
                        <td colspan="5">
                            <div class="bm-admin-empty">
                                <span>▤</span>
                                <b>No bookings found</b>
                                <p>
                                    Bookings will appear here once customers
                                    make reservations.
                                </p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/_footer.php'; ?>
