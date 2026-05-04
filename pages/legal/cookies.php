<?php
$base         = '../../';
$current_page = 'legal';
require $base . 'config/conexion.php';
require_once $base . 'config/lang.php';
$page_title = $lang === 'en' ? 'Cookies Policy | TUOI' : 'Política de Cookies | TUOI';
require $base . 'includes/header.php';
?>

<main>
<section class="page-hero">
    <span class="section-label"><?= $lang === 'en' ? 'Legal' : 'Legal' ?></span>
    <h1><?= $lang === 'en' ? 'Cookies Policy' : 'Política de Cookies' ?></h1>
    <p><?= $lang === 'en' ? 'How this website uses cookies.' : 'Cómo utiliza cookies este sitio web.' ?></p>
</section>

<article class="legal-page">
<?php if ($lang === 'en'): ?>

<h2>1. What is a cookie?</h2>
<p>A cookie is a small text file that a website stores in the user's browser in order to remember information about their visit, such as preferences or browsing data.</p>

<h2>2. Cookies used on this website</h2>
<p>This website only uses <strong>strictly necessary technical cookies</strong>. According to Spanish law (LSSI-CE) and the criteria of the Spanish Data Protection Agency (AEPD), these cookies do <strong>not</strong> require prior consent.</p>

<table class="legal-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Purpose</th>
            <th>Type</th>
            <th>Duration</th>
            <th>Owner</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><code>tuoi_lang</code></td>
            <td>Stores the user's language preference (Spanish / English).</td>
            <td>Functional · necessary</td>
            <td>1 year</td>
            <td>Own (tuoi.es)</td>
        </tr>
    </tbody>
</table>

<h2>3. Third-party cookies</h2>
<p>This website does <strong>not</strong> use analytics, advertising, profiling or social-network cookies. There are no third-party tracking scripts (Google Analytics, Meta Pixel, etc.). All resources (typography, images, scripts) are served from our own domain.</p>

<h2>4. How to disable cookies</h2>
<p>You can configure your browser to block or delete cookies at any time. Below are the official guides for the most common browsers:</p>
<ul>
    <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener">Google Chrome</a></li>
    <li><a href="https://support.mozilla.org/kb/borrar-cookies" target="_blank" rel="noopener">Mozilla Firefox</a></li>
    <li><a href="https://support.apple.com/guide/safari/manage-cookies-sfri11471/mac" target="_blank" rel="noopener">Safari</a></li>
    <li><a href="https://support.microsoft.com/microsoft-edge" target="_blank" rel="noopener">Microsoft Edge</a></li>
</ul>
<p>Please note that disabling the technical cookies may prevent some basic features of the website (such as the language switcher) from working correctly.</p>

<h2>5. Updates</h2>
<p>This Cookies Policy may be updated to adapt to legal or operational changes. The latest version will always be available on this page.</p>

<p class="legal-updated"><em>Last updated: <?= date('d/m/Y') ?></em></p>

<?php else: ?>

<h2>1. ¿Qué es una cookie?</h2>
<p>Una cookie es un pequeño archivo de texto que un sitio web almacena en el navegador del usuario con el fin de recordar información sobre su visita, como preferencias o datos de navegación.</p>

<h2>2. Cookies utilizadas en este sitio</h2>
<p>Este sitio web utiliza únicamente <strong>cookies técnicas estrictamente necesarias</strong>. De acuerdo con la LSSI-CE y los criterios de la Agencia Española de Protección de Datos (AEPD), estas cookies <strong>no requieren consentimiento previo</strong>.</p>

<table class="legal-table">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Finalidad</th>
            <th>Tipo</th>
            <th>Duración</th>
            <th>Titular</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><code>tuoi_lang</code></td>
            <td>Almacena la preferencia de idioma del usuario (Español / Inglés).</td>
            <td>Funcional · necesaria</td>
            <td>1 año</td>
            <td>Propia (tuoi.es)</td>
        </tr>
    </tbody>
</table>

<h2>3. Cookies de terceros</h2>
<p>Este sitio web <strong>no</strong> utiliza cookies analíticas, publicitarias, de elaboración de perfiles o de redes sociales. No existen scripts de seguimiento de terceros (Google Analytics, Meta Pixel, etc.). Todos los recursos (tipografías, imágenes, scripts) se sirven desde nuestro propio dominio.</p>

<h2>4. Cómo desactivar las cookies</h2>
<p>Puedes configurar tu navegador para bloquear o eliminar las cookies en cualquier momento. A continuación facilitamos las guías oficiales de los navegadores más habituales:</p>
<ul>
    <li><a href="https://support.google.com/chrome/answer/95647?hl=es" target="_blank" rel="noopener">Google Chrome</a></li>
    <li><a href="https://support.mozilla.org/es/kb/borrar-cookies" target="_blank" rel="noopener">Mozilla Firefox</a></li>
    <li><a href="https://support.apple.com/es-es/guide/safari/manage-cookies-sfri11471/mac" target="_blank" rel="noopener">Safari</a></li>
    <li><a href="https://support.microsoft.com/es-es/microsoft-edge" target="_blank" rel="noopener">Microsoft Edge</a></li>
</ul>
<p>Ten en cuenta que la desactivación de las cookies técnicas puede impedir el correcto funcionamiento de algunas funcionalidades básicas del sitio (por ejemplo, el selector de idioma).</p>

<h2>5. Actualizaciones</h2>
<p>Esta Política de Cookies puede actualizarse para adaptarse a cambios legales u operativos. La última versión estará siempre disponible en esta página.</p>

<p class="legal-updated"><em>Última actualización: <?= date('d/m/Y') ?></em></p>

<?php endif; ?>
</article>
</main>

<?php require $base . 'includes/footer.php'; ?>
