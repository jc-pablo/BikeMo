# BikeMo — Setup and defense guide

1. BACK UP the existing `bikemo` database in phpMyAdmin (Export > SQL).
2. Select the `bikemo` database, open SQL, and run `db/upgrade_mp1_mp5.sql` ONCE. Do not import `db/bikemo.sql` over your live data.
3. Back up `C:\xampp\htdocs\BikeMo`, then copy the `BikeMo` folder into `htdocs`.
4. Verify Apache and MySQL are running. Log in as admin and test `admin/bikes.php`, `admin/users.php`, `admin/rentals.php`.
5. Register a customer with a photo; update their profile; rent a bike; write and edit a review.
6. Check uploaded images in `uploads/bikes` and `uploads/users`.
7. Keep `reset_adpass.php` OUT of the live website; it was removed from this ZIP.

## What each table does
`users`: email, hashed password, role, active flag, profile photo.
`bikes`: inventory, rates and availability.
`bike_photos`: multiple photos per bike, one row per photo.
`rentals`: confirmed bookings, durations, total and return status.
`bike_reviews`: customer reviews, one review per customer per bike, editable.
`transactions`: legacy table created earlier; this app uses `rentals` as the source of truth. Do not delete existing data.

## Important testing notes
This package passed PHP syntax checks only; it has NOT been connected to your actual MySQL server or browser-tested. Test all workflows locally before presenting. Back up first. Review the SQL migration before running it.

## Defense explanations
Prepared statements protect SQL values. `password_hash` and `password_verify` store and check passwords securely. PHP checks forms on the server, rather than trusting HTML validation. Sessions store login identity. Admin role checks prevent customer access. Bike images use server-side file type and size checks. Review eligibility is checked against confirmed rental records. A regular expression masks specified foul words. The existing navy/orange CSS is reused.
