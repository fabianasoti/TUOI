<?php
/**
 * Shared image utilities for the admin panel.
 * Require this file once; do not call directly.
 */

/**
 * Convert any supported image to WebP using GD.
 * Returns true on success, false if the source cannot be decoded.
 */
function convert_to_webp(string $src_path, string $dest_path, int $quality = 82): bool {
    $mime = mime_content_type($src_path);
    $img  = match ($mime) {
        'image/jpeg' => imagecreatefromjpeg($src_path),
        'image/png'  => imagecreatefrompng($src_path),
        'image/webp' => imagecreatefromwebp($src_path),
        'image/gif'  => imagecreatefromgif($src_path),
        default      => false,
    };
    if (!$img) return false;

    if (in_array($mime, ['image/png', 'image/gif'], true)) {
        imagepalettetotruecolor($img);
        imagealphablending($img, true);
        imagesavealpha($img, true);
    }

    $ok = imagewebp($img, $dest_path, $quality);
    imagedestroy($img);
    return $ok;
}
