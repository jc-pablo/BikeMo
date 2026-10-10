<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'BikeMo | Bike Rental Management System';
$base      = '';

$featured  = get_featured_bikes($conn, 4);   // 4 available bikes
$bikeCount = count_bikes($conn);             // number in the hero stats

include __DIR__ . '/includes/header.php';
?>

<!-- ============ NAVBAR ============ -->
<nav class="nav" id="nav">
  <div class="container">
    <a class="logo" href="#top" style="text-decoration:none">
      <!-- this small wheel logo also spins (CSS: .logo svg) -->
      <svg viewBox="0 0 40 40"><circle cx="20" cy="20" r="16" fill="none" stroke="#ff6a1a" stroke-width="4"/><g stroke="#c6ff3d" stroke-width="2"><line x1="20" y1="6" x2="20" y2="34"/><line x1="6" y1="20" x2="34" y2="20"/><line x1="10" y1="10" x2="30" y2="30"/><line x1="30" y1="10" x2="10" y2="30"/></g><circle cx="20" cy="20" r="3" fill="#fff"/></svg>
      Bike<b>Mo</b>
    </a>
    <div class="nav-links">
      <a href="#how">How It Works</a><a href="#fleet">Our Bikes</a><a href="#types">Categories</a><a href="#why">Why BikeMo</a>
    </div>
    <div class="nav-cta">
      <?php if (is_logged_in()): ?>
        <a class="btn btn-lime btn-sm" style="text-decoration:none"
           href="<?= $_SESSION['user']['role'] === 'admin' ? 'admin/index.php' : 'bikes.php' ?>">
          <?= $_SESSION['user']['role'] === 'admin' ? 'Admin Panel' : 'My Dashboard' ?> →
        </a>
      <?php else: ?>
        <a class="btn btn-ghost btn-sm" style="text-decoration:none" href="login.php?role=admin">Admin</a>
        <a class="btn btn-ghost btn-sm" style="text-decoration:none" href="login.php">Log In</a>
        <a class="btn btn-primary btn-sm" style="text-decoration:none" href="register.php">Sign Up</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- ============ HERO ============ -->
<header class="hero" id="top">
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
        <a class="btn btn-primary" style="text-decoration:none" href="<?= is_logged_in() ? 'bikes.php' : 'login.php' ?>">Rent a Bike Now →</a>
        <a class="btn btn-ghost" style="text-decoration:none" href="#fleet">See the Fleet</a>
      </div>
      <div class="hero-stats">
        <div><b data-count="<?= $bikeCount ?>">0</b><small>Bikes in the fleet</small></div>
        <div><b data-count="6">0</b><small>Bike categories</small></div>
        <div><b data-count="24">0</b><small>Hours open (online)</small></div>
      </div>
    </div>

    <!-- THE BIKE WITH ROTATING WHEELS: the 3rd value "true" turns spinning on -->
    <div class="hero-bike">
      <?= bike_svg('Mountain', '#ff6a1a', true) ?>
    </div>
  </div>
</header>

<!-- ============ MARQUEE ============ -->
<div class="marquee"><div>
  <?php
  $words = ['MOUNTAIN','ROAD','CITY','ELECTRIC','FOLDING','BMX','RENT','RIDE','REPEAT'];
  for ($i = 0; $i < 4; $i++) {
      foreach ($words as $w) echo "<span>$w</span><span>★</span>";
  }
  ?>
</div></div>

<!-- ============ HOW IT WORKS ============ -->
<section class="section" id="how">
  <div class="container">
    <div class="section-head reveal"><small>SIMPLE PROCESS</small><h2>FOUR STEPS TO YOUR RIDE</h2><p>From sign-up to handlebars in minutes.</p></div>
    <div class="steps">
      <div class="step reveal"><span class="num">01</span><div class="ico">📝</div><h3>Create Account</h3><p>Register once and keep your rentals, history and details in one secure place.</p></div>
      <div class="step reveal"><span class="num">02</span><div class="ico">🔎</div><h3>Pick a Bike</h3><p>Browse bikes with full details, rates and live availability before you decide.</p></div>
      <div class="step reveal"><span class="num">03</span><div class="ico">🛒</div><h3>Set Duration</h3><p>Choose hours, days or weeks. Your total is computed automatically.</p></div>
      <div class="step reveal"><span class="num">04</span><div class="ico">✅</div><h3>Confirm &amp; Ride</h3><p>Check out your cart and get a reservation code. Your bike is held for you.</p></div>
    </div>
  </div>
</section>

<!-- ============ FEATURED BIKES (from the database) ============ -->
<section class="section dark-sec" id="fleet">
  <div class="container">
    <div class="section-head reveal"><small style="color:var(--lime)">THE FLEET</small><h2>FEATURED BIKES</h2><p style="color:#b9c7d3">A peek at what is available right now.</p></div>
    <div class="bike-grid">
      <?php foreach ($featured as $b) echo bike_card($b); ?>
      <?php if (!$featured): ?>
        <p style="text-align:center;opacity:.7;grid-column:1/-1">No bikes available right now.</p>
      <?php endif; ?>
    </div>
    <div style="text-align:center;margin-top:36px">
      <a class="btn btn-primary" style="text-decoration:none" href="<?= is_logged_in() ? 'bikes.php' : 'login.php' ?>">View All Bikes →</a>
    </div>
  </div>
</section>

<!-- ============ CATEGORIES ============ -->
<section class="section" id="types">
  <div class="container">
    <div class="section-head reveal"><small>FIND YOUR STYLE</small><h2>BIKES FOR EVERY RIDER</h2></div>
    <?php $go = is_logged_in() ? 'bikes.php' : 'login.php'; ?>
    <div class="cats">
      <a class="cat reveal" href="<?= $go ?>" style="display:block;color:inherit;text-decoration:none"><div class="e">⛰️</div><h3>MOUNTAIN</h3><p>Trails and rough roads</p></a>
      <a class="cat reveal" href="<?= $go ?>" style="display:block;color:inherit;text-decoration:none"><div class="e">🏁</div><h3>ROAD</h3><p>Fast and lightweight</p></a>
      <a class="cat reveal" href="<?= $go ?>" style="display:block;color:inherit;text-decoration:none"><div class="e">🏙️</div><h3>CITY</h3><p>Comfy daily rides</p></a>
      <a class="cat reveal" href="<?= $go ?>" style="display:block;color:inherit;text-decoration:none"><div class="e">⚡</div><h3>ELECTRIC</h3><p>Pedal-assist power</p></a>
      <a class="cat reveal" href="<?= $go ?>" style="display:block;color:inherit;text-decoration:none"><div class="e">🎒</div><h3>FOLDING</h3><p>Portable and compact</p></a>
      <a class="cat reveal" href="<?= $go ?>" style="display:block;color:inherit;text-decoration:none"><div class="e">🤸</div><h3>BMX</h3><p>Tricks and fun</p></a>
    </div>
  </div>
</section>

<!-- ============ WHY BIKEMO ============ -->
<section class="section" id="why" style="background:#efe8d8">
  <div class="container">
    <div class="section-head reveal"><small>WHY BIKEMO</small><h2>BUILT FOR RENTERS &amp; SHOPS</h2></div>
    <div class="why">
      <div class="reveal"><span class="b">💸</span><span><h4>Automatic Pricing</h4><p>Rates and duration are computed for you. No manual math, no surprises.</p></span></div>
      <div class="reveal"><span class="b">🟢</span><span><h4>Live Availability</h4><p>Only bikes that are actually ready can be reserved. No double-booking.</p></span></div>
      <div class="reveal"><span class="b">📜</span><span><h4>Rental History</h4><p>See ongoing and past rentals, due times and what you paid.</p></span></div>
      <div class="reveal"><span class="b">🛠️</span><span><h4>Admin Panel</h4><p>Manage listings, update bike status and monitor every transaction.</p></span></div>
    </div>
  </div>
</section>

<!-- ============ TESTIMONIALS ============ -->
<section class="section dark-sec">
  <div class="container">
    <div class="section-head reveal"><small style="color:var(--lime)">RIDER LOVE</small><h2>WHAT RIDERS SAY</h2></div>
    <div class="testi">
      <blockquote class="reveal"><p>"Reserved a mountain bike in two minutes. The total was right there before I confirmed. So easy!"</p><cite>Mika R. · Weekend rider</cite></blockquote>
      <blockquote class="reveal"><p>"I love seeing which bikes are really available. No more wasted trips to the shop."</p><cite>Jared T. · Commuter</cite></blockquote>
      <blockquote class="reveal"><p>"The history page keeps track of my returns and payments. Super convenient."</p><cite>Aly S. · Tourist</cite></blockquote>
    </div>
  </div>
</section>

<!-- ============ CALL TO ACTION ============ -->
<section class="cta">
  <h2>READY TO ROLL?</h2>
  <p>Create your free account and book your first ride today.</p>
  <a class="btn btn-dark" style="text-decoration:none" href="register.php">Get Started →</a>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
