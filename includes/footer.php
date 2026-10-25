<?php
// Pages in customers/ use ../; index.php uses the project root.
$assetPrefix = $assetPrefix ?? (strpos($_SERVER['SCRIPT_NAME'] ?? '', '/customers/') !== false ? '../' : '');
$isHomePage = basename($_SERVER['SCRIPT_NAME'] ?? '') === 'index.php';
?>

<?php if ($isHomePage): ?>
    <script>window.bikemoRentUrl = <?= json_encode($rentUrl ?? 'login.php?role=user') ?>;</script>
    <script src="assets/js/main.js" defer></script>
<?php endif; ?>

<footer>
    <div class="container">
        <div class="foot">
            <div>
                <div class="logo">Bike<b>Mo</b></div>
                <p>A web-based bike rental management system that makes renting quick and hassle-free.</p>
            </div>
            
            <div>
                <b style="color:#fff">Explore</b><br>
                <a href="<?= $assetPrefix ?? '' ?>index.php#how" style="color:inherit">How it works</a><br>
                <a href="<?= $assetPrefix ?? '' ?>index.php#fleet" style="color:inherit">Fleet</a><br>
                <a href="<?= $assetPrefix ?? '' ?>index.php#why" style="color:inherit">Why BikeMo</a>
            </div>
            
            <div>
                <b style="color:#fff">Account</b><br>
                <a style="cursor:pointer" href="<?= $assetPrefix ?? '' ?>login.php?role=user">Customer Login</a><br>
                <a style="cursor:pointer" href="<?= $assetPrefix ?? '' ?>login.php?role=admin">Admin Login</a>
            </div>
        </div>
        
        <div class="copy">© 2026 BikeMo · Bike Rental Management System</div>
    </div>
</footer>
</body>
</html>