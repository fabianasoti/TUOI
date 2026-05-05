<?php
/**
 * Shared image utilities for the admin panel.
 * Require this file once; do not call directly.
 */

/**
 * Convert any supported image to WebP using GD, redimensionando si la imagen
 * supera $max_dim píxeles en el lado más largo (mantiene proporción).
 *
 * @param string $src_path  Imagen origen
 * @param string $dest_path Destino .webp
 * @param int    $quality   1-100, calidad WebP (default 82)
 * @param int    $max_dim   Px máximos del lado más largo (default 2000, 0 = sin límite)
 * @return bool true si éxito; false si no se pudo decodificar
 */
function convert_to_webp(string $src_path, string $dest_path, int $quality = 82, int $max_dim = 2000): bool {
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

    // Redimensionar si excede el máximo
    if ($max_dim > 0) {
        $w = imagesx($img);
        $h = imagesy($img);
        $longest = max($w, $h);
        if ($longest > $max_dim) {
            $ratio = $max_dim / $longest;
            $new_w = (int) round($w * $ratio);
            $new_h = (int) round($h * $ratio);
            $resized = imagecreatetruecolor($new_w, $new_h);
            // Preservar transparencia para PNG/GIF
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefilledrectangle($resized, 0, 0, $new_w, $new_h, $transparent);
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
            imagedestroy($img);
            $img = $resized;
        }
    }

    $ok = imagewebp($img, $dest_path, $quality);
    imagedestroy($img);
    return $ok;
}
