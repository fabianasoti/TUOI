<?php
$base         = '../../';
$current_page = 'legal';
require $base . 'config/conexion.php';
require_once $base . 'config/lang.php';
$page_title = $lang === 'en' ? 'Privacy Policy | TUOI' : 'Política de Privacidad | TUOI';
require $base . 'includes/header.php';
?>

<main>
<section class="page-hero">
    <span class="section-label"><?= $lang === 'en' ? 'Legal' : 'Legal' ?></span>
    <h1><?= $lang === 'en' ? 'Privacy Policy' : 'Política de Privacidad' ?></h1>
    <p><?= $lang === 'en' ? 'How we handle your personal data.' : 'Cómo tratamos tus datos personales.' ?></p>
</section>

<article class="legal-page">
<?php if ($lang === 'en'): ?>

<h2>1. Data Controller</h2>
<p>In accordance with Regulation (EU) 2016/679 (GDPR) and the Spanish Organic Law 3/2018 on Personal Data Protection, the controller of the personal data collected through this website is:</p>
<ul>
    <li><strong>Company name:</strong> Alimentación y Vida SL — commercial name «MIOBIO» (operates the TUOI brand and website)</li>
    <li><strong>Tax ID (NIF):</strong> B67728113</li>
    <li><strong>Registered office:</strong> Avd. del Cid 124, 46014 Valencia, Spain</li>
    <li><strong>Phone:</strong> 606 014 121</li>
    <li><strong>Email:</strong> hola@miobiosport.com</li>
    <li><strong>Website:</strong> https://tuoi.es</li>
</ul>

<h2>2. Purposes of processing</h2>
<p>We process your personal data for the following purposes:</p>
<ul>
    <li>To respond to enquiries, requests for quotes and event bookings sent through our contact form.</li>
    <li>To manage the commercial relationship and the provision of our catering, events and food services.</li>
    <li>To comply with the legal obligations applicable to the company (tax, accounting and commercial law).</li>
</ul>
<p>We do <strong>not</strong> use your data for behavioural profiling or advertising automation, nor do we sell or rent your data to third parties.</p>

<h2>3. Legal basis</h2>
<ul>
    <li><strong>Consent</strong> of the data subject when submitting the contact form (Art. 6.1.a GDPR).</li>
    <li><strong>Performance of a contract</strong> or pre-contractual measures requested by you (Art. 6.1.b GDPR).</li>
    <li><strong>Legal obligation</strong> of the controller (Art. 6.1.c GDPR), where applicable.</li>
</ul>

<h2>4. Data we collect</h2>
<p>Through the contact form we collect: name, email address, phone number (optional) and the content of your message. We also store the submission date and the page from which the form was sent. The website only stores a technical cookie to remember your language preference.</p>

<h2>5. Recipients</h2>
<p>The data is only accessed by authorised personnel of Alimentación y Vida SL. It will only be shared with third parties when there is a legal obligation to do so (for example, the Spanish Tax Agency or banks for invoicing purposes) or with service providers acting as data processors under contract:</p>
<ul>
    <li><strong>Arsys Internet S.L.U.</strong> (Spain) — provider of web hosting and corporate email. Acts as data processor under Art. 28 GDPR.</li>
</ul>
<p>No international transfers outside the European Economic Area take place.</p>

<h2>6. Retention period</h2>
<p>Contact form data is kept for <strong>up to 1 year after the last contact</strong>, unless an active commercial relationship arises from the enquiry, in which case the data will be kept for the duration of the relationship and, after that, for the legally required periods (typically up to 6 years for accounting and tax purposes under Spanish law). After those periods, the data is securely deleted.</p>

<h2>7. Your rights</h2>
<p>You may exercise the following rights at any time, free of charge, by writing to <a href="mailto:hola@miobiosport.com">hola@miobiosport.com</a> with a copy of an ID document:</p>
<ul>
    <li>Access to your personal data.</li>
    <li>Rectification of inaccurate data.</li>
    <li>Erasure (the "right to be forgotten").</li>
    <li>Restriction of processing.</li>
    <li>Data portability.</li>
    <li>Objection to the processing.</li>
    <li>Withdrawal of consent at any time, without affecting the lawfulness of processing based on consent before its withdrawal.</li>
</ul>
<p>You also have the right to lodge a complaint with the Spanish Data Protection Agency (AEPD), C/ Jorge Juan 6, 28001 Madrid — <a href="https://www.aepd.es" target="_blank" rel="noopener">www.aepd.es</a>.</p>

<h2>8. Security measures</h2>
<p>The controller has adopted the technical and organisational measures required by current data protection legislation to safeguard the security and integrity of the data and to prevent its alteration, loss or unauthorised access.</p>

<h2>9. Changes to this policy</h2>
<p>This Privacy Policy may be updated to reflect legal or operational changes. The latest version will always be available on this page.</p>

<p class="legal-updated"><em>Last updated: <?= date('d/m/Y') ?></em></p>

<?php else: ?>

<h2>1. Responsable del tratamiento</h2>
<p>De conformidad con el Reglamento (UE) 2016/679 (RGPD) y la Ley Orgánica 3/2018 de Protección de Datos Personales y Garantía de los Derechos Digitales, el responsable de los datos personales recogidos a través de este sitio web es:</p>
<ul>
    <li><strong>Razón social:</strong> Alimentación y Vida SL — nombre comercial «MIOBIO» (titular de la marca y sitio web TUOI)</li>
    <li><strong>NIF:</strong> B67728113</li>
    <li><strong>Domicilio:</strong> Avd. del Cid 124, 46014 Valencia</li>
    <li><strong>Teléfono:</strong> 606 014 121</li>
    <li><strong>Email:</strong> hola@miobiosport.com</li>
    <li><strong>Sitio web:</strong> https://tuoi.es</li>
</ul>

<h2>2. Finalidades del tratamiento</h2>
<p>Tratamos tus datos personales con las siguientes finalidades:</p>
<ul>
    <li>Atender consultas, solicitudes de presupuesto y reservas de eventos enviadas a través del formulario de contacto.</li>
    <li>Gestionar la relación comercial y la prestación de nuestros servicios de catering, eventos y alimentación.</li>
    <li>Cumplir con las obligaciones legales aplicables a la empresa (fiscales, contables y mercantiles).</li>
</ul>
<p><strong>No</strong> utilizamos tus datos para elaborar perfiles publicitarios automatizados ni los vendemos o cedemos a terceros con fines comerciales.</p>

<h2>3. Base legal</h2>
<ul>
    <li><strong>Consentimiento</strong> del interesado al enviar el formulario de contacto (art. 6.1.a RGPD).</li>
    <li><strong>Ejecución de un contrato</strong> o medidas precontractuales solicitadas por ti (art. 6.1.b RGPD).</li>
    <li><strong>Obligación legal</strong> del responsable (art. 6.1.c RGPD), cuando proceda.</li>
</ul>

<h2>4. Datos que recogemos</h2>
<p>A través del formulario de contacto recogemos: nombre, correo electrónico, teléfono (opcional) y el contenido de tu mensaje. También almacenamos la fecha de envío y la página desde la que se ha enviado el formulario. El sitio web únicamente guarda una cookie técnica para recordar tu idioma preferido.</p>

<h2>5. Destinatarios</h2>
<p>Los datos sólo son accesibles por el personal autorizado de Alimentación y Vida SL. Únicamente se comunicarán a terceros cuando exista una obligación legal (por ejemplo, Agencia Tributaria o entidades bancarias para facturación) o a proveedores que actúen como encargados del tratamiento bajo contrato:</p>
<ul>
    <li><strong>Arsys Internet S.L.U.</strong> (España) — proveedor de hosting web y correo corporativo. Actúa como encargado del tratamiento conforme al art. 28 RGPD.</li>
</ul>
<p>No se realizan transferencias internacionales fuera del Espacio Económico Europeo.</p>

<h2>6. Plazo de conservación</h2>
<p>Los datos del formulario de contacto se conservan durante <strong>un máximo de 1 año desde el último contacto</strong>, salvo que la consulta dé lugar a una relación comercial activa, en cuyo caso se conservarán mientras dicha relación se mantenga y, posteriormente, durante los plazos legalmente exigidos (habitualmente hasta 6 años por motivos contables y fiscales). Transcurridos dichos plazos, los datos se suprimen de forma segura.</p>

<h2>7. Tus derechos</h2>
<p>Puedes ejercer en cualquier momento y de forma gratuita los siguientes derechos escribiendo a <a href="mailto:hola@miobiosport.com">hola@miobiosport.com</a> adjuntando copia de un documento identificativo:</p>
<ul>
    <li>Acceso a tus datos personales.</li>
    <li>Rectificación de los datos inexactos.</li>
    <li>Supresión («derecho al olvido»).</li>
    <li>Limitación del tratamiento.</li>
    <li>Portabilidad de los datos.</li>
    <li>Oposición al tratamiento.</li>
    <li>Revocación del consentimiento en cualquier momento, sin que ello afecte a la licitud del tratamiento previo a su retirada.</li>
</ul>
<p>Asimismo, tienes derecho a presentar una reclamación ante la Agencia Española de Protección de Datos (AEPD), C/ Jorge Juan 6, 28001 Madrid — <a href="https://www.aepd.es" target="_blank" rel="noopener">www.aepd.es</a>.</p>

<h2>8. Medidas de seguridad</h2>
<p>El responsable ha adoptado las medidas técnicas y organizativas exigidas por la normativa vigente en materia de protección de datos para garantizar la seguridad e integridad de los datos personales y evitar su alteración, pérdida o acceso no autorizado.</p>

<h2>9. Modificaciones</h2>
<p>Esta Política de Privacidad puede actualizarse para reflejar cambios legales u operativos. La última versión estará siempre disponible en esta página.</p>

<p class="legal-updated"><em>Última actualización: <?= date('d/m/Y') ?></em></p>

<?php endif; ?>
</article>
</main>

<?php require $base . 'includes/footer.php'; ?>
