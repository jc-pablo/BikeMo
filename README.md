# BikeMo setup and term test notes

## Install

1. Create/select the `BikeMo` database in phpMyAdmin.
2. For a new database, import `includes/bikemo.sql`.
3. For an existing database, first back it up, then run `includes/migration_term_test.sql` once. Do not run that migration on a fresh database.
4. Put the project in your web server folder and set database details in `includes/db.php`.
5. Make sure PHP has PDO MySQL and OpenSSL enabled. Composer dependencies are included in `vendor/`.

Each bike row represents one physical bike, so there is no bike stock quantity. The customer-facing number is the rental duration (such as 2 days), not the number of bikes. One selected bike is booked per cart item. Multiple different bikes in one checkout share an order code and update together.

## Monthly expenses

For an existing BikeMo database, select the `BikeMo` database in phpMyAdmin, open **Import**, and import `includes/expenses.sql` once. This adds only the expenses table. Then sign in as an admin and choose **Expenses** in the sidebar to add, edit, or delete monthly costs and see that month's total. For a new database, import `includes/bikemo.sql` first, then also import `includes/expenses.sql`.

## Mailtrap sandbox setup

Transaction status emails use Mailtrap Sandbox SMTP. They are captured in your Mailtrap inbox for demonstration; they are not delivered to the customer's real inbox while using a sandbox.

Set these environment variables for the PHP/Apache process. Use the SMTP credentials shown in your Mailtrap Email Testing inbox, not an API token:

- `BIKEMO_SMTP_HOST` (normally `sandbox.smtp.mailtrap.io`)
- `BIKEMO_SMTP_PORT` (normally `2525`)
- `BIKEMO_SMTP_USERNAME`
- `BIKEMO_SMTP_PASSWORD`
- `BIKEMO_FROM_EMAIL` (optional sender label/address)

On Windows/XAMPP, add the variables in Windows Environment Variables, then fully restart Apache. Set username and password to the values provided by your Mailtrap sandbox. Do not put real SMTP passwords in source files or upload them to Git/ZIP. After an admin changes an order status, check the Mailtrap inbox for an email containing the bike list, each subtotal, grand total, and attached PDF receipt.

## Existing email credential

The previously uploaded project contained a Gmail app password. That credential has been removed from this copy. Revoke it in the Google Account that issued it, since it was included in the uploaded archive.

See `PROFILE_PHOTO_GUIDE.md` for the exact files and code path that upload, store, retrieve, and display the customer profile picture.

Admins can open **Edit my profile** from the lower left of the admin sidebar to update name, email, phone, and profile photo. The photo also appears in the sidebar after saving.
