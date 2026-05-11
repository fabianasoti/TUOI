<?php
/**
 * Utilidades de imagen compartidas por el panel admin.
 *
 * El admin sube fotos en cualquier formato común (JPEG, PNG, WebP, GIF)
 * y aquí las normalizamos a WebP — más ligero y soportado por todos los
 * navegadores modernos — antes de guardarlas en disco. Esto reduce el
 * peso de la web pública sin pedirle al usuario que prepare las imágenes.
 *
 * Archivo de utilidades: incluir con require_once desde las páginas
 * que procesen subidas (admin/imagenes.php, admin/eventos.php, etc.).
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
    // Detectamos el tipo real por contenido (no por extensión) para evitar
    // que un usuario suba un .png que en realidad es otro formato.
    $mime = mime_content_type($src_path);
    $img  = match ($mime) {
        'image/jpeg' => imagecreatefromjpeg($src_path),
        'image/png'  => imagecreatefrompng($src_path),
        'image/webp' => imagecreatefromwebp($src_path),
        'image/gif'  => imagecreatefromgif($src_path),
        default      => false, // Tipo no soportado: que el caller lo maneje.
    };
    if (!$img) return false;

    // PNG/GIF pueden tener canal alfa (transparencia). Los convertimos a
    // truecolor y activamos el guardado del alfa para que la transparencia
    // sobreviva al volcado WebP. Sin esto, los fondos transparentes salen negros.
    if (in_array($mime, ['image/png', 'image/gif'], true)) {
        imagepalettetotruecolor($img);
        imagealphablending($img, true);
        imagesavealpha($img, true);
    }

    // Redimensionado opcional: si la imagen es más grande que $max_dim en
    // su lado más largo, la escalamos manteniendo proporción. Evita guardar
    // imágenes de 5000px que la web nunca va a mostrar a ese tamaño.
    if ($max_dim > 0) {
        $w = imagesx($img);
        $h = imagesy($img);
        $longest = max($w, $h);
        if ($longest > $max_dim) {
            $ratio = $max_dim / $longest;
            $new_w = (int) round($w * $ratio);
            $new_h = (int) round($h * $ratio);
            $resized = imagecreatetruecolor($new_w, $new_h);
            // Repetimos el setup de transparencia en el lienzo destino:
            // truecolor() crea un canvas opaco negro por defecto, así que
            // hay que prepararlo y rellenarlo con un color totalmente
            // transparente antes de copiar para que el alfa se preserve.
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefilledrectangle($resized, 0, 0, $new_w, $new_h, $transparent);
            imagecopyresampled($resized, $img, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
            imagedestroy($img); // Liberamos el recurso original.
            $img = $resized;
        }
    }

    $ok = imagewebp($img, $dest_path, $quality);
    imagedestroy($img); // Importante: GD no libera memoria automáticamente.
    return $ok;
}
