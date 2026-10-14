# BikeMo
- `index.php`: original page markup
- `assets/css/style.css`: original CSS, separated
- `assets/js/main.js`: original JavaScript, separated
- `includes/header.php` / `includes/footer.php`: shared wrapper
- `login.php` / `register.php`: entry points retaining the same front-end
- `admin/details.php`: routes to the original app
- `includes/db.php` / `includes/functions.php`: PHP helpers
- `db/bikemo.sql`: database schema reference

Run inside a PHP server web root (e.g. `htdocs/BikeMo`) and open `/BikeMo/`.
Important: the supplied app uses browser `localStorage`; the SQL/PDO files are not connected so existing behavior and interface stay unchanged.
