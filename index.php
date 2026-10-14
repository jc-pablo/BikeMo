<?php require __DIR__ . '/includes/header.php'; ?>
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
      <div class="nav-cta" id="landingNav"></div>
    </div>
  </nav>

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
          <div><b data-count="10">0</b><small>Bikes in the fleet</small></div>
          <div><b data-count="6">0</b><small>Bike categories</small></div>
          <div><b data-count="24">0</b><small>Hours open (online)</small></div>
        </div>
      </div>
      <div class="hero-bike" id="heroBike"></div>
    </div>
  </header>

  <div class="marquee"><div id="marq"></div></div>

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

  <footer>
    <div class="container">
      <div class="foot">
        <div><div class="logo">Bike<b>Mo</b></div><p>A web-based bike rental management system that makes renting quick and hassle-free.</p></div>
        <div><b style="color:#fff">Explore</b><br><a href="#how" style="color:inherit">How it works</a><br><a href="#fleet" style="color:inherit">Fleet</a><br><a href="#why" style="color:inherit">Why BikeMo</a></div>
        <div><b style="color:#fff">Account</b><br><a style="cursor:pointer" onclick="openAuth('login','user')">Customer Login</a><br><a style="cursor:pointer" onclick="openAuth('login','admin')">Admin Login</a></div>
      </div>
      <div class="copy">© 2026 BikeMo · Bike Rental Management System</div>
    </div>
  </footer>
</section>

<!-- ============ AUTH ============ -->
<section id="view-auth" class="view hidden">
  <div class="auth-wrap">
    <div class="auth-side">
      <div class="logo" onclick="showView('landing')">Bike<b>Mo</b></div>
      <div>
        <div class="auth-bike" id="authBike"></div>
        <h2>EVERY RIDE <span>STARTS HERE.</span></h2>
        <p>Sign in to rent bikes, manage your cart, and track your rental history. Admins can manage the whole fleet.</p>
      </div>
      <small style="opacity:.6">© 2026 BikeMo</small>
    </div>
    <div class="auth-main">
      <div class="auth-box" id="authBox"></div>
    </div>
  </div>
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
