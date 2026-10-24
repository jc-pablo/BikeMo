<?php
// Landing page: everything shown here comes from the actual MySQL database.
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/bike_images.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$bikeCount = (int) $pdo->query('SELECT COUNT(*) FROM bikes')->fetchColumn();
$typeCount = (int) $pdo->query('SELECT COUNT(DISTINCT type) FROM bikes')->fetchColumn();
$activeRentals = (int) $pdo->query("SELECT COUNT(*) FROM rentals WHERE status = 'Ongoing'")->fetchColumn();
$featuredBikes = $pdo->query("SELECT * FROM bikes WHERE status = 'available' ORDER BY id DESC LIMIT 4")
    ->fetchAll(PDO::FETCH_ASSOC);
$latestReviews = $pdo->query(
    'SELECT r.rating, r.comment, u.name AS reviewer, b.name AS bike_name
     FROM bike_reviews r
     JOIN users u ON u.id = r.user_id
     JOIN bikes b ON b.id = r.bike_id
     ORDER BY r.created_at DESC LIMIT 3'
)->fetchAll(PDO::FETCH_ASSOC);

// Signed-in customers go straight to their bike page.
$rentUrl = !empty($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'user'
    ? 'customers/bikes.php'
    : 'login.php?role=user';
$esc = fn($text) => htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
require __DIR__ . '/includes/header.php';
?>
<!-- ============ LANDING ============ -->
<section id="view-landing" class="view">
  <nav class="nav" id="nav">
    
    <div class="container">
      <div class="logo" onclick="window.scrollTo({top:0,behavior:'smooth'})">
        <svg viewBox="0 0 40 40"><circle cx="20" cy="20" r="16" fill="none" stroke="#ff6a1a" stroke-width="4"/><g stroke="#c6ff3d" stroke-width="2"><line x1="20" y1="6" x2="20" y2="34"/><line x1="6" y1="20" x2="34" y2="20"/><line x1="10" y1="10" x2="30" y2="30"/><line x1="30" y1="10" x2="10" y2="30"/></g><circle cx="20" cy="20" r="3" fill="#fff"/></svg>
        Bike<b>Mo</b>
      </div>
      <div class="nav-links">
        <a href="#how">How It Works</a><a href="#fleet">Our Bikes</a><a href="#types">Categories</a><a href="#why">Why BikeMo</a>
      </div>
      
<div class="nav-cta">
  <a href="login.php?role=admin" class="btn btn-ghost btn-sm">Admin</a>
  <a href="login.php?role=user" class="btn btn-ghost btn-sm">Log In</a>
  <a href="register.php" class="btn btn-primary btn-sm">Sign Up</a>
</div>

    
  </nav>

  
<div class="marquee">
  <div class="marquee-track">
    <span>MOUNTAIN</span><span>★</span>
    <span>ROAD</span><span>★</span>
    <span>CITY</span><span>★</span>
    <span>ELECTRIC</span><span>★</span>
    <span>FOLDING</span><span>★</span>
    <span>BMX</span><span>★</span>
    <span>RENT</span><span>★</span>
    <span>RIDE</span><span>★</span>
    <span>REPEAT</span><span>★</span>
  </div>
</div>


  
<section class="section" id="how">
  <div class="container">
    <div class="section-head">
      <small>SIMPLE PROCESS</small>
      <h2>FOUR STEPS TO YOUR RIDE</h2>
      <p>From sign-up to handlebars in minutes.</p>
    </div>

    <div class="steps">
      <div class="step">
        <span class="num">01</span>
        <div class="ico">📝</div>
        <h3>Create Account</h3>
        <p>Register once and keep your rentals, history and details in one secure place.</p>
      </div>

      <div class="step">
        <span class="num">02</span>
        <div class="ico">🔎</div>
        <h3>Pick a Bike</h3>
        <p>Browse bikes with full details, rates and live availability before you decide.</p>
      </div>

      <div class="step">
        <span class="num">03</span>
        <div class="ico">🛒</div>
        <h3>Set Duration</h3>
        <p>Choose hours, days or weeks. Your total is computed automatically.</p>
      </div>

      <div class="step">
        <span class="num">04</span>
        <div class="ico">✅</div>
        <h3>Confirm &amp; Ride</h3>
        <p>Check out your cart and get a reservation code. Your bike is held for you.</p>
      </div>
    </div>
  </div>
</section>


  <section class="section dark-sec" id="fleet">
    <div class="container">
      <div class="section-head reveal"><small style="color:var(--lime)">THE FLEET</small><h2>FEATURED BIKES</h2><p style="color:#b9c7d3">A peek at what is available right now.</p></div>
      <div class="bike-grid" id="featured">
        <?php foreach ($featuredBikes as $bike): ?>
          <?php $images = bikeImages($bike); ?>
          <article class="bike-card featured-real-bike">
            <div class="bike-art">
              <?php if ($images && bikePhotoExists($images[0])): ?>
                <img src="<?= $esc($images[0]) ?>" alt="<?= $esc($bike['name']) ?>" loading="lazy">
              <?php else: ?>
                <div class="bike-art-fallback">🚲</div>
              <?php endif; ?>
              <span class="type"><?= $esc($bike['type']) ?></span>
              <span class="badge available">Available</span>
            </div>
            <div class="bike-body">
              <h3><?= $esc($bike['name']) ?></h3>
              <p><?= $esc($bike['description'] ?: 'Ready for your next ride.') ?></p>
              <div class="price"><b>₱<?= number_format((float) $bike['rate_per_hour'], 2) ?></b><small>/ hour</small></div>
              <a class="btn btn-primary btn-sm" href="<?= $esc($rentUrl) ?>">Rent this bike →</a>
            </div>
          </article>
        <?php endforeach; ?>
        <?php if (!$featuredBikes): ?>
          <div class="fleet-empty">No available bikes yet. Check back soon!</div>
        <?php endif; ?>
      </div>
      <div style="text-align:center;margin-top:36px"><a class="btn btn-primary" href="<?= $esc($rentUrl) ?>">View All Bikes →</a></div>
    </div>
  </section>

  <section class="section" id="types">
    <div class="container">
      <div class="section-head reveal"><small>FIND YOUR STYLE</small><h2>BIKES FOR EVERY RIDER</h2></div>
      <div class="cats">
        <div class="cat reveal" onclick="startRenting()"><div class="e">⛰️</div><h3>MOUNTAIN</h3><p>Trails and rough roads</p></div>
        <div class="cat reveal" onclick="startRenting()"><div class="e">🏁</div><h3>ROAD</h3><p>Fast and lightweight</p></div>
        <div class="cat reveal" onclick="startRenting()"><div class="e">🏙️</div><h3>CITY</h3><p>Comfy daily rides</p></div>
        <div class="cat reveal" onclick="startRenting()"><div class="e">⚡</div><h3>ELECTRIC</h3><p>Pedal-assist power</p></div>
        <div class="cat reveal" onclick="startRenting()"><div class="e">🎒</div><h3>FOLDING</h3><p>Portable and compact</p></div>
        <div class="cat reveal" onclick="startRenting()"><div class="e">🤸</div><h3>BMX</h3><p>Tricks and fun</p></div>
      </div>
    </div>
  </section>

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

  <section class="section dark-sec">
    <div class="container">
      <div class="section-head reveal">
        <small style="color:var(--lime)">REAL RIDER REVIEWS</small>
        <h2>WHAT OUR RIDERS SAY</h2>
        <p>Feedback comes from customers who have rented a bike.</p>
      </div>
      <div class="testi">
        <?php foreach ($latestReviews as $review): ?>
          <blockquote class="reveal">
            <p><?= $esc($review['comment']) ?></p>
            <cite><?= $esc($review['reviewer']) ?> · <?= $esc($review['bike_name']) ?> · <?= (int) $review['rating'] ?>/5 ★</cite>
          </blockquote>
        <?php endforeach; ?>
        <?php if (!$latestReviews): ?>
          <div class="bm-no-reviews">Reviews from verified renters will appear here once submitted.</div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="cta">
    <h2>READY TO ROLL?</h2><p>Create your free account and book your first ride today.</p>
    <a class="btn btn-dark" href="<?= $esc($rentUrl) ?>">Get Started →</a>
  </section>


</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
