# Documentación Técnica — TUOI Web

> **Versión:** Junio 2026 (rev. 01-06-2026)
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
9. [Gestión de usuarios admin](#9-gestión-de-usuarios-admin)
10. [Gestión de imágenes](#10-gestión-de-imágenes)
11. [Frontend: CSS y JavaScript](#11-frontend-css-y-javascript)
12. [Seguridad](#12-seguridad)
13. [Guía de despliegue](#13-guía-de-despliegue)
14. [Tareas pendientes (TO-DO)](#14-tareas-pendientes-to-do)

---

## 1. Visión general

TUOI es el sitio web corporativo de una cafetería de alimentación funcional en Valencia. Está construido en PHP puro (sin framework) con una arquitectura de plantillas incluidas. El contenido de la web es editable sin tocar código desde un panel de administración privado.

**Características principales:**

- Sitio bilingüe (ES/EN) con cambio de idioma via cookie
- Contenido editable desde panel admin (textos, imágenes y carta plato a plato)
- Carta gestionada como base de datos (tabla `carta_items`): ítems con nombre, descripción, ingredientes, precio, foto, alérgenos, sub-grupo y orden
- Formulario de contacto único en `/pages/contacto/` (con anti-spam y consentimiento RGPD)
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
│   ├── content_helper.php       # Carga de contenido editable desde BD
│   └── carta_meta.php           # Categorías de la carta + catálogo de alérgenos (fuente única ES/EN)
│
├── includes/
│   ├── header.php               # Header HTML compartido (navbar + <head>)
│   ├── footer.php               # Footer HTML compartido
│   ├── carta-subnav.php         # Subnavegación de la carta (categorías)
│   ├── carta-page.php           # Template legacy de páginas de carta por imágenes (fallback)
│   ├── contact-form.php         # Markup del formulario de contacto (reutilizable)
│   └── contact-handler.php      # Lógica de POST/AJAX del formulario de contacto
│
├── pages/
│   ├── quienes-somos.php        # Página "Quiénes somos"
│   ├── contacto/
│   │   └── index.php            # Formulario único de contacto del sitio
│   ├── carta/
│   │   ├── index.php            # Render de la carta a partir de carta_items (?cat=slug)
│   │   ├── desayunos.php        # Páginas-shortcut por categoría (template carta-page.php)
│   │   ├── menu-brunch.php
│   │   ├── menu-lunch.php
│   │   ├── toque-salado.php
│   │   ├── momento-dulce.php
│   │   └── bebidas.php
│   ├── eventos/
│   │   ├── index.php            # Página de Eventos (hub con 2 opciones)
│   │   ├── eventos-listos/      # Sub-página: "Listos para disfrutar" (experiencias prediseñadas)
│   │   └── a-tu-medida/         # Sub-página: "Diseñado a tu medida"
│   └── legal/
│       ├── aviso-legal.php
│       ├── privacidad.php
│       └── cookies.php
│
├── admin/
│   ├── config.php               # Guard de sesión + CSRF + helpers admin (is_admin, require_admin)
│   ├── login.php                # Formulario de login (guarda rol en sesión)
│   ├── logout.php               # Cierre de sesión
│   ├── index.php                # Dashboard principal del admin
│   ├── contenido.php            # Editor de textos del sitio (hub por páginas, ES/EN)
│   ├── carta.php                # Gestor de la carta plato a plato (categorías + ítems + EN)
│   ├── imagenes.php             # Gestor de imágenes por sección
│   ├── testimonios.php          # Gestión de testimonios
│   ├── mensajes.php             # Bandeja de mensajes de contacto
│   ├── usuarios.php             # Gestión de usuarios admin (crear, eliminar, roles, contraseña)
│   ├── sql/                     # Scripts SQL puntuales (p.ej. carta_translations_en.sql)
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
├── .gitignore                   # Excluye .claude/, logs y archivos de entorno
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
| `role` | ENUM('admin','editor') | Rol del usuario. `admin` = acceso total; `editor` = solo edición de contenido |
| `password_hash` | VARCHAR(255) | Hash `password_hash()` de PHP (bcrypt) |
| `created_at` | TIMESTAMP | — |

> La columna `role` se añade automáticamente (auto-migración en `admin/usuarios.php`) si la tabla ya existe sin ella. El primer usuario existente hereda el rol `admin`.

#### `image_order` — Orden personalizado de imágenes

| Columna | Tipo | Descripción |
|---|---|---|
| `id` | INT PK AUTO | — |
| `section` | VARCHAR(100) | Slug de la sección (ej. `carta/desayunos`) |
| `filename` | VARCHAR(255) | Nombre del archivo |
| `sort_order` | INT | Posición en la galería |

Índice compuesto UNIQUE en `(section, filename)`.

#### `contact_submissions` — Formulario de contacto

| Columna | Tipo | Descripción |
|---|---|---|
| `id` | INT PK AUTO | — |
| `name`, `email`, `phone` | VARCHAR | Datos del remitente |
| `message` | TEXT | Cuerpo del mensaje |
| `source_page` | VARCHAR | **Deprecada (mayo 2026).** El sitio tiene un único formulario en `/pages/contacto/`, ya no se distingue origen. La columna se mantiene para registros históricos pero el handler ya no escribe en ella. |
| `consent_at` | DATETIME | Fecha del consentimiento RGPD |
| `consent_ip` | VARCHAR(45) | IP en el momento del consentimiento |
| `submitted_at` | TIMESTAMP | Fecha de envío |

> Las columnas `consent_at` y `consent_ip` se añaden con `db/rgpd_migration.sql`. Si ya existen la migración las ignora.

#### `carta_items` — Ítems de la carta

| Columna | Tipo | Descripción |
|---|---|---|
| `id` | INT PK AUTO | — |
| `categoria` | VARCHAR(50) | Slug de categoría (clave de `$CARTA_CATEGORIAS` en `config/carta_meta.php`) |
| `subcategoria` | VARCHAR(100) NULL | Sub-grupo opcional dentro de la categoría (ej. "Tostadas", "Bowls") |
| `subcategoria_en` | VARCHAR(100) NULL | Traducción EN del sub-grupo |
| `nombre` | VARCHAR(255) | Nombre del plato/bebida |
| `nombre_en` | VARCHAR(255) NULL | Traducción EN del nombre |
| `descripcion` | TEXT NULL | Descripción corta |
| `descripcion_en` | TEXT NULL | Traducción EN |
| `ingredientes` | TEXT NULL | Lista/nota de ingredientes |
| `ingredientes_en` | TEXT NULL | Traducción EN |
| `precio` | DECIMAL(6,2) NULL | Precio en euros (puede ser NULL si no aplica) |
| `es_suplemento` | TINYINT(1) | Si vale 1, el precio se muestra como `+X,XX €` |
| `alergenos` | VARCHAR(255) NULL | CSV de claves de `$CARTA_ALERGENOS` (ej. `vegano,sin-gluten`) |
| `foto` | VARCHAR(255) NULL | Nombre del archivo en `assets/img/carta/items/` |
| `visible` | TINYINT(1) | 1 = visible en la web pública, 0 = oculto |
| `sort_order` | INT | Orden dentro de la categoría (drag-and-drop en admin) |
| `created_at`, `updated_at` | TIMESTAMP | Timestamps automáticos |

La tabla se crea/migra de forma idempotente desde `admin/carta.php` la primera vez que se abre. Si `nombre_en` está vacío, el sitio público en EN cae al `nombre` español.

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

Las credenciales se cargan desde **`/etc/tuoi/db.php`**, un archivo fuera del webroot y del repositorio git. Nunca se editan credenciales en `conexion.php`.

```php
// /etc/tuoi/db.php — en cada servidor, con sus propias credenciales
return [
    'host'     => 'localhost',
    'user'     => 'tuoi_admin2026',
    'password' => '...',
    'database' => 'tuoi_db',
];
```

```php
// config/conexion.php — carga el archivo externo
$_db = require '/etc/tuoi/db.php';
$conexion = mysqli_connect($_db['host'], $_db['user'], $_db['password'], $_db['database']);
if (!$conexion) {
    $error_db = "Error de conexión: " . mysqli_connect_error();
} else {
    mysqli_set_charset($conexion, "utf8mb4");
}
unset($_db);
```

Si `/etc/tuoi/db.php` no existe, `conexion.php` escribe el error en `$error_db` y retorna sin bloquear la ejecución.

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
- **CTA Instagram:** banner oscuro con icono de Instagram, texto editable (`ig_cta_text`) y botón con enlace editable (`ig_cta_url`) que abre Instagram en pestaña nueva

Todo el texto viene de `load_site_content()`.

### `pages/quienes-somos.php` — Quiénes somos

Tres bloques de contenido (`qs_page_b1_*`, `qs_page_b2_*`, `qs_page_b3_*`) + cierre con CTA a la carta. Texto editable desde admin.

### `pages/carta/` — La carta

La carta se renderiza desde la tabla `carta_items` (no desde imágenes sueltas).

**`pages/carta/index.php`** es la página principal y soporta el parámetro `?cat=<slug>`. Sin parámetro carga la primera categoría definida en `$CARTA_CATEGORIAS` (`config/carta_meta.php`). Por cada categoría:

1. Carga título y subtítulo desde `$carta_info` (`lang.php`) según el idioma activo.
2. Consulta `carta_items WHERE categoria=… AND visible=1 ORDER BY sort_order, id`.
3. Agrupa ítems consecutivos por `subcategoria` para renderizar bloques con sub-cabecera.
4. Para cada ítem, si `$lang === 'en'` y existen los campos `_en`, los usa; si no, cae al ES.

**Páginas-shortcut por categoría** (`desayunos.php`, `bebidas.php`, etc.): mantienen URLs limpias y usan el template legacy `includes/carta-page.php`, que sigue siendo válido para el caso en que se quiera servir solo imágenes (fallback). En la práctica los enlaces internos del sitio apuntan a `index.php?cat=…`.

**CTA de Instagram en Menú lunch:** `carta-page.php` incluye condicionalmente (solo cuando `$current_carta === 'menu-lunch'`) el mismo bloque de CTA de Instagram de la home. El texto y la URL se toman de las mismas claves `ig_cta_text` e `ig_cta_url`.

**Datos de cada ítem en pantalla:** nombre, descripción, ingredientes (si existen), precio (formateado `X,XX €` o `+X,XX €` si `es_suplemento=1`), foto (si existe), badges de alérgenos (icono + label de `$CARTA_ALERGENOS`).

### `pages/eventos/` — Eventos (hub + 2 sub-páginas)

`pages/eventos/index.php` actúa como hub y presenta dos opciones que llevan a las sub-páginas:

- **`eventos-listos/`** — "Listos para disfrutar": 6 experiencias prediseñadas (Coffee Break, Social Cocktail, Table Experience × Essential/Signature) + bloque de servicio incluido + condiciones de contratación.
- **`a-tu-medida/`** — "Diseñado a tu medida": placeholder hasta que se defina el contenido final.

Incluye además:
- Hero con CTAs (CTA principal lleva a `/pages/contacto/`)
- Carrusel de imágenes (de `eventos/carrusel/`)
- Manifiesto / "Nuestra filosofía" (intro narrativa)
- Sección "Por qué TUOI" (4 bloques)
- Prueba social (testimonio + logos de clientes)
- Marquee animado de tipos de eventos
- CTA final con enlace a contacto

### `pages/contacto/index.php` — Contacto

Página única con el formulario de contacto del sitio. Antes existían CTAs con `?from=eventos`, `?from=eventos-listos`, etc. para diferenciar origen; **esto se eliminó en mayo 2026**: ahora todos los enlaces apuntan limpiamente a `/pages/contacto/` y el handler ya no escribe la columna `source_page`.

**Flujo del formulario** (`includes/contact-handler.php` + `includes/contact-form.php`):

- Validación de campos (nombre, email, mensaje, consentimiento RGPD)
- Honeypot anti-spam: campo oculto `c_website`; si llega con valor, se simula éxito sin procesar ni guardar
- Soporte AJAX: si la petición lleva `X-Requested-With: XMLHttpRequest`, responde JSON
- Los envíos se guardan en `contact_submissions` (con `consent_at` + `consent_ip`)
- Email de aviso al admin con asunto `Nuevo contacto · TUOI`. En entorno local (host `localhost`/`.local`) se loguea en `logs/mail.log` en lugar de enviar
- `includes/contact-form.php` es reutilizable: si necesitas el formulario en otra página basta con `require` del handler antes del header y luego del form donde quieras

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
5. Define los helpers de CSRF, el helper `upsert_content()` y los helpers de rol:

```php
function is_admin(): bool {
    return ($_SESSION['admin_role'] ?? '') === 'admin';
}

function require_admin(): void {
    if (!is_admin()) { http_response_code(403); exit('Sin permisos.'); }
}
```

### CSRF

- `csrf_token()` genera y persiste un token de 32 bytes en sesión
- `csrf_field()` devuelve un `<input type="hidden">` listo para insertar en formularios
- `csrf_check()` verifica el token en cada POST (comparación con `hash_equals` para evitar timing attacks)
- La verificación es automática: cualquier POST a una página que incluya `config.php` es comprobado

### Secciones del admin

| Página | Función | Rol mínimo |
|---|---|---|
| `index.php` | Dashboard con resumen y accesos rápidos | Editor |
| `contenido.php` | Editor de textos del sitio (ES y EN) organizado por secciones | Editor |
| `carta.php` | Gestor de la carta plato a plato (CRUD + reorden + traducción EN) | Editor |
| `imagenes.php` | Subida, reordenación y borrado de imágenes por sección | Editor |
| `testimonios.php` | Gestión de testimonios de la página de Eventos | Editor |
| `mensajes.php` | Bandeja de entrada de contacto (con filtros y borrado) | Editor |
| `usuarios.php` | Gestión de usuarios (crear, eliminar, cambiar rol/contraseña) | Admin (parcial: editor puede ver y cambiar su propia contraseña) |
| `logout.php` | Destruye la sesión y redirige a login | — |

### Editor de contenido (`contenido.php`)

- Hub por páginas: tarjetas para `home`, `eventos`, `eventos-listos`, `eventos-medida`, `quienes` (`?section=…`)
- Selector de idioma por pestañas ES/EN (`?edit_lang=es|en`); si una clave EN está vacía el sitio cae al ES
- Por cada clave de texto renderiza un `<textarea>` (con Quill como editor rico en los campos largos) o `<input>`
- Al guardar hace `upsert_content()` para cada campo modificado
- Los campos de la variante EN editan claves con sufijo `_en` en la tabla `site_content`

### Gestor de la carta (`carta.php`)

CRUD completo de `carta_items`. Estructura del UI:

- **Tabs de idioma** ES / EN: en ES se edita todo, en EN solo los campos traducibles (`nombre_en`, `descripcion_en`, `ingredientes_en`, `subcategoria_en`). El formulario EN muestra el original ES como referencia.
- **Pills de categoría** (de `$CARTA_CATEGORIAS`): filtran la lista y determinan a qué categoría se añaden los ítems nuevos. Cada pill muestra el contador de ítems.
- **Formulario de alta** (solo en tab ES) con: sub-grupo (con autocompletado de los existentes), nombre, precio, flag `es_suplemento`, descripción, ingredientes, chips de alérgenos, foto y visibilidad.
- **Lista de ítems** agrupada por sub-categoría, con drag-and-drop para reordenar. Cada fila muestra: thumbnail, nombre + precio, descripción truncada, badges de alérgenos, pill de visibilidad, pill de traducción EN, y acciones (editar / toggle / eliminar).
- **Rename inline** de un sub-grupo: actualiza el campo `subcategoria` en bloque para todos los ítems del grupo.
- **Reparar orden**: cuando se detectan `sort_order` duplicados, aparece un botón que reasigna 0,1,2,… preservando el orden actual.
- **Subida de foto**: pipeline compartido con `imagenes.php` — conversión a WebP, redimensionado a 1400 px máx., límite de 20 MB. Las fotos se guardan en `assets/img/carta/items/` con nombre único.
- **Traducción inicial**: el script `admin/sql/carta_translations_en.sql` (mayo 2026) contiene las traducciones EN de los 115 ítems iniciales. Se ejecutó una sola vez; cualquier ítem nuevo o cambio se traduce desde el admin.

---

## 9. Gestión de usuarios admin

**Archivo:** `admin/usuarios.php`

### Sistema de roles

| Rol | Descripción |
|---|---|
| `admin` | Acceso completo: puede crear usuarios, eliminarlos y cambiar roles |
| `editor` | Puede editar todo el contenido del sitio pero no gestionar usuarios (excepto su propia contraseña) |

El rol se guarda en `admin_users.role` y se carga en sesión (`$_SESSION['admin_role']`) al hacer login. Los helpers `is_admin()` y `require_admin()` (definidos en `admin/config.php`) permiten proteger operaciones sensibles en cualquier página del admin.

### Flujo de login con rol

```
POST /admin/login.php
  → SELECT id, password_hash, role FROM admin_users WHERE username = ?
  → password_verify()
  → $_SESSION['admin_role'] = $user['role'] ?? 'editor'
```

### Auto-migración de la columna `role`

`admin/usuarios.php` añade la columna `role` automáticamente si la tabla existe sin ella (instalaciones anteriores a junio 2026). El primer usuario existente hereda el rol `admin`.

### Operaciones disponibles

| Operación | Admin | Editor |
|---|---|---|
| Ver lista de usuarios | ✅ | ✅ |
| Cambiar su propia contraseña | ✅ | ✅ |
| Cambiar contraseña de otro usuario | ✅ | ❌ |
| Crear nuevo usuario | ✅ | ❌ |
| Cambiar rol de un usuario | ✅ (no el propio) | ❌ |
| Eliminar usuario | ✅ (no el propio, no si es el único) | ❌ |

### Seguridad de contraseñas

- Longitud mínima: 8 caracteres
- Almacenamiento: `password_hash($pass, PASSWORD_DEFAULT)` (bcrypt)
- Todas las operaciones usan sentencias preparadas (`mysqli_prepare`)

---

## 10. Gestión de imágenes

**Admin:** `admin/imagenes.php` + `admin/partials/image_utils.php`

### Secciones de imágenes

| Slug | Carpeta en disco | Uso |
|---|---|---|
| `carteles` | `assets/img/carteles/` | Logos Balance/Energy/Focus/Power |
| `quienes_somos` | `assets/img/quienes_somos/` | Foto de "Quiénes somos" |
| `inicio` | `assets/img/` | Imagen principal del hero |
| `eventos/carrusel` | `assets/img/eventos/carrusel/` | Carrusel de Eventos |
| `eventos/por-que-tuoi` | `assets/img/eventos/por-que-tuoi/` | Bloque "Por qué TUOI" |
| `eventos/logos` | `assets/img/eventos/logos/` | Logos de clientes |
| `eventos/cta-fondo` | `assets/img/eventos/cta-fondo/` | Fondo del CTA de Eventos |

> **Carta**: las fotos de los ítems no se gestionan desde `imagenes.php` sino desde `admin/carta.php` (campo *Foto* de cada ítem). Se guardan todas juntas en `assets/img/carta/items/` con nombre único y se referencian desde la columna `foto` de `carta_items`. Las antiguas carpetas `carta/<categoria>/` y `carta/<categoria>-en/` del modelo basado en imágenes han quedado obsoletas con la migración a `carta_items`.

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

## 11. Frontend: CSS y JavaScript

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
| 5 | Formulario de contacto | Envío AJAX con validación de campos y mensaje de éxito/error (página `/pages/contacto/`) |

### Cache-busting del CSS

El header añade automáticamente `?v=<filemtime>` a la URL del CSS para forzar la descarga cuando el archivo cambia:

```php
$css_v = @filemtime(dirname(__DIR__) . '/assets/css/style.css') ?: time();
// → <link href="style.css?v=1714000000">
```

---

## 12. Seguridad

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

### Credenciales de base de datos

Las credenciales viven en `/etc/tuoi/db.php`, **fuera del webroot y del repositorio git**. El historial de git fue limpiado (junio 2026) para eliminar credenciales anteriores. El archivo de configuración nunca debe commitearse; está protegido por `.gitignore`.

### Control de acceso por rol

- Dos roles: `admin` y `editor` (ver sección 9)
- El rol se verifica en cada operación sensible con `is_admin()` tanto en el servidor (PHP) como en la UI (botones ocultos para editores)
- Un editor no puede escalar privilegios: todas las restricciones se comprueban server-side

### Puntos de mejora pendientes (ver TO-DO)

_(Las credenciales de BD ya están fuera del repo desde junio 2026)_

---

## 13. Guía de despliegue

### Requisitos del servidor

- PHP ≥ 8.0 con extensiones: `mysqli`, `gd` (para WebP), `mbstring`
- MySQL ≥ 5.7 o MariaDB ≥ 10.3
- Apache con `mod_rewrite` activo

### Pasos de instalación

```bash
# 1. Clonar el repositorio en el servidor
git clone https://github.com/fabianasoti/TUOI.git /var/www/html/tuoi/

# 2. Crear el archivo de credenciales FUERA del webroot
sudo mkdir -p /etc/tuoi
sudo tee /etc/tuoi/db.php << 'EOF'
<?php
return [
    'host'     => 'localhost',
    'user'     => 'tuoi_produccion',     # usuario MySQL de producción
    'password' => 'contraseña_segura',   # contraseña de producción
    'database' => 'tuoi_db',
];
EOF
sudo chmod 640 /etc/tuoi/db.php
sudo chown www-data:www-data /etc/tuoi/db.php

# 3. Ejecutar los scripts SQL en orden
mysql -u root -p < db/tuoi_db.sql
mysql -u root -p < db/admin_migration.sql
mysql -u root -p < db/rgpd_migration.sql
mysql -u root -p < db/eventos_textos_update.sql

# 3b. Importar el contenido inicial (textos, carta, testimonios, orden de imágenes)
mysql -u root -p tuoi_db < db/content_seed.sql

# 4. Crear el primer usuario admin
# Genera el hash primero:
php -r "echo password_hash('tu_contraseña', PASSWORD_DEFAULT);"
# Luego inserta en MySQL:
# INSERT INTO admin_users (username, role, password_hash)
# VALUES ('admin', 'admin', 'HASH_GENERADO');
# (O entra al panel en /admin/usuarios.php una vez dentro)

# 5. Dar permisos de escritura al servidor sobre la carpeta de imágenes
# (necesario para que el admin pueda subir fotos nuevas)
chmod -R 775 /var/www/html/tuoi/assets/img/
chown -R www-data:www-data /var/www/html/tuoi/assets/img/
```

> **Nota:** `config/conexion.php` no contiene credenciales. El único archivo con datos sensibles es `/etc/tuoi/db.php`, que el sysadmin crea manualmente en el servidor y nunca entra al repositorio.

> **Imágenes:** la carpeta `assets/img/` **sí está en el repositorio git** (no está en `.gitignore`), por lo que el `git clone` del paso 1 ya las incluye. No hace falta transferirlas por separado.

### Verificación

- `/` → Home carga con texto por defecto
- `/admin/login.php` → Login funciona
- `/admin/imagenes.php` → Se puede subir una imagen y se convierte a WebP
- Selector ES/EN en el header → cambia el idioma

---

## 14. Tareas pendientes (TO-DO)

| Prioridad | Descripción | Archivo |
|---|---|---|
| 🟡 Media | Configurar `secure=true` en la cookie de sesión admin también en desarrollo local (o usar HTTPS con cert autofirmado) | `admin/config.php` |
| 🟡 Media | Añadir rate-limiting al formulario de contacto (p.ej. por IP en `contact_submissions`) | `includes/contact-handler.php` |
| 🟡 Media | Modo edición inline en el sitio público: clic en el título → input flotante (fase 2 del rediseño admin) | — |
| 🟢 Baja | Internacionalizar los textos de la carta con más granularidad (actualmente solo título y descripción de categoría) | `config/lang.php` |
| 🟢 Baja | Añadir sitemap.xml y meta tags Open Graph | `includes/header.php` |
| 🟢 Baja | Migrar estilos del `mockup.html` a `style.css` (rediseño visual aprobado: Fraunces + General Sans, bento cards, grain) | `assets/css/style.css` |

---

*Documentación generada en Mayo 2026 · Revisión 01-06-2026: credenciales BD movidas fuera del repo, sistema de roles admin (admin/editor), nueva página `admin/usuarios.php`, `.gitignore` añadido, historial de git limpiado.*
