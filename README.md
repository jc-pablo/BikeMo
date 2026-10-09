# BikeMo - Bike Rental Management System

PHP + MySQL (XAMPP). Put this folder in `C:\xampp\htdocs\bikemo`, then open `http://localhost/bikemo`.

## Setup
1. Start **Apache** and **MySQL** in XAMPP.
2. phpMyAdmin -> new database `bikemo` -> **Import** `database/bikemo.sql`.
3. Open http://localhost/bikemo

## Where everything goes

| Old BikeMo.txt part | New file |
|---|---|
| `<style>...</style>` | `assets/css/style.css` |
| `<head>` + fonts | `includes/header.php` |
| Footer | `includes/footer.php` |
| Landing (`view-landing`) | `index.php` |
| Scroll nav / reveal / count-up JS | `assets/js/main.js` |
| `bikeSVG()` (rotating wheels) | `includes/functions.php` -> `bike_svg()` |
| `esc()`, `money()`, `tint()` | `includes/functions.php` |
| `bikeCard()` | `includes/functions.php` -> `bike_card()` |
| `getUsers/getBikes/getTx` (localStorage) | MySQL tables `users`, `bikes`, `rentals` + `includes/db.php` |
| Auth (`view-auth`) | `login.php`, `register.php`, `logout.php` |
| Browse + details + rent modal | `bikes.php`, `bike_details.php` |
| Cart (`renderCart`) | `cart.php`, `cart_update.php` (uses `$_SESSION`) |
| Checkout (`doCheckout`) | `checkout.php` |
| My Rentals (`renderRentals`) | `history.php` |
| Admin dashboard | `admin/index.php` |
| Admin manage bikes | `admin/bikes.php` |
| Admin transactions | `admin/transactions.php` |
| Admin customers | `admin/customers.php` |

## Folder structure
```
bikemo/
├── index.php            landing page
├── login.php            (placeholder)
├── register.php         (placeholder)
├── includes/            db.php, functions.php, header.php, footer.php
├── assets/              css/style.css, js/main.js, images/
├── admin/               admin pages (later)
├── database/bikemo.sql  tables + sample data
└── .gitignore
```

Demo logins (after importing the SQL): `admin@bikemo.com / admin123`, `user@bikemo.com / user123`
