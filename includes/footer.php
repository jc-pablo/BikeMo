<?php
/* =========================================================
   includes/footer.php  -  bottom of every public page
   Needs $base (same as header.php).
   ========================================================= */
$base = $base ?? '';
?>
  <footer>
    <div class="container">
      <div class="foot">
        <div>
          <div class="logo">Bike<b>Mo</b></div>
          <p>A web-based bike rental management system that makes renting quick and hassle-free.</p>
        </div>
        <div>
          <b style="color:#fff">Explore</b><br>
          <a href="<?= $base ?>index.php#how" style="color:inherit">How it works</a><br>
          <a href="<?= $base ?>index.php#fleet" style="color:inherit">Fleet</a><br>
          <a href="<?= $base ?>index.php#why" style="color:inherit">Why BikeMo</a>
        </div>
        <div>
          <b style="color:#fff">Account</b><br>
          <a href="<?= $base ?>login.php" style="color:inherit">Customer Login</a><br>
          <a href="<?= $base ?>login.php?role=admin" style="color:inherit">Admin Login</a>
        </div>
      </div>
      <div class="copy">© 2026 BikeMo · Bike Rental Management System</div>
    </div>
  </footer>

  <script src="<?= $base ?>assets/js/main.js"></script>
</body>
</html>
