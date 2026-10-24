<?php

function validBikeImagePath(string $path): bool
{
    return (bool) preg_match(
        '~^(assets/images/|uploads/bikes/)[a-zA-Z0-9_./-]+\.(jpg|jpeg|png|webp)$~i',
        $path
    ) && strpos($path, '..') === false;
}

function bikeImages(array $bike): array
{
    $paths = [];
    $main = (string) ($bike['image_path'] ?? '');

    if (validBikeImagePath($main)) {
        $paths[] = $main;
    }

    $gallery = json_decode((string) ($bike['gallery_images'] ?? '[]'), true);
    if (is_array($gallery)) {
        foreach ($gallery as $path) {
            if (is_string($path) && validBikeImagePath($path)) {
                $paths[] = $path;
            }
        }
    }

    return array_values(array_unique($paths));
}

function bikePhotoExists(string $path): bool
{
    return validBikeImagePath($path) && is_file(__DIR__ . '/../' . $path);
}
