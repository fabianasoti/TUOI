<?php
/**
 * Metadatos compartidos de la carta: categorías y catálogo de alérgenos.
 *
 * Se incluyen tanto desde el admin (admin/carta.php) como desde el render
 * público (pages/carta/index.php) para mantener una única fuente de verdad
 * — el orden definido aquí es también el orden visual.
 */

// Slug → etiquetas ES/EN de cada categoría de la carta.
// El orden de este array determina el orden de los tabs en el sitio público.
$CARTA_CATEGORIAS = [
    'desayunos'     => ['es' => 'Desayunos',     'en' => 'Breakfasts'],
    'menu-brunch'   => ['es' => 'Menú brunch',   'en' => 'Brunch menu'],
    'toque-salado'  => ['es' => 'Opciones saladas', 'en' => 'Savory options'],
    'menu-lunch'    => ['es' => 'Menú lunch',    'en' => 'Lunch menu'],
    'momento-dulce' => ['es' => 'Momento dulce', 'en' => 'Sweet moment'],
    'bebidas'       => ['es' => 'Bebidas',       'en' => 'Drinks'],
];

// Catálogo cerrado de etiquetas dietéticas / alérgenos. Cada ítem guarda en
// la columna `alergenos` un CSV de las claves seleccionadas.
$CARTA_ALERGENOS = [
    'vegano'        => ['es' => 'Vegano',       'en' => 'Vegan',         'icon' => '🌱'],
    'vegetariano'   => ['es' => 'Vegetariano',  'en' => 'Vegetarian',    'icon' => '🥗'],
    'sin-gluten'    => ['es' => 'Sin gluten',   'en' => 'Gluten-free',   'icon' => '🌾'],
    'sin-lactosa'   => ['es' => 'Sin lactosa',  'en' => 'Lactose-free',  'icon' => '🥛'],
    'picante'       => ['es' => 'Picante',      'en' => 'Spicy',         'icon' => '🌶️'],
    'frutos-secos'  => ['es' => 'Frutos secos', 'en' => 'Contains nuts', 'icon' => '🥜'],
    'con-alcohol'   => ['es' => 'Con alcohol',  'en' => 'Contains alcohol', 'icon' => '🍷'],
];
