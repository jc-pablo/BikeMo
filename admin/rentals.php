
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_admin'] ?? '', $token)) {
        $error = 'Security check failed. Refresh the page and try again.';
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if ($id < 1 || !in_array($status, ['Ongoing', 'Completed'], true)) {
            $error = 'Invalid booking or status.';
        } else {
            try {
                $pdo->beginTransaction();
                $find = $pdo->prepare('SELECT order_code FROM rentals WHERE id = ? FOR UPDATE');
                $find->execute([$id]);
                $orderCode = $find->fetchColumn();
                if (!$orderCode) throw new RuntimeException('Order not found.');

               $itemsQuery = $pdo->prepare(
    'SELECT id, bike_id, status, end_time, returned_at, late_fee
     FROM rentals
     WHERE order_code = ?
     FOR UPDATE'
);
$itemsQuery->execute([$orderCode]);
$orderRows = $itemsQuery->fetchAll(PDO::FETCH_ASSOC);

if (!$orderRows) {
    throw new RuntimeException('Order not found.');
}

$statusChanged = false;
$bikeIds = [];

$updateOne = $pdo->prepare(
    'UPDATE rentals
     SET status = ?, returned_at = ?, late_fee = ?
     WHERE id = ?'
);

$returnTime = new DateTimeImmutable('now');

foreach ($orderRows as $row) {
    if ($row['status'] !== $status) {
        $statusChanged = true;
    }

    $bikeIds[(int) $row['bike_id']] = true;

    if ($status === 'Completed' && $row['status'] !== 'Completed') {
        $dueTimestamp = strtotime($row['end_time']);

        $graceSeconds = 30 * 60; 
        $lateRatePerHour = 50;   // PHP 50 per late hour

        $lateSeconds = max(
            0,
            $returnTime->getTimestamp() - $dueTimestamp - $graceSeconds
        );

        $lateHours = (int) ceil($lateSeconds / 3600);
        $lateFee = $lateHours * $lateRatePerHour;
        $returnedAt = $returnTime->format('Y-m-d H:i:s');
    } elseif ($status === 'Completed') {
        // Preserve the saved fee and return time if already completed.
        $lateFee = (float) $row['late_fee'];
        $returnedAt = $row['returned_at'];
    } else {
        // Reset these if the order is changed back to Ongoing.
        $lateFee = 0;
        $returnedAt = null;
    }

    $updateOne->execute([
        $status,
        $returnedAt,
        $lateFee,
        (int) $row['id'],
    ]);
}
                $pdo->commit();
                $message = 'Order status updated successfully.';

                if ($statusChanged) {
                    try {
                        require_once __DIR__ . '/../PHPMailer/mailer.php';
                        require_once __DIR__ . '/../includes/receipt_pdf.php';
                        $details = $pdo->prepare(
                            'SELECT u.name AS customer_name, u.email AS customer_email,
                                    r.code, r.start_time, r.end_time, r.duration_value, r.unit, r.total, r.late_fee,
                                    b.name AS bike_name
                             FROM rentals r
                             JOIN users u ON u.id = r.user_id
                             JOIN bikes b ON b.id = r.bike_id
                             WHERE r.order_code = ? ORDER BY r.id'
                        );
                        $details->execute([$orderCode]);
                        $orderItems = $details->fetchAll(PDO::FETCH_ASSOC);
                        if ($orderItems) {
                            $customer = ['name' => $orderItems[0]['customer_name'], 'email' => $orderItems[0]['customer_email']];
                            $grandTotal = array_sum(array_map(fn($item) => (float) $item['total']  + (float) $item['late_fee'], $orderItems));
                            $body = "Hello {$customer['name']},\n\n" .
                                "The status of order {$orderCode} is now {$status}.\n\nProducts/services:\n";
                            foreach ($orderItems as $item) {
                               $lineTotal = (float) $item['total'] + (float) $item['late_fee'];

                            $body .= '- ' . $item['bike_name'] .
                             ' (' . (int) $item['duration_value'] . ' ' . $item['unit'] . ')' .
                              ' — rental subtotal PHP ' . number_format((float) $item['total'], 2) .
                             ' — late fee PHP ' . number_format((float) $item['late_fee'], 2) .
                               ' — line total PHP ' . number_format($lineTotal, 2) . "\n";
                            }
                            $body .= "\nGrand total: PHP " . number_format($grandTotal, 2) . "\n\n" .
                                'The transaction receipt is attached as a PDF.';
                            $tmpPdf = tempnam(sys_get_temp_dir(), 'bm-receipt-');
                            if ($tmpPdf === false) throw new RuntimeException('Could not create receipt file.');
                            file_put_contents($tmpPdf, bikeMoReceiptPdf($orderCode, $customer, $orderItems, $grandTotal, $status));
                            $sent = sendBikeMoEmail(
                                $customer['email'], $customer['name'],
                                'BikeMo order ' . $orderCode . ' status: ' . $status,
                                $body, $tmpPdf, 'BikeMo-' . $orderCode . '-receipt.pdf'
                            );
                            @unlink($tmpPdf);
                            if (!$sent) $error = 'Status saved, but email was not sent. Check Mailtrap SMTP settings.';
                            else $message .= ' Customer email and PDF receipt sent to the Mailtrap inbox.';
                        }
                    } catch (Throwable $mailError) {
                        if (isset($tmpPdf) && is_file($tmpPdf)) @unlink($tmpPdf);
                        error_log('BikeMo status email error: ' . $mailError->getMessage());
                        $error = 'Status saved, but the receipt email failed. Check Mailtrap SMTP settings and server logs.';
                    }
                }
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                error_log($e->getMessage());
                $error = $e instanceof RuntimeException ? $e->getMessage() : 'The order could not be updated. Check that the database migration was applied.';
            }
        }
    }
}

// FETCH RENTALS
$filter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');

$sql = "
    SELECT
        r.id,
        r.code,
        r.start_time,
        r.end_time,
        r.duration_value,
        r.order_code,
        (SELECT SUM(r2.total + r2.late_fee)
        FROM rentals r2
        WHERE r2.order_code = r.order_code) AS order_total,
        r.unit,
        r.total,
        r.late_fee,
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
            OR r.order_code LIKE ?
            OR u.name LIKE ?
            OR u.email LIKE ?
            OR b.name LIKE ?
        )
    ";

    $like = '%' . $search . '%';

    array_push($params, $like, $like, $like, $like, $like);
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
                            <b><?= adminEscape($r['order_code']) ?></b>
                            <small class="bm-admin-cell-sub">Order total ₱<?= number_format((float) $r['order_total'], 2) ?></small>

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
                                <?= (int) $r['duration_value'] ?>
                                <?= adminEscape($r['unit']) ?>
                            </small>
                        </td>

                        <td>
                            <b>
                                ₱<?= number_format((float) $r['total'], 2) ?>
                            </b>

                            <small class="bm-admin-cell-sub">
                        Bike subtotal · <?= adminEscape($r['payment']) ?>
                        </small>

<?php if ((float) $r['late_fee'] > 0): ?>
    <small class="bm-admin-cell-sub">
        Late fee: ₱<?= number_format((float) $r['late_fee'], 2) ?>
    </small>
<?php endif; ?>
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
