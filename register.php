<?php
/* register.php - PLACEHOLDER so the landing page buttons work.
   NEXT STEP: move the register form from BikeMo.txt here,
   then INSERT into `users` using password_hash() and a prepared statement. */
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'BikeMo | Sign Up';
$base = '';
include __DIR__ . '/includes/header.php';
?>
<div style="min-height:70vh;display:grid;place-items:center;text-align:center;padding:40px 20px">
  <div>
    <h1 style="font-size:3rem;color:var(--navy)">SIGN UP PAGE</h1>
    <p style="color:var(--muted);margin:10px 0 20px">Coming next. This file is where the register form goes.</p>
    <a class="btn btn-primary" style="text-decoration:none" href="index.php">← Back to home</a>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
