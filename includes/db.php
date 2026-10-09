<?php
/* =========================================================
   includes/db.php  -  DATABASE CONNECTION
   Every page that needs the database requires this file
   (functions.php already does it for you).
   XAMPP defaults: user = root, password = empty.
   ========================================================= */

$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';          // XAMPP default is empty
$DB_NAME = 'bikemo';

// Don't crash the whole site if MySQL is off or the database isn't imported yet.
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    $conn = null;   // pages will use sample data instead (see functions.php)
} else {
    $conn->set_charset('utf8mb4');
}
