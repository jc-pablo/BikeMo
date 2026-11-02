# Where the customer profile photo code lives

The profile picture now appears in the customer header and admin sidebar, not only on the profile-edit screen. Both customer and admin profiles can upload a new photo. Follow this path to understand it.

## 1. Where a photo gets uploaded

File: `includes/uploads.php`

The `savePhoto()` function checks the image type and size, then saves it under `uploads/users/`. The database stores the resulting relative path, for example `uploads/users/abc123.webp`.

The profile form is in `customers/profile.php`. Its upload field is:

```html
<input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
```

## 2. Where the profile form saves the photo path

File: `customers/profile.php`

When a new photo was uploaded, this is the database update:

```php
$stmt = $pdo->prepare('UPDATE users SET name=?, phone=?, photo=? WHERE id=?');
$stmt->execute([$name, $phone, $photo, $id]);
```

The `$photo` variable is the path returned by `savePhoto()`.

## 3. Where the photo is retrieved and shown around the interface

File: `includes/customer_nav.php`

This shared header reads the current photo path for the signed-in user:

```php
$customerQuery = $pdo->prepare('SELECT name, photo FROM users WHERE id = ?');
$customerQuery->execute([$customerId]);
$customerAccount = $customerQuery->fetch(PDO::FETCH_ASSOC) ?: [];
```

Then it shows the image if a saved image path exists, or the first-letter avatar if no photo has been uploaded:

```php
<?php if ($customerHasPhoto): ?>
    <img class="bm-user__avatar-image"
         src="../<?= $customerEscape($customerPhoto) ?>"
         alt="<?= $customerEscape($customerName) ?> profile photo">
<?php else: ?>
    <span class="bm-user__avatar"><?= $customerEscape($customerInitial) ?></span>
<?php endif; ?>
```

The header is included by these customer pages: `customers/dashboard.php`, `customers/bikes.php`, `customers/cart.php`, `customers/rentals.php`, `customers/profile.php`, and `customers/review.php`.

## 4. Where to change the picture's size or shape

File: `assets/css/style.css`

Change these styles to adjust the small picture in the header:

```css
.bm-user__avatar-image {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 50%;
}
```

The larger preview on the profile page uses `.bm-profile-photo` in the same CSS file.

## 5. Database column

File: `includes/bikemo.sql`

The `users.photo` column stores the image path. The image itself stays in `uploads/users/`; the database stores only its path.

## 6. Admin profile editing and admin photo display

Admin profile page: `admin/profile.php`. It uses `savePhoto()` like the customer page, validates name/email/phone in PHP, and updates the logged-in admin row using PDO prepared statements. Open it by clicking **Edit my profile** at the bottom of the admin sidebar.

Shared admin header: `admin/_header.php`. It loads the current admin name/photo and displays the photo in the sidebar. The shared header appears on every admin page. The admin sidebar image size is controlled by `.bm-admin-avatar-photo` in `assets/css/style.css`.

The admin photo path is stored in the same `users.photo` database column as the customer photo.

## 7. If a profile image link was broken or oversized

The shared headers and profile pages now verify that the saved image file exists before rendering it. If the database still points to an image file that is missing from `uploads/users/`, BikeMo shows the user's initial instead of a broken image; upload the photo again from the profile page to save a current path. The stylesheet URL includes a version query so the browser reloads the corrected avatar sizing rules.
