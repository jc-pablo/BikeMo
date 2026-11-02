guide muna kita



# BikeMo setup and term test notes

## Install

 Create/select the `BikeMo` database in phpMyAdmin.
 For a new database, import `includes/bikemo.sql`.
 Make sure PHP has PDO MySQL and OpenSSL enabled. Composer dependencies are included in `vendor/`.

## Mailtrap sandbox setup

Transaction status emails use Mailtrap Sandbox SMTP. They are captured in your Mailtrap inbox for demonstration; they are not delivered to the customer's real inbox while using a sandbox.

Set these environment variables for the PHP/Apache process. Use the SMTP credentials shown in your Mailtrap Email Testing inbox, not an API token:

- `BIKEMO_SMTP_HOST` (normally `sandbox.smtp.mailtrap.io`)
- `BIKEMO_SMTP_PORT` (normally `2525`)
- `BIKEMO_SMTP_USERNAME`
- `BIKEMO_SMTP_PASSWORD`
- `BIKEMO_FROM_EMAIL` (optional sender label/address)

