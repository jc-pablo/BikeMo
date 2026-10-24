<?php
// Upload helper for JPG, PNG and WebP images (maximum 3 MB).
function savePhoto(array $file, string $folder): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 3 * 1024 * 1024) {
        throw new RuntimeException('Please upload an image smaller than 3 MB.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($types[$mime]) || !getimagesize($file['tmp_name'])) {
        throw new RuntimeException('Only JPG, PNG and WebP photos are allowed.');
    }
    $filename = bin2hex(random_bytes(12)) . '.' . $types[$mime];
    $directory = __DIR__ . '/../uploads/' . $folder;
    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        throw new RuntimeException('Upload directory could not be created.');
    }
    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
        throw new RuntimeException('Photo upload failed.');
    }
    return 'uploads/' . $folder . '/' . $filename;
}
