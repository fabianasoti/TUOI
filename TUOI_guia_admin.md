# Guía del Panel de Administración — TUOI Web

> Para el equipo de TUOI con acceso al panel de gestión de la web.  
> No hace falta saber programar para usar esta guía.

---

## Índice

1. [Acceder al panel](#1-acceder-al-panel)
2. [El dashboard (inicio)](#2-el-dashboard-inicio)
3. [Editar textos del sitio](#3-editar-textos-del-sitio)
4. [Gestionar imágenes](#4-gestionar-imágenes)
5. [Testimonios de clientes](#5-testimonios-de-clientes)
6. [Mensajes de contacto](#6-mensajes-de-contacto)
7. [Traducción al inglés](#7-traducción-al-inglés)
8. [Ver cómo queda en la web](#8-ver-cómo-queda-en-la-web)
9. [Cerrar sesión](#9-cerrar-sesión)
10. [Preguntas frecuentes](#10-preguntas-frecuentes)

---

## 1. Acceder al panel

Abre el navegador y ve a la dirección del panel admin (tu responsable te habrá dado la URL exacta, algo como `tuoicoffee.com/admin`).

Verás una pantalla de inicio de sesión:

```
Usuario:    [ tu usuario ]
Contraseña: [ tu contraseña ]
```

Escribe tus credenciales y pulsa **Entrar →**

> ⚠️ Si introduces mal la contraseña tres veces seguidas, espera un momento antes de volver a intentarlo. Si no recuerdas la contraseña, contacta con el responsable técnico.

---

## 2. El dashboard (inicio)

Al entrar verás el panel principal con:

- **Bloques de texto** — cuántos textos del sitio están guardados en la base de datos.
- **Imágenes totales** — número de imágenes subidas en todas las secciones.
- **Secciones de imágenes** — cuántas carpetas de imágenes hay.

Debajo hay **accesos rápidos** a las cuatro acciones más habituales:

| Acceso rápido | Para qué sirve |
|---|---|
| ✏️ Editar contenido | Cambiar textos, títulos y descripciones del sitio |
| 🖼️ Gestionar imágenes | Subir, reordenar o borrar fotos |
| 🌐 Ver el sitio | Abre la web pública en una pestaña nueva |
| 🍽️ Ver la carta | Abre directamente la sección de menú |

También hay una tabla al final del dashboard con un resumen de cuántas imágenes hay en cada sección, para que de un vistazo sepas qué secciones tienen contenido y cuáles están vacías.

---

## 3. Editar textos del sitio

Ve a **Editar contenido** desde el menú lateral o desde el acceso rápido del dashboard.

### Seleccionar la sección a editar

Los textos están organizados por sección de la web. Verás un menú en la parte superior con las opciones:

| Sección | Qué contiene |
|---|---|
| **Inicio** | Hero principal, "¿Quiénes somos?" preview, tarjetas de filosofía (Balance, Energy, Focus, Power) y valores |
| **Eventos** | Página de eventos: hero, intro, "¿Por qué TUOI?", menús disponibles, CTA |
| **Experiencias listas** | Textos de la subpágina de eventos con menús predefinidos |
| **Eventos a medida** | Textos de la subpágina de eventos personalizados |
| **Quiénes somos** | Todos los textos de la página "Quiénes somos" |

Haz clic en la sección que quieras editar.

### Editar un campo

Cada texto aparece como un campo editable con su nombre. Simplemente haz clic sobre él, borra lo que hay y escribe el nuevo texto.

- Los campos **pequeños** (una línea) son para títulos cortos, etiquetas, CTAs.
- Los campos **grandes** (varias líneas) son para párrafos y descripciones.

### Guardar los cambios

Cuando hayas terminado de editar, baja al final de la página y pulsa el botón **Guardar sección**.

Verás un mensaje de confirmación en verde: *"Sección guardada — X campo(s) actualizado(s)."*

> ✅ Los cambios se reflejan en la web inmediatamente. Si no los ves, prueba a recargar la página del sitio con **Ctrl+F5** (o Cmd+Shift+R en Mac).

### ¿Puedo usar HTML en los textos?

Sí, en los campos de párrafo puedes usar etiquetas HTML básicas:

- `<strong>texto en negrita</strong>`
- `<a href="https://..." target="_blank">enlace</a>`
- `<br>` para forzar un salto de línea

Evita usar HTML complejo (tablas, scripts, etc.) en estos campos.

---

## 4. Gestionar imágenes

Ve a **Gestionar imágenes** desde el menú lateral.

### Elegir la sección

En la parte superior hay un desplegable o lista de secciones. Cada sección corresponde a una parte del sitio:

| Sección | Dónde aparecen las imágenes |
|---|---|
| **Carteles / Logos** | Logos de Balance, Energy, Focus, Power en el home |
| **Quiénes somos** | Foto del equipo o local en la página "Quiénes somos" |
| **Inicio (imagen principal)** | Imagen de fondo del hero de la portada |
| **Carta — Desayunos** | Fotos de platos en la categoría Desayunos (versión ES) |
| **Carta — Toque Salado** | Fotos de platos en Toque Salado (ES) |
| **Carta — Momento Dulce** | Fotos de platos en Momento Dulce (ES) |
| **Carta — Bebidas** | Fotos de bebidas (ES) |
| **Carta — Superalimentos** | Fotos de superalimentos (ES) |
| **Carta — Desayunos (EN)** | Fotos con texto en inglés para la versión EN |
| *(ídem para el resto de categorías EN)* | — |
| **Eventos — Carrusel** | Fotos que aparecen en el carrusel de la página de Eventos |
| **Eventos — Por qué TUOI** | Imágenes del bloque "¿Por qué TUOI?" |
| **Eventos — Logos clientes** | Logos de empresas que han contratado eventos |
| **Eventos — Fondo del CTA** | Imagen de fondo de la llamada a la acción de Eventos |

### Subir una imagen nueva

1. Selecciona la sección donde quieres subir.
2. Pulsa el botón **Seleccionar imagen** (o arrastra el archivo al área indicada).
3. Elige el archivo desde tu ordenador. Formatos aceptados: JPG, PNG, WebP, GIF.
4. Puedes subir varias imágenes a la vez.
5. Pulsa **Subir**.

> 📌 **Importante:** Las imágenes se convierten automáticamente a formato WebP al subirse. Esto hace la web más rápida. No tienes que hacer nada especial; es automático.

> 📌 El tamaño máximo por archivo es **20 MB**. Si la foto es más grande, redúcela antes de subirla.

> 📌 Si la imagen tiene más de 2000 píxeles de ancho o alto, se reducirá automáticamente para no ocupar espacio innecesario.

### Ver y gestionar las imágenes de una sección

Después de seleccionar una sección verás una galería con todas las imágenes subidas. Desde aquí puedes:

- **Reordenar** — arrastra las imágenes para cambiar el orden en que aparecen en la web.
- **Eliminar** — pulsa el botón de borrar (🗑️) junto a la imagen que quieras quitar. Se pedirá confirmación.

> ⚠️ Borrar una imagen es permanente. No hay papelera de reciclaje. Asegúrate antes de confirmar.

### Imágenes de la carta en inglés

Si tienes fotos con texto rotulado en inglés (por ejemplo, el nombre del plato escrito sobre la imagen), súbelas en la sección correspondiente con **(EN)** al final, por ejemplo **Carta — Desayunos (EN)**.

Si esa carpeta está vacía, la web mostrará automáticamente las imágenes en español como alternativa.

---

## 5. Testimonios de clientes

Ve a **Testimonios** desde el menú lateral. Aquí puedes gestionar las opiniones de clientes que aparecen en la página de Eventos.

### Añadir un testimonio nuevo

Rellena el formulario en la parte superior:

- **Cita** — el texto de la opinión (obligatorio).
- **Autor** — nombre de la persona (obligatorio).
- **Cargo / empresa** — puesto y empresa, por ejemplo *"People & Culture · Innovae"* (opcional).

Pulsa **Añadir testimonio**.

### Editar un testimonio

En la lista de testimonios, haz clic en **Editar** junto al que quieras modificar. Cambia los campos y guarda.

### Activar o desactivar un testimonio

Cada testimonio tiene un interruptor de **Activo / Inactivo**. Si lo desactivas, deja de mostrarse en la web sin necesidad de borrarlo. Útil si quieres ocultarlo temporalmente.

### Traducción al inglés

Para añadir la versión en inglés de un testimonio, cambia al modo **EN** con el selector de idioma en la parte superior. Verás los mismos testimonios pero con campos para la cita y el cargo en inglés. El nombre del autor no se traduce.

### Reordenar

Arrastra los testimonios para cambiar el orden en que aparecen en la web.

### Borrar un testimonio

Pulsa el botón **Eliminar** junto al testimonio. Se pedirá confirmación. Esta acción es permanente.

---

## 6. Mensajes de contacto

Ve a **Mensajes** desde el menú lateral. Aquí ves todos los mensajes enviados desde el formulario de contacto de la página de Eventos.

### Leer un mensaje

Los mensajes no leídos aparecen resaltados. Haz clic en el mensaje para ver el contenido completo: nombre, email, teléfono (si lo dejaron), mensaje y fecha de envío.

### Filtrar mensajes

En la parte superior puedes filtrar entre:
- **Todos** — muestra todos los mensajes.
- **No leídos** — muestra solo los que aún no has revisado.

### Marcar como leído / no leído

Pulsa el botón **Marcar como leído** (o **No leído**) junto a cada mensaje para cambiar su estado. También hay un botón **Marcar todos como leídos** si tienes muchos mensajes nuevos.

### Borrar un mensaje

Pulsa **Eliminar** junto al mensaje. Se pedirá confirmación. Una vez borrado no se puede recuperar.

> 💡 Recomendamos copiar el email o teléfono del cliente a vuestra herramienta habitual (CRM, agenda, etc.) antes de borrar el mensaje, ya que los mensajes no se reenvían por correo automáticamente.

---

## 7. Traducción al inglés

La web de TUOI está disponible en español e inglés. Los visitantes pueden cambiar de idioma con el selector **ES / EN** del menú.

Tanto en el editor de textos como en testimonios, hay un selector de idioma en la parte superior:

```
[ Español ]  [ English ]
```

- **Español** — editas los textos principales del sitio.
- **English** — editas la versión en inglés de los mismos textos.

### ¿Qué pasa si no traduzco un texto?

No pasa nada grave. Si un texto no tiene versión en inglés, la web mostrará automáticamente el texto en español para ese campo. Así que puedes ir traduciendo poco a poco sin que la web se rompa.

### Imágenes en inglés

Si las imágenes de la carta tienen texto escrito encima (por ejemplo el nombre del plato en español), necesitarás subir versiones alternativas con el texto en inglés en las carpetas **"(EN)"** del gestor de imágenes.

---

## 8. Ver cómo queda en la web

Desde cualquier página del panel admin puedes pulsar el botón **🌐 Ver sitio** (arriba a la derecha) para abrir la web pública en una nueva pestaña y comprobar cómo se ven tus cambios.

También puedes ir directamente a cualquier página del sitio:

- **Inicio:** `tuoicoffee.com/`
- **Carta:** `tuoicoffee.com/pages/carta/`
- **Eventos:** `tuoicoffee.com/pages/eventos/`
- **Quiénes somos:** `tuoicoffee.com/pages/quienes-somos.php`

> 💡 Si acabas de guardar cambios y no los ves, recarga la página con **Ctrl+F5** (Windows/Linux) o **Cmd+Shift+R** (Mac) para forzar la descarga de la versión nueva.

---

## 9. Cerrar sesión

Cuando termines, cierra siempre la sesión para evitar que otra persona acceda al panel desde el mismo ordenador.

Pulsa **Cerrar sesión** en la parte inferior del menú lateral.

> ⚠️ Si dejas el navegador abierto y no hay actividad durante un tiempo prolongado, la sesión se cerrará automáticamente por seguridad.

---

## 10. Preguntas frecuentes

**¿Puedo romper la web si cambio algo mal?**  
Los textos siempre tienen un valor por defecto guardado en el sistema. Si borras el contenido de un campo y guardas, el sitio puede quedar con ese campo vacío, pero no se "rompe". Para restaurar un texto, simplemente vuelve al editor y escríbelo de nuevo.

**¿Las imágenes que borro se pueden recuperar?**  
No. Una vez borrada una imagen del gestor, se elimina del servidor permanentemente. Si necesitas recuperarla, tendrás que volver a subirla.

**Subí una imagen y no aparece en la web, ¿qué hago?**  
Primero comprueba que has subido la imagen en la sección correcta. Luego recarga la página del sitio con Ctrl+F5. Si sigue sin aparecer, es posible que la carpeta de la sección esté vacía o que la imagen no se haya subido correctamente; intenta volver a subirla.

**¿Puedo tener varias personas editando a la vez?**  
Técnicamente sí, pero no es recomendable editar la misma sección de textos simultáneamente desde dos ordenadores diferentes, ya que los cambios del último en guardar sobreescribirán los del anterior.

**¿Cómo cambio mi contraseña?**  
La gestión de contraseñas no está disponible desde el panel de usuario actual. Contacta con el responsable técnico para que la cambie.

**¿Se guarda un historial de cambios?**  
No hay historial de versiones. Cada vez que guardas, el texto anterior se sobreescribe. Si necesitas recuperar una versión antigua de un texto, tendrás que recordarlo o pedírselo al responsable técnico.

---

*Guía elaborada en Mayo 2026 · Para dudas o problemas técnicos, contacta con el equipo de desarrollo.*
