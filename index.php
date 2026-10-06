<?php
// index.php  -  BikeMo home / landing page
$pageTitle = "BikeMo | Bike Rental Made Easy";

// Later you can replace these numbers with real counts from your database, e.g.
//   $result = $conn->query("SELECT COUNT(*) AS total FROM bikes");
//   $bikesCount = $result->fetch_assoc()['total'];
$bikesCount      = 10;
$categoriesCount = 6;
$hoursOpen       = 24;

include "includes/header.php";
?>

<main class="hero">

    <!-- sun + clouds (decoration) -->
    <div class="sun"></div>
    <div class="cloud cloud-1"></div>
    <div class="cloud cloud-2"></div>

    <div class="hero-content">
        <span class="badge">🚴 BIKE RENTAL MADE EASY</span>

        <h1>RIDE THE CITY.<br><span class="orange">OWN THE ROAD.</span></h1>

        <p class="hero-text">
            Browse available bikes, pick your rental time, and confirm your
            reservation online. No more asking around and hoping something is
            free. Just pick, book, and pedal.
        </p>

        <div class="hero-buttons">
            <a href="login.php" class="btn btn-orange">Rent a Bike Now &rarr;</a>
            <a href="#fleet"    class="btn btn-outline">See the Fleet</a>
        </div>

        <div class="stats">
            <div class="stat">
                <strong><?php echo $bikesCount; ?></strong>
                <span>Bikes in the fleet</span>
            </div>
            <div class="stat">
                <strong><?php echo $categoriesCount; ?></strong>
                <span>Bike categories</span>
            </div>
            <div class="stat">
                <strong><?php echo $hoursOpen; ?></strong>
                <span>Hours open (online)</span>
            </div>
        </div>
    </div>

    <!-- The bike with spinning wheels -->
    <div class="bike-scene">

        <!-- mountains -->
        <svg class="mountains" viewBox="0 0 1200 200" preserveAspectRatio="none" aria-hidden="true">
            <polygon points="0,200 0,120 150,40 300,130 420,80 560,150 700,60 860,150 1000,90 1200,160 1200,200" fill="#16314d"/>
            <polygon points="0,200 0,170 200,110 380,175 560,130 760,185 980,125 1200,180 1200,200" fill="#0f2740"/>
        </svg>

        <!-- bike -->
        <svg class="bike" viewBox="0 0 520 300" role="img" aria-label="Bike with spinning wheels">

            <!-- REAR WHEEL (center 130,190) -->
            <g class="wheel">
                <circle cx="130" cy="190" r="88" fill="#0b1a2b" stroke="#0b1a2b" stroke-width="10"/>
                <circle cx="130" cy="190" r="78" fill="none" stroke="#e8edf3" stroke-width="2.5"/>
                <?php for ($i = 0; $i < 12; $i++):
                    $a = deg2rad($i * 30);
                    $x = 130 + 78 * cos($a);
                    $y = 190 + 78 * sin($a); ?>
                    <line x1="130" y1="190" x2="<?php echo round($x, 1); ?>" y2="<?php echo round($y, 1); ?>" stroke="#cfd8e3" stroke-width="1.6"/>
                <?php endfor; ?>
            </g>

            <!-- FRONT WHEEL (center 390,190) -->
            <g class="wheel">
                <circle cx="390" cy="190" r="88" fill="#0b1a2b" stroke="#0b1a2b" stroke-width="10"/>
                <circle cx="390" cy="190" r="78" fill="none" stroke="#e8edf3" stroke-width="2.5"/>
                <?php for ($i = 0; $i < 12; $i++):
                    $a = deg2rad($i * 30);
                    $x = 390 + 78 * cos($a);
                    $y = 190 + 78 * sin($a); ?>
                    <line x1="390" y1="190" x2="<?php echo round($x, 1); ?>" y2="<?php echo round($y, 1); ?>" stroke="#cfd8e3" stroke-width="1.6"/>
                <?php endfor; ?>
            </g>

            <!-- FRAME (stays still) -->
            <g stroke="#ff6b1a" stroke-width="9" stroke-linecap="round" stroke-linejoin="round" fill="none">
                <polyline points="130,190 235,195 205,100 130,190"/>   <!-- rear triangle + seat tube -->
                <polyline points="205,100 350,95 235,195"/>            <!-- top tube + down tube -->
                <line x1="350" y1="95" x2="390" y2="190"/>             <!-- front fork -->
            </g>

            <!-- seat -->
            <line x1="205" y1="100" x2="200" y2="78" stroke="#0b1a2b" stroke-width="7" stroke-linecap="round"/>
            <rect x="175" y="66" width="55" height="12" rx="6" fill="#0b1a2b"/>

            <!-- handlebar -->
            <polyline points="350,95 345,68 372,66" fill="none" stroke="#0b1a2b" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>

            <!-- pedal crank -->
            <circle cx="235" cy="195" r="16" fill="#0b1a2b" stroke="#ff6b1a" stroke-width="4"/>

            <!-- hubs -->
            <circle cx="130" cy="190" r="6" fill="#ff6b1a"/>
            <circle cx="390" cy="190" r="6" fill="#ff6b1a"/>
        </svg>
    </div>
</main>

<!-- Fleet section: you will list bikes from the database here later -->
<section id="fleet" class="fleet">
    <h2>OUR FLEET</h2>
    <p>Available bikes will show up here once the bikes table is connected.</p>
</section>

<?php include "includes/footer.php"; ?>