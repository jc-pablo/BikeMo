<?php
// Shared top navigation for customer pages.
$customerName = $_SESSION['name'] ?? 'Customer';$customerPage = basename($_SERVER['SCRIPT_NAME']);$customerEscape = fn($text) => htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');

$customerLinks = [
    'dashboard.php' => ['⌂', 'Dashboard'],
    'bikes.php'     => ['🚲', 'Browse Bikes'],
    'cart.php'      => ['🛒', 'My Cart'],
    'rentals.php'   => ['▤', 'My Rentals']
];
?>
<header class="bm-header">
    <div class="container bm-header__inner">
        <a class="bm-brand" href="dashboard.php">
            <span class="bm-brand__icon">🚲</span>
            <span class="bm-brand__text">Bike<b>Mo</b></span>
        </a>