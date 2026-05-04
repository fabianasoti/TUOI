<?php
$base         = '../../';
$current_page = 'legal';
require $base . 'config/conexion.php';
require_once $base . 'config/lang.php';
$page_title = $lang === 'en' ? 'Legal Notice | TUOI' : 'Aviso Legal | TUOI';
require $base . 'includes/header.php';
?>

<main>
<section class="page-hero">
    <span class="section-label"><?= $lang === 'en' ? 'Legal' : 'Legal' ?></span>
    <h1><?= $lang === 'en' ? 'Legal Notice' : 'Aviso Legal' ?></h1>
    <p><?= $lang === 'en' ? 'Terms of use of this website.' : 'Condiciones de uso del sitio web.' ?></p>
</section>

<article class="legal-page">
<?php if ($lang === 'en'): ?>

<h2>1. Owner of the website</h2>
<p>In compliance with Article 10 of Law 34/2002 on Information Society Services and Electronic Commerce (LSSI-CE), the owner of this website is:</p>
<ul>
    <li><strong>Company name:</strong> Alimentación y Vida SL — commercial name «MIOBIO» (operates the TUOI brand and website)</li>
    <li><strong>Tax ID (NIF):</strong> B67728113</li>
    <li><strong>Registered office:</strong> Avd. del Cid 124, 46014 Valencia, Spain</li>
    <li><strong>Phone:</strong> 606 014 121</li>
    <li><strong>Email:</strong> hola@miobiosport.com</li>
    <li><strong>Website:</strong> https://tuoi.es</li>
</ul>

<h2>2. Purpose</h2>
<p>This website provides information about the products and services offered by TUOI (catering, events and food services) and allows visitors to contact the company through a contact form.</p>

<h2>3. Conditions of use</h2>
<p>Use of this website grants the visitor the status of User and implies full acceptance of this Legal Notice, the Privacy Policy and the Cookies Policy. The User undertakes to use the website and its contents in accordance with the law, good faith, public order and these conditions.</p>
<p>The use of the contents for unlawful purposes or purposes that may damage the interests or rights of third parties, or that may in any way damage, disable or impair the website or prevent its normal use by other Users, is expressly forbidden.</p>

<h2>4. Intellectual and industrial property</h2>
<p>All rights are reserved. All contents of this website (texts, images, logos, trademarks, graphic design, source code, etc.) are the property of Alimentación y Vida SL or of third parties who have authorised their use, and are protected by intellectual and industrial property regulations. Their reproduction, distribution, public communication, transformation or any other form of exploitation is prohibited without prior written authorisation from the owner.</p>

<h2>5. Liability</h2>
<p>The owner of the website does not guarantee the absence of viruses or other harmful elements that could cause damage in the user's computer system. The owner is not liable for the contents of third-party websites linked from this site.</p>

<h2>6. Applicable law and jurisdiction</h2>
<p>This Legal Notice is governed by Spanish law. For any dispute arising from the use of the website, the parties submit to the courts and tribunals of Valencia, expressly waiving any other jurisdiction that may apply.</p>

<p class="legal-updated"><em>Last updated: <?= date('d/m/Y') ?></em></p>

<?php else: ?>

<h2>1. Titular del sitio web</h2>
<p>En cumplimiento del artículo 10 de la Ley 34/2002 de Servicios de la Sociedad de la Información y de Comercio Electrónico (LSSI-CE), se informa de que el titular de este sitio web es:</p>
<ul>
    <li><strong>Razón social:</strong> Alimentación y Vida SL — nombre comercial «MIOBIO» (titular de la marca y sitio web TUOI)</li>
    <li><strong>NIF:</strong> B67728113</li>
    <li><strong>Domicilio:</strong> Avd. del Cid 124, 46014 Valencia</li>
    <li><strong>Teléfono:</strong> 606 014 121</li>
    <li><strong>Email:</strong> hola@miobiosport.com</li>
    <li><strong>Sitio web:</strong> https://tuoi.es</li>
</ul>

<h2>2. Objeto</h2>
<p>Este sitio web proporciona información sobre los productos y servicios ofrecidos por TUOI (catering, eventos y servicios de alimentación) y permite a los visitantes ponerse en contacto con la empresa a través de un formulario.</p>

<h2>3. Condiciones de uso</h2>
<p>La utilización del sitio web atribuye a quien lo utilice la condición de Usuario e implica la aceptación plena del presente Aviso Legal, de la Política de Privacidad y de la Política de Cookies. El Usuario se compromete a utilizar el sitio web y sus contenidos de conformidad con la ley, la buena fe, el orden público y estas condiciones.</p>
<p>Queda expresamente prohibida la utilización de los contenidos con fines ilícitos o que puedan perjudicar los intereses o derechos de terceros, o que de cualquier forma puedan dañar, inutilizar o deteriorar el sitio web o impedir su normal disfrute por otros Usuarios.</p>

<h2>4. Propiedad intelectual e industrial</h2>
<p>Todos los derechos están reservados. Todos los contenidos del sitio web (textos, imágenes, logotipos, marcas, diseño gráfico, código fuente, etc.) son propiedad de Alimentación y Vida SL o de terceros que han autorizado su uso, y están protegidos por la normativa de propiedad intelectual e industrial. Queda prohibida su reproducción, distribución, comunicación pública, transformación o cualquier otra forma de explotación sin autorización previa y por escrito del titular.</p>

<h2>5. Responsabilidad</h2>
<p>El titular del sitio web no garantiza la inexistencia de virus u otros elementos lesivos que pudieran causar daños en el sistema informático del usuario. El titular no se hace responsable del contenido de los sitios web de terceros enlazados desde esta página.</p>

<h2>6. Legislación aplicable y jurisdicción</h2>
<p>El presente Aviso Legal se rige por la legislación española. Para cualquier controversia derivada del uso del sitio web, las partes se someten a los Juzgados y Tribunales de Valencia, con renuncia expresa a cualquier otro fuero que pudiera corresponderles.</p>

<p class="legal-updated"><em>Última actualización: <?= date('d/m/Y') ?></em></p>

<?php endif; ?>
</article>
</main>

<?php require $base . 'includes/footer.php'; ?>
