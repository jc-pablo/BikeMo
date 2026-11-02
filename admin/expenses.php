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

$adminPage = 'expenses';
$adminTitle = 'Monthly Expenses';
$month = (string) ($_GET['month'] ?? date('Y-m'));
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
    $month = date('Y-m');
}
$expenseMonth = $month . '-01';
$message = $_SESSION['expenses_message'] ?? '';
unset($_SESSION['expenses_message']);
$error = '';
$editExpense = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $postedMonth = (string) ($_POST['month'] ?? date('Y-m'));
    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $postedMonth)) {
        $postedMonth = date('Y-m');
    }

    if (!is_string($token) || !hash_equals($_SESSION['csrf_admin'], $token)) {
        $error = 'Security check failed. Refresh the page and try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        $id = (int) ($_POST['id'] ?? 0);
        try {
            if ($action === 'delete' && $id > 0) {
                $delete = $pdo->prepare('DELETE FROM expenses WHERE id = ?');
                $delete->execute([$id]);
                $_SESSION['expenses_message'] = 'Expense deleted.';
                header('Location: expenses.php?month=' . urlencode($postedMonth));
                exit;
            }

            $description = trim((string) ($_POST['description'] ?? ''));
            $category = trim((string) ($_POST['category'] ?? 'Other'));
            $amountText = trim((string) ($_POST['amount'] ?? ''));
            $notes = trim((string) ($_POST['notes'] ?? ''));
            $allowedCategories = ['Maintenance', 'Repair', 'Supplies', 'Utilities', 'Other'];

            if (!in_array($action, ['add', 'update'], true)) {
                $error = 'Choose a valid expense action.';
            } elseif (mb_strlen($description) < 2 || mb_strlen($description) > 160) {
                $error = 'Description must be between 2 and 160 characters.';
            } elseif (!in_array($category, $allowedCategories, true)) {
                $error = 'Choose a valid category.';
            } elseif (!is_numeric($amountText) || (float) $amountText <= 0 || (float) $amountText > 99999999.99) {
                $error = 'Enter an amount greater than zero.';
            } elseif (mb_strlen($notes) > 500) {
                $error = 'Notes cannot exceed 500 characters.';
            } else {
                $amount = number_format((float) $amountText, 2, '.', '');
                if ($action === 'update' && $id > 0) {
                    $save = $pdo->prepare('UPDATE expenses SET expense_month = ?, description = ?, category = ?, amount = ?, notes = ? WHERE id = ?');
                    $save->execute([$postedMonth . '-01', $description, $category, $amount, $notes !== '' ? $notes : null, $id]);
                    $_SESSION['expenses_message'] = 'Expense updated.';
                } else {
                    $save = $pdo->prepare('INSERT INTO expenses (expense_month, description, category, amount, notes) VALUES (?, ?, ?, ?, ?)');
                    $save->execute([$postedMonth . '-01', $description, $category, $amount, $notes !== '' ? $notes : null]);
                    $_SESSION['expenses_message'] = 'Expense added.';
                }
                header('Location: expenses.php?month=' . urlencode($postedMonth));
                exit;
            }
            $month = $postedMonth;
            $expenseMonth = $month . '-01';
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'Could not save this expense. Confirm that includes/expenses.sql has been imported.';
        }
    }
}

if (isset($_GET['edit']) && ctype_digit((string) $_GET['edit'])) {
    $find = $pdo->prepare('SELECT id, expense_month, description, category, amount, notes FROM expenses WHERE id = ?');
    $find->execute([(int) $_GET['edit']]);
    $editExpense = $find->fetch(PDO::FETCH_ASSOC) ?: null;
    if ($editExpense) {
        $month = date('Y-m', strtotime($editExpense['expense_month']));
        $expenseMonth = $month . '-01';
    }
}

$list = $pdo->prepare('SELECT id, description, category, amount, notes FROM expenses WHERE expense_month = ? ORDER BY id DESC');
$list->execute([$expenseMonth]);
$expenses = $list->fetchAll(PDO::FETCH_ASSOC);
$sum = $pdo->prepare('SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE expense_month = ?');
$sum->execute([$expenseMonth]);
$monthlyTotal = (float) $sum->fetchColumn();
$e = fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

require __DIR__ . '/_header.php';
?>

<?php if ($message): ?><div class="bm-admin-alert success"><?= adminEscape($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="bm-admin-alert error"><?= adminEscape($error) ?></div><?php endif; ?>

<section class="bm-admin-welcome">
    <div>
        <span class="bm-admin-pill">MONTHLY TRACKING</span>
        <h2>Keep monthly costs in one place.</h2>
        <p>Add the business expenses for a month and see the total at a glance.</p>
    </div>
    <form method="get" class="bm-expense-month-filter">
        <label for="expense-month">Select month</label>
        <input id="expense-month" name="month" type="month" value="<?= $e($month) ?>">
        <button class="btn btn-primary" type="submit">View month</button>
    </form>
</section>

<div class="bm-admin-stat-grid">
    <article class="bm-admin-stat">
        <div class="bm-admin-stat-icon orange">₱</div>
        <span>Total expenses</span>
        <strong>₱<?= number_format($monthlyTotal, 2) ?></strong>
        <small><?= $e(date('F Y', strtotime($expenseMonth))) ?></small>
    </article>
    <article class="bm-admin-stat">
        <div class="bm-admin-stat-icon blue">▤</div>
        <span>Expense entries</span>
        <strong><?= count($expenses) ?></strong>
        <small>Recorded this month</small>
    </article>
</div>

<div class="bm-admin-dashboard-grid">
    <section class="bm-admin-panel">
        <div class="bm-admin-panel-head"><div><h3><?= $editExpense ? 'Edit expense' : 'Add an expense' ?></h3><p>Record one cost for <?= $e(date('F Y', strtotime($expenseMonth))) ?>.</p></div></div>
        <form method="post" class="bm-admin-form bm-expense-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $e($_SESSION['csrf_admin']) ?>">
            <input type="hidden" name="action" value="<?= $editExpense ? 'update' : 'add' ?>">
            <?php if ($editExpense): ?><input type="hidden" name="id" value="<?= (int) $editExpense['id'] ?>"><?php endif; ?>
            <input type="hidden" name="month" value="<?= $e($month) ?>">
            <div class="field"><label for="expense-description">Description</label><input id="expense-description" name="description" maxlength="160" value="<?= $e($editExpense['description'] ?? '') ?>" required></div>
            <div class="field"><label for="expense-category">Category</label><select id="expense-category" name="category">
                <?php foreach (['Maintenance', 'Repair', 'Supplies', 'Utilities', 'Other'] as $category): ?>
                    <option value="<?= $e($category) ?>" <?= ($editExpense['category'] ?? 'Maintenance') === $category ? 'selected' : '' ?>><?= $e($category) ?></option>
                <?php endforeach; ?>
            </select></div>
            <div class="field"><label for="expense-amount">Amount (PHP)</label><input id="expense-amount" name="amount" type="number" min="0.01" step="0.01" value="<?= $e($editExpense['amount'] ?? '') ?>" required></div>
            <div class="field"><label for="expense-notes">Notes (optional)</label><textarea id="expense-notes" name="notes" maxlength="500" rows="3"><?= $e($editExpense['notes'] ?? '') ?></textarea></div>
            <button class="btn btn-primary" type="submit"><?= $editExpense ? 'Save changes' : 'Add expense' ?></button>
            <?php if ($editExpense): ?> <a href="expenses.php?month=<?= $e($month) ?>" class="bm-expense-cancel">Cancel</a><?php endif; ?>
        </form>
    </section>

    <section class="bm-admin-panel">
        <div class="bm-admin-panel-head"><div><h3>Expenses for <?= $e(date('F Y', strtotime($expenseMonth))) ?></h3><p>Entries are grouped by the month you select.</p></div></div>
        <?php if (!$expenses): ?>
            <div class="bm-admin-empty"><span>₱</span><b>No expenses recorded</b><p>Add an expense using the form.</p></div>
        <?php else: ?>
            <div class="tbl-wrap"><table>
                <thead><tr><th>Description</th><th>Category</th><th>Amount</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><b><?= $e($expense['description']) ?></b><?php if ($expense['notes']): ?><small class="bm-admin-cell-sub"><?= $e($expense['notes']) ?></small><?php endif; ?></td>
                        <td><?= $e($expense['category']) ?></td>
                        <td><b>₱<?= number_format((float) $expense['amount'], 2) ?></b></td>
                        <td class="bm-expense-actions">
                            <a href="expenses.php?month=<?= $e($month) ?>&amp;edit=<?= (int) $expense['id'] ?>">Edit</a>
                            <form method="post" onsubmit="return confirm('Delete this expense?');">
                                <input type="hidden" name="csrf_token" value="<?= $e($_SESSION['csrf_admin']) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $expense['id'] ?>">
                                <input type="hidden" name="month" value="<?= $e($month) ?>">
                                <button type="submit" class="bm-expense-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
            <div class="bm-expense-total"><span>Monthly total</span><strong>₱<?= number_format($monthlyTotal, 2) ?></strong></div>
        <?php endif; ?>
    </section>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
