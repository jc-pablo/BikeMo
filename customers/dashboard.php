
<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('user');
require_once __DIR__ . '/../includes/db.php';

$name = $_SESSION['name'] ?? 'Customer';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard | BikeMo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header class="topbar">
    <div class="container">
        <a class="logo" href="dashboard.php"
           style="text-decoration:none">
            Bike<b>Mo</b>
        </a>

        <nav class="tabs">
            <a href="dashboard.php">Dashboard</a>
            <a href="bikes.php">Browse Bikes</a>
            <a href="cart.php">My Cart</a>
            <a href="rentals.php">My Rentals</a>
        </nav>

        <div class="user-chip">
            <span>
                <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
            </span>
            <a href="../logout.php">Logout</a>
        </div>
    </div>
</header>

<main class="page">
    <div class="container">

        <div class="section-head">
            <small>CUSTOMER PORTAL</small>
            <h1>WELCOME BACK!</h1>
            <p>Ready for your next ride?</p>
        </div>

        <section class="section">
            <div class="section-head">
                <h2>YOUR NEXT RIDE STARTS HERE</h2>
                <p>Explore available bikes and manage your rentals.</p>
            </div>

            <div class="cats">
                <a class="cat" href="bikes.php"
                   style="text-decoration:none">
                    <div class="e">🚲</div>
                    <h3>BROWSE BIKES</h3>
                    <p>Explore the available bikes and rental rates.</p>
                </a>

                <a class="cat" href="cart.php"
                   style="text-decoration:none">
                    <div class="e">🛒</div>
                    <h3>MY CART</h3>
                    <p>Review your bike selections and checkout.</p>
                </a>

                <a class="cat" href="rentals.php"
                   style="text-decoration:none">
                    <div class="e">📜</div>
                    <h3>MY RENTALS</h3>
                    <p>View your ongoing and completed rentals.</p>
                </a>
            </div>
        </section>

    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>
