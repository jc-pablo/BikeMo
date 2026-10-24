# BikeMo — setup and defense guide

This version keeps the navy/orange BikeMo interface, uses the real MySQL `bikemo` database, and **does not use `bike_photos`**. It is designed for your XAMPP / PHP 8.3 project and the schema shown in your screenshots.

## 1. Install without deleting your existing data

1. In phpMyAdmin, select `bikemo` → **Export** → **Quick / SQL** → Go. Keep the downloaded backup.
2. On Windows, make a full copy of `C:\xampp\htdocs\BikeMo` before replacing files.
3. Extract the ZIP. Copy the extracted `BikeMo` files into `C:\xampp\htdocs\BikeMo` and overwrite the same-named files. Don't remove any of your own photos.
4. In phpMyAdmin, click database **bikemo** → **SQL** and paste the contents of `db/upgrade_images_no_table.sql`. Click **Go**. Run it once; it uses `IF NOT EXISTS` for safer reruns on MariaDB.
5. Start Apache and MySQL. Go to `http://localhost/BikeMo/index.php`.
6. Press Ctrl+F5 for a hard refresh, because your browser may have cached the old `main.js`.
7. Test `admin/bikes.php`, `customers/bikes.php`, `customers/cart.php`, `customers/rentals.php`, and `customers/review.php`.

**Existing `transactions` table:** the program uses **`rentals`** for reservations. You may leave the old `transactions` table untouched. Never delete existing data without checking it first.

**Existing `bike_photos` table:** the PHP program no longer reads or writes it. If it contains photos you want to keep, run `db/migrate_old_photo_paths.sql` AFTER the first upgrade script, then open the Admin Edit Bike page to verify photos are visible. Only when you have confirmed everything and backed up the database may you manually run `DROP TABLE bike_photos;` in phpMyAdmin. If it's empty, you can skip the migration and later drop it after backup. **Do not drop it blindly.**

## 2. Put your own real pictures in assets/images

For photos you find online, choose pictures you have permission to use and avoid watermarks. Rename simply, e.g. `trailblazer-x1.jpg`, `trailblazer-side.jpg` (JPG/PNG/WebP).

1. Copy images into `C:\xampp\htdocs\BikeMo\assets\images\`.
2. Open **Admin → Manage Bikes → Edit** the matching bike.
3. In **Main image path**, type `assets/images/trailblazer-x1.jpg`.
4. For optional multiple pictures, enter one path per line under **Additional photo paths**.
5. Click **Save changes**. The images show on the homepage Featured Bikes, customer Browse Bikes, and the bike's Details & Reviews page.

Or use the **Upload bike photos** control instead; it accepts one or multiple selected images, saves files in `uploads/bikes/`, and puts their paths in the `bikes` row automatically.

### Hero bike

- Without `assets/images/hero-bike.jpg`, the orange/white bicycle SVG is displayed on the landing-page hero (no empty gap).
- To use a real hero photo instead, copy the chosen photo to `assets/images/hero-bike.jpg`. Refresh the homepage.

### Database fields used

| Table | Fields / role |
|---|---|
| `users` | User accounts, password hash in `password`, `role`, `photo`, `is_active` |
| `bikes` | Name, type, rates, status, **`image_path`**, **`gallery_images`** (JSON text) |
| `rentals` | Confirmed reservations, pickup/due, amounts and `Ongoing`/`Completed` status |
| `bike_reviews` | Review rating and comment; one review per user per bike |

`gallery_images` stores a JSON list of photo **file paths**, e.g. `["assets/images/bike-side.jpg","uploads/bikes/abc123.jpg"]`. Actual image files stay in folders. Therefore **no separate bike_photos table is needed**.

## 3. Why the 3 counters now work

The homepage gets these values using SELECT queries:

- **Bikes in the fleet** = `SELECT COUNT(*) FROM bikes`
- **Bike categories** = `SELECT COUNT(DISTINCT type) FROM bikes`
- **Ongoing rentals** = `SELECT COUNT(*) FROM rentals WHERE status='Ongoing'`

These are live server-rendered values, animated only for display. If your database has zero rentals, the third counter truthfully remains **0**. Adding bikes in Admin updates the first counter after refresh.

## 4. Professor's 10 functional requirements

1. Register/login: `register.php`, `login.php`, `logout.php`.
2. Browse available bikes/details: `customers/bikes.php` and `customers/review.php`.
3. Select hours/days/weeks and add to cart: `customers/bikes.php`.
4. View, update, remove items in cart: `customers/cart.php`.
5. Confirm booking: checkout inserts into **rentals** table.
6. Automatic pricing: `rentalPrice()` on the server; weeks get a 15% discount.
7. Admin create/read/update/delete bikes: `admin/bikes.php`.
8. Admin set available/rented/maintenance: `admin/bikes.php`; ongoing rentals block making it available prematurely.
9. Admin booking activity and status: `admin/rentals.php`.
10. Customer ongoing/completed rental history: `customers/rentals.php`.

## 5. MP1–MP5 review checklist

- **MP1 product CRUD and photo uploads:** `admin/bikes.php`; one image or multiple; images shown on card/details pages.
- **MP2 user management:** registration photo (`register.php`), edit your photo (`customers/profile.php`), deactivate/change role (`admin/users.php`).
- **MP3 roles and authentication:** email+hashed password; `includes/auth.php` blocks access to customer/admin pages and redirects unsigned-in users to login.
- **MP4 reviews:** `customers/review.php` requires prior rental, shows reviews, supports editing/upserting, and masks a short list of foul words with PHP regex.
- **MP5 PHP form validation:** login and bike CRUD validate data in PHP; these forms have `novalidate` so HTML5 validation isn't the grading implementation.

## 6. Defense questions you should be able to answer

**Where are bikes saved?** In `bikes` in MariaDB. PHP fetches rows via PDO.

**What is CRUD?** Create `INSERT`, Read `SELECT`, Update `UPDATE`, Delete `DELETE`. Bikes with rental history cannot be deleted to protect rental records.

**What happened to bike_photos?** A primary path and a JSON gallery are in the `bikes` table; physical files are in `assets/images` or `uploads/bikes`.

**Why password_hash/password_verify?** Plaintext passwords should not be saved. PHP creates a one-way hash at registration and verifies a login password against it.

**Why are there sessions?** `$_SESSION['user_id']` tells the server who is signed in. The cart is temporarily in the session and becomes database records only at checkout.

**What prevents double-booking?** Checkout locks the bike row in a database transaction and updates its status from `available` to `rented`. If another booking got there first, checkout rolls back.

**How are reviews restricted?** Before saving a review PHP checks whether a matching `rentals` row exists for that customer and bike.

**Why server-side validation?** Browser controls can be bypassed. PHP checks data and permitted enum values before database changes.

## 7. What still needs local testing

Syntax was checked with PHP and Node, but this ZIP cannot connect to your private Windows XAMPP/MySQL database in this environment. **Do the MP1–MP5 operations on your actual machine** and confirm each SQL record. This is not a guarantee of a complete grade, because your professor may use extra cases or data.

Quick smoke test: add a bike + multiple images → see it on homepage and browse → register customer with photo → book bike → admin sees booking → mark Completed → write and edit review → change bike status → test user deactivation. Check phpMyAdmin after each important operation.
