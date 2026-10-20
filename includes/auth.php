
<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function requireRole(string $role): void
{
    if (
        !isset($_SESSION['user_id'], $_SESSION['role']) ||
        $_SESSION['role'] !== $role
    ) {
        header('Location: /BikeMo/login.php');
        exit;
    }
}
