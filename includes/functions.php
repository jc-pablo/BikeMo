<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

/* ---------- small helpers ---------- */

// Safe output (stops people injecting HTML/JS).  JS version: esc()
function esc($s): string {
    return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8');
}

// 1500 -> ₱1,500   |   52.5 -> ₱52.50.   JS version: money()
function money($n): string {
    $n = (float)$n;
    return '₱' . (floor($n) == $n ? number_format($n) : number_format($n, 2));
}

// Soft background colour behind a bike picture.   JS version: tint()
function tint(string $c): string {
    return "linear-gradient(135deg, {$c}1f, {$c}55)";
}

function is_logged_in(): bool {
    return !empty($_SESSION['user']);
}

/* ---------- bike data ---------- */
function sample_bikes(): array {
    $B = fn($id, $name, $type, $color, $h, $d, $gears, $frame, $weight, $desc, $status = 'available') => [
        'id' => $id, 'name' => $name, 'type' => $type, 'color' => $color,
        'rate_per_hour' => $h, 'rate_per_day' => $d, 'gears' => $gears,
        'frame' => $frame, 'weight' => $weight, 'description' => $desc, 'status' => $status,
    ];
    return [
        $B(1,  'Trailblazer X1',  'Mountain', '#ff6a1a', 80, 450, '21-speed', 'Aluminum', '14.5 kg', 'Rugged suspension bike built for trails, hills and rough roads.'),
        $B(2,  'Roader',    'Road',     '#e63946', 90, 500, '18-speed', 'Carbon',   '9.8 kg',  'Ultra-light road bike for speed lovers and long smooth rides.'),
        $B(3,  'Urban Cruiser',   'City',     '#12b5a6', 60, 350, '7-speed',  'Steel',    '13 kg',   'Comfortable upright bike for everyday city rides and errands.'),
        $B(4,  'Volt E-Rider',    'Electric', '#7b2ff7', 150, 900, 'Pedal-assist', 'Aluminum', '21 kg', 'Electric bike with up to 60 km range. Effortless on hills.'),
        $B(5,  'Pocket Fold',     'Folding',  '#ffb703', 55, 320, '6-speed',  'Aluminum', '11 kg',   'Folds in seconds. Perfect for commuters and small spaces.'),
        $B(6,  'Dirt Kid BMX',    'BMX',      '#06d6a0', 50, 300, 'Single-speed', 'Chromoly', '10 kg', 'Tough and playful BMX for tricks, parks and pump tracks.'),
        $B(7,  'Ridge Runner 29', 'Mountain', '#3a86ff', 85, 480, '24-speed', 'Aluminum', '14 kg',   'Big 29-inch wheels roll over anything in your path.'),
        $B(8,  'Coastal Breeze',  'City',     '#f72585', 65, 380, '3-speed',  'Steel',    '14 kg',   'Retro-style beach cruiser made for relaxed seaside rides.'),
        $B(9,  'Thunder E-Cargo', 'Electric', '#1d3557', 140, 850, 'Pedal-assist', 'Alloy', '23 kg', 'Powerful e-bike with strong torque for heavy-duty rides.', 'maintenance'),
        $B(10, 'Sprint Aero',     'Road',     '#ffd60a', 95, 520, '22-speed', 'Carbon',   '9.2 kg',  'Aerodynamic racer for personal bests and group rides.', 'rented'),
    ];
}

function get_featured_bikes($conn, int $limit = 4): array {
    if ($conn) {
        $stmt = $conn->prepare("SELECT * FROM bikes WHERE status = 'available' ORDER BY id LIMIT ?");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    $avail = array_filter(sample_bikes(), fn($b) => $b['status'] === 'available');
    return array_slice(array_values($avail), 0, $limit);
}

// Number shown under "Bikes in the fleet".
function count_bikes($conn): int {
    if ($conn) {
        $res = $conn->query('SELECT COUNT(*) AS n FROM bikes');
        return (int)$res->fetch_assoc()['n'];
    }
    return count(sample_bikes());
}

/* ---------- the bike picture (SVG) ----------
   JS version: bikeSVG(type, color, spin)
   $spin = true  -> the two wheels ROTATE (CSS classes "wheel spin",
                    animation "spin" lives in assets/css/style.css)
------------------------------------------------ */
function bike_svg(string $type = 'City', string $color = '#ff6a1a', bool $spin = false): string {
    $dark = '#0b1d2e';
    $r = 40; $cy = 118; $tw = 3.5;
    if ($type === 'Mountain') $tw = 5.5;
    if ($type === 'Folding')  { $r = 30; $cy = 128; }
    if ($type === 'BMX')      { $r = 34; $cy = 124; }

    $spinClass = $spin ? ' spin' : '';

    // one wheel = tire + inner rim + 6 spokes + hub
    $wheel = function (int $cx) use ($cy, $r, $tw, $dark, $color, $spinClass): string {
        $top = $cy - $r + 6;
        $bot = $cy + $r + 0 - 6;
        $rim = $r - 6;
        $spokes = '';
        foreach ([0, 30, 60, 90, 120, 150] as $a) {
            $spokes .= "<line x1=\"$cx\" y1=\"$top\" x2=\"$cx\" y2=\"$bot\" stroke=\"#9aa7b3\" stroke-width=\"1\" transform=\"rotate($a $cx $cy)\"/>";
        }
        return "<g class=\"wheel$spinClass\">"
            . "<circle cx=\"$cx\" cy=\"$cy\" r=\"$r\" fill=\"none\" stroke=\"$dark\" stroke-width=\"$tw\"/>"
            . "<circle cx=\"$cx\" cy=\"$cy\" r=\"$rim\" fill=\"none\" stroke=\"#9aa7b3\" stroke-width=\"1\"/>"
            . $spokes
            . "<circle cx=\"$cx\" cy=\"$cy\" r=\"4.5\" fill=\"$color\"/></g>";
    };

    // handlebar shape depends on bike type
    $y46 = $cy - 46; $y44 = $cy - 44; $y40 = $cy - 40; $y54 = $cy - 54;
    if ($type === 'Road') {
        $y60 = $cy - 60; $y62 = $cy - 62;
        $bars = "M205 $y46 L212 $y60 Q228 $y62 230 $y46";
    } elseif ($type === 'BMX') {
        $y72 = $cy - 72;
        $bars = "M205 $y46 L208 $y72 L232 $y72";
    } else {
        $y62 = $cy - 62;
        $bars = "M205 $y46 L210 $y62 L228 $y62";
    }

    $battery = '';
    if ($type === 'Electric') {
        $by = $cy - 25; $bcy = $cy - 20;
        $battery = "<rect x=\"152.5\" y=\"$by\" width=\"40\" height=\"10\" rx=\"3\" fill=\"#c6ff3d\" stroke=\"$dark\" stroke-width=\"1.5\" transform=\"rotate(-31.6 172.5 $bcy)\"/>";
    }
    $hinge = '';
    if ($type === 'Folding') {
        $hy = $cy - 45;
        $hinge = "<circle cx=\"163\" cy=\"$hy\" r=\"5\" fill=\"$dark\"/>";
    }

    $shadowY = $cy + $r + 5;
    $pedalA = $cy + 8; $pedalB = $cy - 8;

    return "<svg viewBox=\"0 0 300 180\" xmlns=\"http://www.w3.org/2000/svg\">"
        . "<ellipse cx=\"152\" cy=\"$shadowY\" rx=\"125\" ry=\"6\" fill=\"rgba(0,0,0,.18)\"/>"
        . $wheel(70) . $wheel(235)
        . "<g stroke=\"$color\" stroke-width=\"6\" stroke-linecap=\"round\" stroke-linejoin=\"round\" fill=\"none\">"
        .   "<line x1=\"70\" y1=\"$cy\" x2=\"140\" y2=\"$cy\"/>"
        .   "<line x1=\"70\" y1=\"$cy\" x2=\"122\" y2=\"$y46\"/>"
        .   "<line x1=\"140\" y1=\"$cy\" x2=\"122\" y2=\"$y46\"/>"
        .   "<line x1=\"122\" y1=\"$y46\" x2=\"205\" y2=\"$y44\"/>"
        .   "<line x1=\"140\" y1=\"$cy\" x2=\"205\" y2=\"$y40\"/>"
        .   "<line x1=\"205\" y1=\"$y40\" x2=\"235\" y2=\"$cy\"/>"
        . "</g>"
        . $battery . $hinge
        . "<g stroke=\"$dark\" stroke-width=\"4\" stroke-linecap=\"round\" fill=\"none\">"
        .   "<line x1=\"122\" y1=\"$y46\" x2=\"122\" y2=\"$y54\"/><line x1=\"109\" y1=\"$y54\" x2=\"136\" y2=\"$y54\" stroke-width=\"6\"/>"
        .   "<path d=\"$bars\"/><line x1=\"205\" y1=\"$y46\" x2=\"205\" y2=\"$y40\"/>"
        .   "<circle cx=\"140\" cy=\"$cy\" r=\"9\" stroke-width=\"3\"/><line x1=\"132\" y1=\"$pedalA\" x2=\"148\" y2=\"$pedalB\" stroke-width=\"3\"/>"
        . "</g></svg>";
}

/* ---------- one bike card (landing version) ----------
   JS version: bikeCard(b, true)
------------------------------------------------------- */
function bike_card(array $b, string $loginUrl = 'login.php'): string {
    $can = $b['status'] === 'available';
    ob_start(); ?>
    <div class="bike-card reveal">
      <div class="bike-art" style="background:<?= tint($b['color']) ?>">
        <span class="type"><?= esc($b['type']) ?></span>
        <span class="badge <?= esc($b['status']) ?>"><?= esc($b['status']) ?></span>
        <?= bike_svg($b['type'], $b['color']) ?>
      </div>
      <div class="bike-body">
        <h3><?= esc($b['name']) ?></h3>
        <div class="specs">
          <span>⚙️ <?= esc($b['gears']) ?></span><span>🔩 <?= esc($b['frame']) ?></span><span>⚖️ <?= esc($b['weight']) ?></span>
        </div>
        <div class="price">
          <div><b><?= money($b['rate_per_hour']) ?></b><small> / hour</small></div>
          <div style="text-align:right"><b style="font-size:1.4rem;color:var(--navy)"><?= money($b['rate_per_day']) ?></b><small> / day</small></div>
        </div>
        <?php if ($can): ?>
          <a class="btn btn-primary btn-sm" style="justify-content:center;text-decoration:none" href="<?= esc($loginUrl) ?>">Rent This Bike</a>
        <?php else: ?>
          <button class="btn btn-primary btn-sm" style="justify-content:center" disabled>Unavailable</button>
        <?php endif; ?>
      </div>
    </div>
    <?php
    return ob_get_clean();
}
