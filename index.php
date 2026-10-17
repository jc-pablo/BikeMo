<?php require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/db.php';
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
      <div class="bike-grid" id="featured"></div>
      <div style="text-align:center;margin-top:36px"><button class="btn btn-primary" onclick="startRenting()">View All Bikes →</button></div>
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
      <div class="section-head reveal"><small style="color:var(--lime)">RIDER LOVE</small><h2>WHAT RIDERS SAY</h2></div>
      <div class="testi">
        <blockquote class="reveal"><p>"Reserved a mountain bike in two minutes. The total was right there before I confirmed. So easy!"</p><cite>Mika R. · Weekend rider</cite></blockquote>
        <blockquote class="reveal"><p>"I love seeing which bikes are really available. No more wasted trips to the shop."</p><cite>Jared T. · Commuter</cite></blockquote>
        <blockquote class="reveal"><p>"The history page keeps track of my returns and payments. Super convenient."</p><cite>Aly S. · Tourist</cite></blockquote>
      </div>
    </div>
  </section>

  <section class="cta">
    <h2>READY TO ROLL?</h2><p>Create your free account and book your first ride today.</p>
    <button class="btn btn-dark" onclick="startRenting()">Get Started →</button>
  </section>


</section>

<!-- ============ CUSTOMER APP ============ -->
<section id="view-user" class="view hidden">
  <div class="topbar">
    <div class="container">
      <div class="logo" onclick="showView('landing')">Bike<b>Mo</b></div>
      <div class="tabs" id="userTabs"></div>
      <div class="user-chip"><span id="userName"></span><div class="avatar" id="userAv"></div><button class="btn btn-ghost btn-sm" onclick="logout()">Logout</button></div>
    </div>
  </div>
  <div class="page"><div class="container" id="userContent"></div></div>
</section>

<!-- ============ ADMIN APP ============ -->
<section id="view-admin" class="view hidden">
  <div class="admin-wrap">
    <aside class="sidebar">
      <div class="logo" onclick="showView('landing')">Bike<b>Mo</b></div>
      <nav class="side-nav" id="adminNav"></nav>
      <div style="font-size:.8rem;color:#9fb0bf;padding:10px 6px"><span id="adminName"></span><br><button class="btn btn-ghost btn-sm" style="margin-top:8px" onclick="logout()">Logout</button></div>
    </aside>
    <main class="admin-main" id="adminContent"></main>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
