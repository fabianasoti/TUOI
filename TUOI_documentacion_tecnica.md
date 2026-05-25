# Documentación Técnica — TUOI Web

> **Versión:** Mayo 2026  
> **Stack:** PHP · MySQL · HTML/CSS/JS vanilla  
> **Entorno:** Apache + mod_rewrite, PHP ≥ 8.0, MySQL ≥ 5.7

---

## Índice

1. [Visión general](#1-visión-general)
2. [Estructura de archivos](#2-estructura-de-archivos)
3. [Base de datos](#3-base-de-datos)
4. [Configuración y arranque](#4-configuración-y-arranque)
5. [Sistema de internacionalización (i18n)](#5-sistema-de-internacionalización-i18n)
6. [Sistema de contenido editable](#6-sistema-de-contenido-editable)
7. [Páginas públicas](#7-páginas-públicas)
8. [Panel de administración](#8-panel-de-administración)
9. [Gestión de imágenes](#9-gestión-de-imágenes)
10. [Frontend: CSS y JavaScript](#10-frontend-css-y-javascript)
11. [Seguridad](#11-seguridad)
12. [Guía de despliegue](#12-guía-de-despliegue)
13. [Tareas pendientes (TO-DO)](#13-tareas-pendientes-to-do)

---

## 1. Visión general

TUOI es el sitio web corporativo de una cafetería de alimentación funcional en Valencia. Está construido en PHP puro (sin framework) con una arquitectura de plantillas incluidas. El contenido de la web es editable sin tocar código desde un panel de administración privado.

**Características principales:**

- Sitio bilingüe (ES/EN) con cambio de idioma via cookie
- Contenido editable desde panel admin (textos e imágenes)
- Carta con categorías de platos, gestión de imágenes por sección
- Página de eventos con formulario de contacto con anti-spam
- Panel admin protegido con autenticación, sesiones endurecidas y CSRF
- Imágenes convertidas automáticamente a WebP al subirse

---

## 2. Estructura de archivos

```
TUOI/
├── index.php                    # Página de inicio (Home)
├── set-lang.php                 # Endpoint de cambio de idioma (cookie)
├── README.md
│
├── config/
│   ├── conexion.php             # Conexión MySQLi compartida ($conexion)
│   ├── lang.php                 # i18n: strings de UI + helper t()
│   └── content_helper.php       # Carga de contenido editable desde BD
│
├── includes/
│   ├── header.php               # Header HTML compartido (navbar + <head>)
│   ├── footer.php               # Footer HTML compartido
│   ├── carta-subnav.php         # Subnavegación de la carta (categorías)
│   └── carta-page.php           # Template compartido para páginas de carta
│
├── pages/
│   ├── quienes-somos.php        # Página "Quiénes somos"
│   ├── carta/
│   │   ├── index.php            # Índice de la carta (vista general)
│   │   ├── desayunos.php        # Categoría Desayunos
│   │   ├── toque-salado.php     # Categoría Toque Salado
│   │   ├── momento-dulce.php    # Categoría Momento Dulce
│   │   ├── bebidas.php          # Categoría Bebidas
│   │   └── superalimentos.php   # Categoría Superalimentos
│   ├── eventos/
│   │   ├── index.php            # Página de Eventos (incluye formulario contacto)
│   │   ├── eventos-listos/      # Sub-página: Eventos listos
│   │   └── a-tu-medida/         # Sub-página: Eventos a medida
│   └── legal/
│       ├── aviso-legal.php
│       ├── privacidad.php
│       └── cookies.php
│
├── admin/
│   ├── config.php               # Guard de sesión + CSRF + helpers admin
│   ├── login.php                # Formulario de login
│   ├── logout.php               # Cierre de sesión
│   ├── index.php                # Dashboard principal del admin
│   ├── contenido.php            # Editor de textos del sitio
│   ├── imagenes.php             # Gestor de imágenes por sección
│   ├── testimonios.php          # Gestión de testimonios
│   ├── mensajes.php             # Bandeja de mensajes de contacto
│   ├── .htaccess                # Protección extra de acceso
│   ├── assets/css/admin.css     # Estilos exclusivos del panel admin
│   └── partials/
│       ├── sidebar.php          # Barra lateral del admin
│       ├── image_utils.php      # Conversión a WebP y redimensionado
│       └── toast.php            # Notificaciones tipo toast
│
├── assets/
│   ├── css/style.css            # Estilos del sitio público
│   ├── js/main.js               # JavaScript del sitio público
│   ├── fonts/inter.css          # Fuente Inter (self-hosted)
│   └── img/                     # Imágenes estáticas y de contenido
│       ├── tuoi_logo.png
│       ├── tuoi_blanco.png
│       ├── tuoi_quienes_somos.jpg
│       ├── carteles/            # Logos Balance/Energy/Focus/Power
│       └── carta/               # Imágenes de los platos, por categoría
│           ├── desayunos/
│           ├── desayunos-en/    # Versión EN (si existe)
│           ├── toque-salado/
│           └── …
│
└── db/
    ├── tuoi_db.sql              # Script inicial: crea BD, usuario y privilegios
    ├── admin_migration.sql      # Crea tablas del panel admin
    ├── rgpd_migration.sql       # Añade columnas de consentimiento RGPD
    └── eventos_textos_update.sql # Datos iniciales de textos de Eventos
```

---

## 3. Base de datos

### Usuario y base de datos

```sql
-- Creados por db/tuoi_db.sql
CREATE DATABASE tuoi_db CHARACTER SET utf8mb4;
CREATE USER 'tuoi_admin2026'@'localhost' IDENTIFIED BY '…';
GRANT ALL PRIVILEGES ON tuoi_db.* TO 'tuoi_admin2026'@'localhost';
```

### Tablas

#### `site_content` — Textos editables del sitio

| Columna | Tipo | Descripción |
|---|---|---|
| `id` | INT PK AUTO | Identificador interno |
| `content_key` | VARCHAR(100) UNIQUE | Clave del texto (ej. `hero_h1`, `hero_h1_en`) |
| `content_value` | TEXT | Valor del texto |
| `updated_at` | TIMESTAMP | Actualización automática |

Las claves de idioma inglés llevan sufijo `_en`. Ejemplo: `hero_h1` (ES) + `hero_h1_en` (EN).

#### `admin_users` — Usuarios del panel admin

| Columna | Tipo | Descripción |
|---|---|---|
| `id` | INT PK AUTO | — |
| `username` | VARCHAR(50) UNIQUE | Nombre de usuario |
| `password_hash` | VARCHAR(255) | Hash `password_hash()` de PHP |
| `created_at` | TIMESTAMP | — |

#### `image_order` — Orden personalizado de imágenes

| Columna | Tipo | Descripción |
|---|---|---|
| `id` | INT PK AUTO | — |
| `section` | VARCHAR(100) | Slug de la sección (ej. `carta/desayunos`) |
| `filename` | VARCHAR(255) | Nombre del archivo |
| `sort_order` | INT | Posición en la galería |

Índice compuesto UNIQUE en `(section, filename)`.

#### `contact_submissions` — Formulario de contacto (Eventos)

| Columna | Tipo | Descripción |
|---|---|---|
| `id` | INT PK AUTO | — |
| `name`, `email`, `phone` | VARCHAR | Datos del remitente |
| `message` | TEXT | Cuerpo del mensaje |
| `source_page` | VARCHAR | Página desde la que se envió |
| `consent_at` | DATETIME | Fecha del consentimiento RGPD |
| `consent_ip` | VARCHAR(45) | IP en el momento del consentimiento |
| `submitted_at` | TIMESTAMP | Fecha de envío |

> Las columnas `consent_at` y `consent_ip` se añaden con `db/rgpd_migration.sql`. Si ya existen la migración las ignora.

### Orden de ejecución de los scripts SQL

```
1. db/tuoi_db.sql              → crea BD y usuario
2. db/admin_migration.sql      → tablas site_content, admin_users, image_order
3. db/rgpd_migration.sql       → añade columnas RGPD a contact_submissions
4. db/eventos_textos_update.sql → datos iniciales de la página de Eventos
```

---

## 4. Configuración y arranque

### `config/conexion.php`

Crea la variable global `$conexion` (recurso `mysqli`). Si la conexión falla, escribe el error en `$error_db` y deja que la página continúe renderizando con los valores por defecto.

```php
$conexion = mysqli_connect($host, $user, $password, $database);
if (!$conexion) {
    $error_db = "Error de conexión: " . mysqli_connect_error();
} else {
    mysqli_set_charset($conexion, "utf8mb4");
}
```

**Variables de conexión** (actualmente en el archivo — ver TO-DO de seguridad):

| Variable | Valor por defecto |
|---|---|
| `$host` | `localhost` |
| `$user` | `tuoi_admin2026` |
| `$database` | `tuoi_db` |

### Patrón de inclusión en páginas

Cada página pública define tres variables antes de incluir el header:

```php
$base         = '';          // ruta relativa hasta la raíz
$current_page = 'inicio';   // para marcar el enlace activo en la nav
$page_title   = 'TUOI | …'; // título del <title>
require 'config/conexion.php';
require 'config/content_helper.php';
require 'includes/header.php';
// header.php carga lang.php → define $lang
```

Para páginas dentro de subdirectorios, `$base` se ajusta con `'../../'`, `'../../../'`, etc.

---

## 5. Sistema de internacionalización (i18n)

**Archivo:** `config/lang.php`

### Detección de idioma

```php
$lang = (isset($_COOKIE['tuoi_lang']) && $_COOKIE['tuoi_lang'] === 'en') ? 'en' : 'es';
```

El español es el idioma por defecto. La cookie `tuoi_lang` la establece `set-lang.php` cuando el usuario pulsa el selector de idioma del header.

### Cambio de idioma — `set-lang.php`

Valida el parámetro `?lang=es|en`, escribe la cookie por 1 año y redirige de vuelta al `HTTP_REFERER` (solo si es del mismo origen, para evitar open redirect).

### Helper `t($key)` / `t_raw($key)`

```php
// Devuelve el string traducido para $lang activo.
// Si la clave no existe en EN, cae al español.
t('nav_home')     // → 'Inicio' / 'Home'
t_raw('qs_label') // → HTML sin escapar (para tags <strong>, etc.)
```

Los strings están en `$_ui` como array `clave → [es => '…', en => '…']`.

### Metadatos de la carta (`$carta_info`)

Definido también en `lang.php`. Contiene título y subtítulo de cada categoría en ambos idiomas:

```php
$carta_info = [
    'desayunos' => [
        'es' => ['Desayunos', 'Empieza el día con energía'],
        'en' => ['Breakfasts', 'Start the day with energy'],
    ],
    // …
];
```

---

## 6. Sistema de contenido editable

**Archivo:** `config/content_helper.php`

### `load_site_content($conexion, $lang)`

Carga todos los textos editables del sitio. Sigue tres pasos:

1. **Parte de los defaults en español** definidos en el mismo archivo. El sitio funciona aunque la BD esté vacía.
2. **Sobrescribe con valores de `site_content`** si hay conexión a BD.
3. **Si `$lang === 'en'`**, busca cada clave con sufijo `_en` y la sustituye. Si no existe, mantiene el texto en español como fallback.

Devuelve un array `clave → texto` listo para usar en plantillas con `$c['clave']`.

### `load_ordered_images($conexion, $section, $dir_path)`

Devuelve la lista de imágenes de una carpeta ordenada según `image_order` en BD. Si una imagen no tiene registro de orden, se añade al final con orden alto. Devuelve solo archivos `.webp`, `.jpg`, `.jpeg` y `.png`.

### `upsert_content($conexion, $key, $value)`

Helper del admin para guardar o actualizar un texto en `site_content`:

```sql
INSERT INTO site_content (content_key, content_value) VALUES (…)
ON DUPLICATE KEY UPDATE content_value = …, updated_at = NOW()
```

---

## 7. Páginas públicas

### `index.php` — Home

Secciones:
- **Hero:** imagen de fondo, etiqueta, h1, subtítulo y CTA a la carta
- **¿Quiénes somos?:** preview con texto y enlace a la página completa
- **Nuestra filosofía:** grid de 4 tarjetas (Balance / Energy / Focus / Power) + lista de valores

Todo el texto viene de `load_site_content()`.

### `pages/quienes-somos.php` — Quiénes somos

Tres bloques de contenido (`qs_page_b1_*`, `qs_page_b2_*`, `qs_page_b3_*`) + cierre con CTA a la carta. Texto editable desde admin.

### `pages/carta/` — La carta

**`pages/carta/index.php`** muestra un índice visual con las categorías. Cada categoría enlaza a su página individual.

**`includes/carta-page.php`** es un template compartido que usan todas las páginas de categoría (`desayunos.php`, `bebidas.php`, etc.). Recibe:

| Variable | Descripción |
|---|---|
| `$current_carta` | Slug de la categoría (ej. `desayunos`) |
| `$carta_titulo` | Título en español (se reemplaza si `$lang === 'en'`) |
| `$carta_desc` | Descripción en español (ídem) |

El template resuelve la carpeta de imágenes correcta según idioma:
- EN con carpeta `-en` existente y no vacía → usa `carta/desayunos-en/`
- EN sin carpeta `-en` → fallback a `carta/desayunos/`

### `pages/eventos/index.php` — Eventos

Incluye:
- Hero con CTAs
- Carrusel de imágenes (de `eventos/carrusel/`)
- Sección "Por qué TUOI" (4 bloques)
- Marquee animado de tipos de eventos
- Menús disponibles (coffee break, brunch, tardeo)
- Prueba social (testimonio + logos de clientes)
- Formulario de contacto

**Formulario de contacto:**
- Validación de campos (nombre, email, mensaje, consentimiento RGPD)
- Honeypot anti-spam: campo oculto `c_website`; si llega con valor, se simula éxito sin procesar
- Soporte AJAX: si la petición lleva `X-Requested-With: XMLHttpRequest`, responde JSON
- Los envíos se guardan en `contact_submissions`

### `pages/legal/`

Tres páginas estáticas de contenido legal (aviso legal, política de privacidad, política de cookies). Sin contenido dinámico.

### `includes/header.php`

- Carga `config/lang.php`
- Cache-busting del CSS: `?v=<filemtime>` en el `<link>`
- Navbar con dropdown de Carta y selector de idioma
- Atributo `aria-expanded` en hamburguesa y dropdown

### `includes/footer.php`

- Links de navegación, dirección física, redes sociales
- Links a páginas legales
- Cierra `</body>` y `</html>`

---

## 8. Panel de administración

Accesible en `/admin/`. Protegido por autenticación de sesión PHP.

### Flujo de acceso

```
GET /admin/login.php   → formulario de login
POST /admin/login.php  → verifica credenciales con password_verify()
                         si OK → $_SESSION['admin_logged_in'] = true
                              → redirect a index.php
```

### `admin/config.php` — Guard central

Incluido al inicio de cada página del admin. Realiza:

1. Configura la cookie de sesión (httponly, samesite=Lax, secure si HTTPS)
2. Llama a `session_start()`
3. Redirige a `login.php` si no hay sesión activa
4. Carga `config/conexion.php`
5. Define los helpers de CSRF y el helper `upsert_content()`

### CSRF

- `csrf_token()` genera y persiste un token de 32 bytes en sesión
- `csrf_field()` devuelve un `<input type="hidden">` listo para insertar en formularios
- `csrf_check()` verifica el token en cada POST (comparación con `hash_equals` para evitar timing attacks)
- La verificación es automática: cualquier POST a una página que incluya `config.php` es comprobado

### Secciones del admin

| Página | Función |
|---|---|
| `index.php` | Dashboard con resumen y accesos rápidos |
| `contenido.php` | Editor de textos del sitio (ES y EN) organizado por secciones |
| `imagenes.php` | Subida, reordenación y borrado de imágenes por sección |
| `testimonios.php` | Gestión de testimonios de la página de Eventos |
| `mensajes.php` | Bandeja de entrada de contacto (con filtros y borrado) |
| `logout.php` | Destruye la sesión y redirige a login |

### Editor de contenido (`contenido.php`)

- Tiene un selector de idioma (`?edit_lang=es|en`) y un selector de sección (`?section=home|eventos|quienes|…`)
- Por cada clave de texto renderiza un `<textarea>` o `<input>`
- Al guardar hace `upsert_content()` para cada campo modificado
- Los campos de la variante EN editan claves con sufijo `_en`

---

## 9. Gestión de imágenes

**Admin:** `admin/imagenes.php` + `admin/partials/image_utils.php`

### Secciones de imágenes

| Slug | Carpeta en disco | Uso |
|---|---|---|
| `carteles` | `assets/img/carteles/` | Logos Balance/Energy/Focus/Power |
| `quienes_somos` | `assets/img/quienes_somos/` | Foto de "Quiénes somos" |
| `inicio` | `assets/img/` | Imagen principal del hero |
| `carta/desayunos` | `assets/img/carta/desayunos/` | Carta: Desayunos (ES) |
| `carta/desayunos-en` | `assets/img/carta/desayunos-en/` | Carta: Desayunos (EN) |
| … | … | (ídem para cada categoría de carta) |
| `eventos/carrusel` | `assets/img/eventos/carrusel/` | Carrusel de Eventos |
| `eventos/por-que-tuoi` | `assets/img/eventos/por-que-tuoi/` | Bloque "Por qué TUOI" |
| `eventos/logos` | `assets/img/eventos/logos/` | Logos de clientes |
| `eventos/cta-fondo` | `assets/img/eventos/cta-fondo/` | Fondo del CTA de Eventos |

### Pipeline de subida

1. Validación de extensión (`jpg`, `jpeg`, `png`, `webp`, `gif`) y tamaño máximo (20 MB)
2. Validación de que la carpeta destino está dentro de `assets/img/` (evita path traversal)
3. Conversión a WebP con `convert_to_webp()`:
   - Detección de tipo por `mime_content_type()` (no por extensión)
   - Preservación de transparencia (PNG/GIF)
   - Redimensionado proporcional si supera 2000 px en el lado más largo
   - Calidad WebP: 82 (por defecto)
4. Creación automática de la carpeta si no existe (`mkdir` con permisos 0775)

### Orden de imágenes

Las imágenes pueden reordenarse en el admin con drag-and-drop. El nuevo orden se guarda en `image_order`. La función `load_ordered_images()` respeta este orden al servir imágenes en el frontend.

---

## 10. Frontend: CSS y JavaScript

### `assets/css/style.css`

- Variables CSS para tokens de diseño (colores, tipografía, espacios)
- Clases de layout: `.hero`, `.section-quienes`, `.section-filosofia`, `.features-grid`
- Navbar responsive con dropdown
- Badges de colores por categoría filosófica (`.badge-verde`, `.badge-naranja`, `.badge-morado`, `.badge-amarillo`)
- Footer responsive

### `assets/js/main.js`

Módulos al cargar (`DOMContentLoaded`):

| # | Módulo | Descripción |
|---|---|---|
| 1 | Navbar scroll | Añade la clase `.scrolled` al navbar al bajar 40 px |
| 2 | Menú hamburguesa | Toggle del menú móvil, bloqueo del scroll del body, cierre de dropdowns |
| 3 | Dropdown carta | En desktop: hover (CSS). En móvil (≤768px): click activa `.open`, previene navegación |
| 4 | Cierre dropdown externo | Click fuera del dropdown lo cierra |
| 5 | Formulario de contacto (Eventos) | Envío AJAX con validación de campos y mensaje de éxito/error |

### Cache-busting del CSS

El header añade automáticamente `?v=<filemtime>` a la URL del CSS para forzar la descarga cuando el archivo cambia:

```php
$css_v = @filemtime(dirname(__DIR__) . '/assets/css/style.css') ?: time();
// → <link href="style.css?v=1714000000">
```

---

## 11. Seguridad

### Autenticación admin

- Contraseñas almacenadas con `password_hash()` (bcrypt)
- Verificación con `password_verify()` (resistente a timing attacks)
- Sesión con cookie `httponly`, `samesite=Lax`, `secure` si HTTPS

### CSRF

- Token de 32 bytes aleatorios (`bin2hex(random_bytes(32))`) por sesión
- Verificación con `hash_equals()` en cada POST del admin

### Validación de subida de imágenes

- Comprobación de MIME real con `mime_content_type()`, no solo extensión
- Validación de que la ruta de destino está dentro de `assets/img/` (evita path traversal con `realpath()`)

### Anti-spam en formulario de contacto

- Campo honeypot `c_website` invisible para humanos
- Si llega con valor → respuesta de éxito falsa sin procesar ni guardar nada

### Consentimiento RGPD

- El formulario de contacto requiere checkbox de consentimiento explícito
- Se guardan `consent_at` (datetime del envío) y `consent_ip` (IP del usuario)

### Acceso al panel admin

- `.htaccess` en `admin/` con protección adicional de acceso
- Cada página del admin incluye `config.php` que redirige si no hay sesión activa

### Puntos de mejora pendientes (ver TO-DO)

- Las credenciales de BD están actualmente en `config/conexion.php` en texto plano

---

## 12. Guía de despliegue

### Requisitos del servidor

- PHP ≥ 8.0 con extensiones: `mysqli`, `gd` (para WebP), `mbstring`
- MySQL ≥ 5.7 o MariaDB ≥ 10.3
- Apache con `mod_rewrite` activo

### Pasos de instalación

```bash
# 1. Subir los archivos al servidor
rsync -av TUOI/ usuario@servidor:/var/www/html/tuoi/

# 2. Ejecutar los scripts SQL en orden
mysql -u root -p < db/tuoi_db.sql
mysql -u root -p < db/admin_migration.sql
mysql -u root -p < db/rgpd_migration.sql
mysql -u root -p < db/eventos_textos_update.sql

# 3. Ajustar credenciales en config/conexion.php
#    (o moverlas a variables de entorno — ver TO-DO)

# 4. Crear el primer usuario admin (desde MySQL o via script)
INSERT INTO admin_users (username, password_hash)
VALUES ('admin', PASSWORD_HASH_GENERADO_CON_PHP);
# Para generar el hash: php -r "echo password_hash('tu_contraseña', PASSWORD_DEFAULT);"

# 5. Dar permisos de escritura al servidor sobre la carpeta de imágenes
chmod -R 775 assets/img/
chown -R www-data:www-data assets/img/
```

### Verificación

- `/` → Home carga con texto por defecto
- `/admin/login.php` → Login funciona
- `/admin/imagenes.php` → Se puede subir una imagen y se convierte a WebP
- Selector ES/EN en el header → cambia el idioma

---

## 13. Tareas pendientes (TO-DO)

| Prioridad | Descripción | Archivo |
|---|---|---|
| 🔴 Alta | Mover credenciales de BD a variables de entorno o archivo fuera del repositorio | `config/conexion.php` |
| 🟡 Media | Configurar `secure=true` en la cookie de sesión admin también en desarrollo local (o usar HTTPS con cert autofirmado) | `admin/config.php` |
| 🟡 Media | Añadir rate-limiting al formulario de contacto (p.ej. por IP en `contact_submissions`) | `pages/eventos/index.php` |
| 🟢 Baja | Internacionalizar los textos de la carta con más granularidad (actualmente solo título y descripción de categoría) | `config/lang.php` |
| 🟢 Baja | Añadir sitemap.xml y meta tags Open Graph | `includes/header.php` |

---

*Documentación generada en Mayo 2026 a partir del código fuente del repositorio TUOI.*
