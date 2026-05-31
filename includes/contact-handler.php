<?php
/**
 * Handler del formulario de contacto.
 *
 * Se incluye ANTES de header.php para poder responder JSON a peticiones AJAX
 * sin haber emitido HTML todavía. Procesa POST cuando llega 'contact_submit'.
 *
 * Variables requeridas antes del include:
 *   $base        → ruta relativa hasta la raíz del proyecto
 *   $conexion    → conexión mysqli (puede ser falsy: el guardado se omite)
 *
 * Variables que deja disponibles tras incluirlo:
 *   $contact_success → bool
 *   $contact_errors  → array<string,string>  field => translation key
 */

require_once $base . 'config/lang.php';

$contact_success = false;
$contact_errors  = [];

$is_ajax = (
    !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {

    // Honeypot anti-spam: campo oculto que los humanos no ven. Si llega con
    // valor, es un bot: simulamos éxito silenciosamente.
    $is_bot = !empty(trim($_POST['c_website'] ?? ''));

    $name    = trim($_POST['c_name']    ?? '');
    $email   = trim($_POST['c_email']   ?? '');
    $phone   = trim($_POST['c_phone']   ?? '');
    $message = trim($_POST['c_message'] ?? '');
    $consent = isset($_POST['c_consent']);

    if (!$is_bot) {
        if ($name === '')                                  $contact_errors['c_name']    = 'ev_form_required';
        if ($email === '')                                 $contact_errors['c_email']   = 'ev_form_required';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $contact_errors['c_email']   = 'ev_form_email_bad';
        if ($message === '')                               $contact_errors['c_message'] = 'ev_form_required';
        if (!$consent)                                     $contact_errors['c_consent'] = 'ev_contact_consent_err';
    }

    if ($is_bot) {
        $contact_success = true;
        $_POST = [];
    } elseif (empty($contact_errors)) {
        if ($conexion) {
            // Migración idempotente de columnas RGPD.
            try { @mysqli_query($conexion, "ALTER TABLE contact_submissions ADD COLUMN consent_at DATETIME NULL DEFAULT NULL AFTER source_page"); } catch (\Throwable $e) {}
            try { @mysqli_query($conexion, "ALTER TABLE contact_submissions ADD COLUMN consent_ip VARCHAR(45) NULL DEFAULT NULL AFTER consent_at"); } catch (\Throwable $e) {}

            $n  = mysqli_real_escape_string($conexion, $name);
            $em = mysqli_real_escape_string($conexion, $email);
            $p  = mysqli_real_escape_string($conexion, $phone);
            $m  = mysqli_real_escape_string($conexion, $message);
            $ip = mysqli_real_escape_string($conexion, $_SERVER['REMOTE_ADDR'] ?? '');
            try {
                mysqli_query($conexion,
                    "INSERT INTO contact_submissions (name, email, phone, message, consent_at, consent_ip)
                     VALUES ('$n','$em','$p','$m', NOW(), '$ip')"
                );
            } catch (\Throwable $e) {
                @mysqli_query($conexion,
                    "INSERT INTO contact_submissions (name, email, phone, message)
                     VALUES ('$n','$em','$p','$m')"
                );
            }
        }

        // Email de aviso al admin.
        $c_local      = isset($c) && is_array($c) ? $c : [];
        $admin_email  = !empty($c_local['contact_email']) ? $c_local['contact_email'] : 'hola@miobiosport.com';
        $subject_text = 'Nuevo contacto · TUOI';
        $mail_subject = '=?UTF-8?B?' . base64_encode($subject_text) . '?=';
        $mail_body    = "Has recibido un nuevo mensaje desde el formulario.\n\n";
        $mail_body   .= "Nombre:    $name\n";
        $mail_body   .= "Email:     $email\n";
        $mail_body   .= "Teléfono:  " . ($phone ?: '—') . "\n\n";
        $mail_body   .= "Mensaje:\n$message\n";
        $mail_headers  = "From: TUOI <noreply@tuoi.es>\r\n";
        $mail_headers .= "Reply-To: $email\r\n";
        $mail_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        $host = $_SERVER['HTTP_HOST'] ?? '';
        $is_local = str_contains($host, 'localhost') || str_starts_with($host, '127.') || str_contains($host, '.local');
        if ($is_local) {
            $log_path = dirname(__DIR__) . '/logs/mail.log';
            @mkdir(dirname($log_path), 0775, true);
            $entry  = "==== " . date('Y-m-d H:i:s') . " ====\n";
            $entry .= "To:      $admin_email\n";
            $entry .= "Subject: $subject_text\n";
            $entry .= "Headers:\n$mail_headers\n";
            $entry .= "Body:\n$mail_body\n\n";
            if (@file_put_contents($log_path, $entry, FILE_APPEND) === false) {
                error_log("[TUOI mail simulado]\n" . $entry);
            }
        } else {
            @mail($admin_email, $mail_subject, $mail_body, $mail_headers);
        }

        $contact_success = true;
        $_POST = [];
    }

    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        if ($contact_success) {
            echo json_encode(['ok' => true, 'message' => t('ev_contact_ok')]);
        } else {
            $errors_translated = [];
            foreach ($contact_errors as $field => $key) {
                $errors_translated[$field] = t($key);
            }
            echo json_encode(['ok' => false, 'errors' => $errors_translated]);
        }
        exit;
    }
}
