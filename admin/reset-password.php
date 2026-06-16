<?php
/**
 * Restablecimiento de contraseña mediante token firmado.
 * Página pública — no requiere sesión de admin.
 */
session_start();

require_once dirname(__DIR__) . '/config/conexion.php';

$token   = trim($_GET['token'] ?? $_POST['token'] ?? '');
$error   = '';
$success = '';
$valid   = false;
$user_id = null;

// ── Validar token ────────────────────────────────────────────────────────────
if ($token && isset($conexion)) {
    $token_hash = hash('sha256', $token);
    $stmt = mysqli_prepare($conexion,
        "SELECT user_id FROM password_resets
         WHERE token_hash = ? AND expires_at > NOW() LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $token_hash);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);

    if ($row) {
        $valid   = true;
        $user_id = (int) $row['user_id'];
    }
}

// ── Procesar nueva contraseña ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid && isset($conexion)) {
    $new_pass  = $_POST['new_password']  ?? '';
    $new_pass2 = $_POST['new_password2'] ?? '';

    if (strlen($new_pass) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($new_pass !== $new_pass2) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($conexion,
            "UPDATE admin_users SET password_hash = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $hash, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Eliminar token (uso único)
        $token_hash_del = hash('sha256', $token);
        $stmt = mysqli_prepare($conexion,
            "DELETE FROM password_resets WHERE token_hash = ?");
        mysqli_stmt_bind_param($stmt, 's', $token_hash_del);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $success = 'Contraseña actualizada correctamente.';
        $valid   = false;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>TUOI Admin — Nueva contraseña</title>
    <link rel="stylesheet" href="../assets/fonts/inter.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="login-page">
<div class="login-box">
    <div class="login-logo">
        <div class="brand">TUOI</div>
        <div class="sub">Panel de Administración</div>
    </div>

    <h2 class="login-title">Nueva contraseña</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <p style="text-align:center;margin-top:20px;">
            <a href="login.php" class="btn btn-primary"
               style="display:inline-flex;justify-content:center;">
                Iniciar sesión →
            </a>
        </p>

    <?php elseif (!$token || !$valid): ?>
        <div class="alert alert-error">
            ⚠️ El enlace no es válido o ha caducado (1 hora de vigencia).
        </div>
        <p style="text-align:center;margin-top:16px;">
            <a href="forgot-password.php" style="color:var(--naranja);font-weight:600;">
                Solicitar un nuevo enlace →
            </a>
        </p>

    <?php else: ?>
        <p class="login-sub">Elige una nueva contraseña para tu cuenta.</p>

        <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="token"
                   value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label class="form-label" for="new_password">Nueva contraseña</label>
                <input id="new_password" name="new_password" type="password" class="form-control"
                       placeholder="Mínimo 8 caracteres" autocomplete="new-password"
                       required minlength="8">
            </div>
            <div class="form-group">
                <label class="form-label" for="new_password2">Confirmar contraseña</label>
                <input id="new_password2" name="new_password2" type="password" class="form-control"
                       placeholder="Repite la contraseña" autocomplete="new-password"
                       required minlength="8">
            </div>
            <button type="submit" class="btn btn-primary"
                    style="width:100%;justify-content:center;margin-top:8px;">
                Guardar contraseña →
            </button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
