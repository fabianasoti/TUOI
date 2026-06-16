<?php
/**
 * Solicitud de recuperación de contraseña.
 * Página pública — no requiere sesión de admin.
 */
session_start();

require_once dirname(__DIR__) . '/config/conexion.php';

// ── Auto-migraciones ─────────────────────────────────────────────────────────
if (isset($conexion)) {
    @mysqli_query($conexion,
        "ALTER TABLE admin_users ADD COLUMN email VARCHAR(255) NULL AFTER username"
    );
    @mysqli_query($conexion,
        "CREATE TABLE IF NOT EXISTS password_resets (
            id         INT AUTO_INCREMENT PRIMARY KEY,
            user_id    INT NOT NULL,
            token_hash VARCHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME DEFAULT NOW(),
            UNIQUE KEY uq_user (user_id),
            FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

// ── CSRF de página pública ───────────────────────────────────────────────────
if (empty($_SESSION['fp_csrf'])) {
    $_SESSION['fp_csrf'] = bin2hex(random_bytes(32));
}

$error = '';
$sent  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_csrf = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['fp_csrf'] ?? '', $posted_csrf)) {
        $error = 'Token de seguridad inválido. Recarga la página.';
    } else {
        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Introduce un email válido.';
        } else {
            // Respuesta idéntica exista o no el email (evita enumeración)
            $sent = true;

            if (isset($conexion)) {
                $stmt = mysqli_prepare($conexion,
                    "SELECT id, username FROM admin_users WHERE email = ? LIMIT 1");
                mysqli_stmt_bind_param($stmt, 's', $email);
                mysqli_stmt_execute($stmt);
                $res  = mysqli_stmt_get_result($stmt);
                $user = $res ? mysqli_fetch_assoc($res) : null;
                mysqli_stmt_close($stmt);

                if ($user) {
                    // Rate limit: 1 solicitud cada 5 minutos por usuario
                    $stmt = mysqli_prepare($conexion,
                        "SELECT created_at FROM password_resets WHERE user_id = ? LIMIT 1");
                    mysqli_stmt_bind_param($stmt, 'i', $user['id']);
                    mysqli_stmt_execute($stmt);
                    $res_r    = mysqli_stmt_get_result($stmt);
                    $existing = $res_r ? mysqli_fetch_assoc($res_r) : null;
                    mysqli_stmt_close($stmt);

                    $too_soon = $existing && (time() - strtotime($existing['created_at'])) < 300;

                    if (!$too_soon) {
                        $token      = bin2hex(random_bytes(32));
                        $token_hash = hash('sha256', $token);
                        $expires_at = date('Y-m-d H:i:s', time() + 3600);

                        $stmt = mysqli_prepare($conexion,
                            "INSERT INTO password_resets (user_id, token_hash, expires_at)
                             VALUES (?, ?, ?)
                             ON DUPLICATE KEY UPDATE
                               token_hash = VALUES(token_hash),
                               expires_at = VALUES(expires_at),
                               created_at = NOW()");
                        mysqli_stmt_bind_param($stmt, 'iss',
                            $user['id'], $token_hash, $expires_at);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);

                        // ── Email ────────────────────────────────────────
                        $protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                        $host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
                        $reset_url = "{$protocol}://{$host}/admin/reset-password.php?token={$token}";

                        $subject_text = 'Recuperación de contraseña · TUOI Admin';
                        $mail_subject = '=?UTF-8?B?' . base64_encode($subject_text) . '?=';
                        $mail_body    = "Hola {$user['username']},\n\n"
                            . "Recibimos una solicitud para restablecer la contraseña de tu cuenta en el panel TUOI.\n\n"
                            . "Haz clic en el siguiente enlace (válido durante 1 hora):\n"
                            . "{$reset_url}\n\n"
                            . "Si no solicitaste este cambio, puedes ignorar este correo.\n\n"
                            . "— TUOI Admin";
                        $mail_headers  = "From: TUOI Admin <noreply@tuoi.es>\r\n";
                        $mail_headers .= "Reply-To: noreply@tuoi.es\r\n";
                        $mail_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

                        $is_local = str_contains($host, 'localhost')
                            || str_starts_with($host, '127.')
                            || str_contains($host, '.local');

                        if ($is_local) {
                            $log_path = dirname(__DIR__) . '/logs/mail.log';
                            @mkdir(dirname($log_path), 0775, true);
                            $entry  = "==== " . date('Y-m-d H:i:s') . " [PASSWORD RESET] ====\n";
                            $entry .= "To:      {$email}\n";
                            $entry .= "Subject: {$subject_text}\n";
                            $entry .= "Body:\n{$mail_body}\n\n";
                            @file_put_contents($log_path, $entry, FILE_APPEND);
                        } else {
                            @mail($email, $mail_subject, $mail_body, $mail_headers);
                        }
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>TUOI Admin — Recuperar contraseña</title>
    <link rel="stylesheet" href="../assets/fonts/inter.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="login-page">
<div class="login-box">
    <div class="login-logo">
        <div class="brand">TUOI</div>
        <div class="sub">Panel de Administración</div>
    </div>

    <h2 class="login-title">Recuperar contraseña</h2>

    <?php if ($sent): ?>
        <p class="login-sub">Si el email está registrado, recibirás un enlace en breve. Revisa también la carpeta de spam.</p>
        <p style="text-align:center;margin-top:20px;">
            <a href="login.php" style="color:var(--naranja);font-weight:600;">← Volver al login</a>
        </p>
    <?php else: ?>
        <p class="login-sub">Introduce el email asociado a tu cuenta y te enviaremos un enlace de acceso.</p>

        <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['fp_csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input id="email" name="email" type="email" class="form-control"
                       placeholder="tu@email.com" autocomplete="email" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-primary"
                    style="width:100%;justify-content:center;margin-top:8px;">
                Enviar enlace →
            </button>
        </form>
        <p style="text-align:center;margin-top:16px;">
            <a href="login.php" style="color:var(--naranja);font-size:14px;">← Volver al login</a>
        </p>
    <?php endif; ?>
</div>
</body>
</html>
