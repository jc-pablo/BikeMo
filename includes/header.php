<?php
// includes/header.php
// Shared top part of every page (logo + navbar).
// Each page sets $pageTitle BEFORE including this file.
if (!isset($pageTitle)) { $pageTitle = "BikeMo"; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Our styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="navbar">
    <a href="index.php" class="logo">
        <!-- small spinning wheel icon -->
        <svg class="logo-wheel" viewBox="0 0 40 40" width="38" height="38" aria-hidden="true">
            <circle cx="20" cy="20" r="17" fill="none" stroke="#ff6b1a" stroke-width="3"/>
            <g stroke="#b6ff3b" stroke-width="1.8" stroke-linecap="round">
                <line x1="20" y1="4"  x2="20" y2="36"/>
                <line x1="4"  y1="20" x2="36" y2="20"/>
                <line x1="8.7" y1="8.7"  x2="31.3" y2="31.3"/>
                <line x1="31.3" y1="8.7" x2="8.7"  y2="31.3"/>
            </g>
        </svg>
        <span class="logo-text">BIKE <span class="orange">MO</span></span>
    </a>

    <nav class="nav-links">
        <a href="admin/index.php" class="btn btn-outline btn-sm">Admin</a>
        <a href="login.php"       class="btn btn-outline btn-sm">Log In</a>
        <a href="register.php"    class="btn btn-orange btn-sm">Sign Up</a>
    </nav>
</header>