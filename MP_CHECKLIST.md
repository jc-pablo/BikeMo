# BikeMo MP and term test code review

This is a source-code review of the uploaded project and the changes in this copy. A live rubric score still depends on importing/upgrading the database and demonstrating the flows in a running XAMPP instance.

| Requirement | Code status | Evidence / note |
|---|---|---|
| MP1 Product/service CRUD | Implemented | Admin can create, edit, list, delete bikes; deletion is guarded when rentals exist. |
| MP1 Single photo | Implemented | Bike photo uploader accepts one or multiple files. |
| MP1 Multiple photos/view | Implemented | Gallery paths are stored and rendered on browse/detail pages. |
| MP2 User CRUD | Implemented across the app | Registration creates accounts, admin lists and edits role/status, customers edit their profile, admin can delete accounts without rental/review history. Accounts with history should be deactivated to preserve records. |
| MP2 Registration photo | Implemented | Optional profile photo upload at registration. |
| MP2 Profile photo update | Implemented | Customer profile supports photo replacement. The admin can edit name, email, phone, and photo from the sidebar profile link. |
| MP2 Deactivate user | Implemented | Admin can set account inactive; inactive users cannot log in or pass role checks. |
| MP2 Update active user's role | Implemented | Admin role selector updates role and active state. |
| MP3 Email login/logout | Implemented | Login looks up email and verifies a password hash; logout clears session. |
| MP3 Restrict customer from CRUD | Implemented | Shared role guard protects customer/admin pages and action scripts. |
| MP3 Redirect anonymous users with message | Implemented | Role guard redirects to login with an access message. |
| MP3 Admin access | Implemented | Admin role guard permits admin CRUD pages. |
| MP4 Only customers who rented may review | Implemented | Review submission checks for a rental by that customer and bike. |
| MP4 Show reviews on details | Implemented | Bike detail page lists customer reviews. |
| MP4 Update review | Implemented | Unique customer/bike review is inserted or updated. |
| MP4 Regex foul-word mask | Implemented | Review text is filtered with `preg_replace` before storage. |
| MP5 PHP form validation | Implemented | Login and bike CRUD forms use `novalidate`; server-side PHP validates the submitted values. |
| Term test prepared statements | Implemented | Checkout writes and admin status updates use PDO prepared statements and a database transaction. |
| Admin sees orders/updates status | Implemented | Admin rental page lists order lines and updates all items in a checkout together. |
| Status-change email | Implemented in code | Sent after the database status update succeeds. |
| Email item list/subtotals/grand total | Implemented in code | Order email includes each bike line subtotal plus the grouped grand total. |
| PDF transaction receipt attachment | Implemented in code | A receipt PDF is generated and attached to the email. |
| Mailtrap service | Implemented | Mailer targets Mailtrap Sandbox SMTP. Add the sandbox SMTP environment variables and verify the email/attachment appears in the Mailtrap inbox. Without those credentials, the application saves status but reports that email was not sent. |

## Database changes

Fresh install: import `includes/bikemo.sql`.
There is no bike-stock quantity in the schema. The customer number field was the length of the rental; it is now labeled **Rental duration**. Each bike listing remains one physical bike, booked once per cart item.

## Validation limits

This workspace does not have the PHP CLI installed, so PHP linting and a live XAMPP/Mailtrap run could not be performed here. The code review is complete, but runtime confirmation of the term test email and PDF requires configuring Mailtrap and running the order-status demonstration.
