
<?php

// Start the session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Load database connection
require_once __DIR__ . '/db.php';

// Check if the user has permission
function requireRole(string $role): void
{
    global $pdo;

    // Check if the user is logged in
    if (empty($_SESSION['user_id'])) {
        header('Location: /BikeMo/login.php?message=login_required');
        exit;
    }

    // Check the user's account role
    if (($_SESSION['role'] ?? '') !== $role) {
        http_response_code(403);
        exit('Access denied.');
    }

    // Check account status in the database
    $stmt = $pdo->prepare(
        'SELECT role, is_active
         FROM users
         WHERE id = ?'
    );

    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Block inactive or unauthorized accounts
    if (
        !$user ||
        !$user['is_active'] ||
        $user['role'] !== $role
    ) {
        $_SESSION = [];
        session_destroy();

        header('Location: /BikeMo/login.php?message=access_changed');
        exit;
    }
}
