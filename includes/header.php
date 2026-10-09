<?php
/* =========================================================
   includes/header.php  -  top of every page (<head> + <body>)
   Set these BEFORE including it:
     $pageTitle  (string)  e.g. 'BikeMo | Login'
     $base       (string)  '' for pages in the main folder,
                           '../' for pages inside admin/
   ========================================================= */
$pageTitle = $pageTitle ?? 'BikeMo | Bike Rental Management System';
$base      = $base ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($pageTitle) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
</head>
<body>
