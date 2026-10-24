<?php // Shared page header; original interface preserved. ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BikeMo | Bike Rental Management System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div id="toasts"></div>
<div id="modalRoot"></div>


  <header class="hero">
    <div class="sun"></div>
    <div class="cloud c1"></div><div class="cloud c2"></div><div class="cloud c3"></div>
    <svg class="mountains" viewBox="0 0 1440 260" preserveAspectRatio="none"><polygon points="0,260 0,150 160,60 320,160 520,40 740,170 920,70 1130,175 1300,90 1440,150 1440,260" fill="#0f2940"/><polygon points="0,260 0,200 200,130 380,210 600,120 820,215 1040,140 1240,210 1440,160 1440,260" fill="#0a1d2f"/></svg>
    <div class="road"></div>
    <div class="container hero-grid">
      <div>
        <span class="tag">🚴 BIKE RENTAL MADE EASY</span>
        <h1>RIDE THE CITY. <span>OWN THE ROAD.</span></h1>
        <p>Browse available bikes, pick your rental time, and confirm your reservation online. No more asking around and hoping something is free. Just pick, book, and pedal.</p>
        <div class="hero-btns">
          <button class="btn btn-primary" onclick="startRenting()">Rent a Bike Now →</button>
          <a class="btn btn-ghost" href="#fleet" style="text-decoration:none">See the Fleet</a>
        </div>
        <div class="hero-stats">
          <div><b data-count="<?= $bikeCount ?>">0</b><small>Bikes in the fleet</small></div>
          <div><b data-count="<?= $typeCount ?>">0</b><small>Bike categories</small></div>
          <div><b data-count="<?= $activeRentals ?>">0</b><small>Ongoing rentals</small></div>
        </div>
      </div>
      <div class="hero-bike" id="heroBike">
        <?php if (is_file(__DIR__ . '/../assets/images/hero-bike.jpg')): ?>
          <img src="assets/images/hero-bike.jpg" alt="Featured bicycle">
        <?php endif; ?>
      </div>
    </div>
  </header>