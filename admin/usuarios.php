<?php
require_once 'config.php';

// ── Auto-migración: columna role ───────────────────────────────────────
// Si la instalación es anterior a este cambio, añadimos la columna al vuelo.
// ALTER falla silenciosamente si ya existe (try/catch).
try {
    mysqli_query($conexion,
        "ALTER TABLE admin_users
         ADD COLUMN role ENUM('admin','editor') NOT NULL DEFAULT 'editor' AFTER username"
    );
    // El primer usuario existente (id más bajo) hereda rol admin.
    mysqli_query($conexion,
        "UPDATE admin_users SET role = 'admin'
         WHERE id = (SELECT min_id FROM (SELECT MIN(id) AS min_id FROM admin_users) t)
           AND role = 'editor'"
    );
} catch (\Throwable $e) { /* columna ya existe */ }

$success = '';
$error   = '';

// ── Crear usuario (solo admin) ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'crear') {
    if (!is_admin()) {
        $error = 'No tienes permisos para crear usuarios.';
    } else {
        $username  = trim($_POST['username'] ?? '');
        $password  = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        $new_email = trim($_POST['new_email'] ?? '');
        $role      = in_array($_POST['role'] ?? '', ['admin', 'editor'], true)
                     ? $_POST['role'] : 'editor';

        if ($username === '' || $password === '') {
            $error = 'El nombre de usuario y la contraseña son obligatorios.';
        } elseif ($new_email !== '' && !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = 'El email no tiene un formato válido.';
        } elseif (!preg_match('/^[a-zA-Z0-9_\-]{3,50}$/', $username)) {
            $error = 'El usuario solo puede contener letras, números, guiones o guiones bajos (3–50 caracteres).';
        } elseif (strlen($password) < 8) {
            $error = 'La contraseña debe tener al menos 8 caracteres.';
        } elseif ($password !== $password2) {
            $error = 'Las contraseñas no coinciden.';
        } else {
            $stmt = mysqli_prepare($conexion, "SELECT id FROM admin_users WHERE username = ?");
            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            $exists = mysqli_stmt_num_rows($stmt) > 0;
            mysqli_stmt_close($stmt);

            if ($exists) {
                $error = 'Ya existe un usuario con ese nombre.';
            } else {
                $hash       = password_hash($password, PASSWORD_DEFAULT);
                $email_val  = $new_email !== '' ? $new_email : null;
                $stmt = mysqli_prepare($conexion,
                    "INSERT INTO admin_users (username, email, role, password_hash) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, 'ssss', $username, $email_val, $role, $hash);
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Usuario «{$username}» creado correctamente.";
                } else {
                    $error = 'Error al guardar el usuario. Inténtalo de nuevo.';
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// ── Cambiar rol (solo admin, no en sí mismo) ───────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cambiar_rol') {
    if (!is_admin()) {
        $error = 'No tienes permisos para cambiar roles.';
    } else {
        $target_id = (int) ($_POST['target_id'] ?? 0);
        $new_role  = in_array($_POST['new_role'] ?? '', ['admin', 'editor'], true)
                     ? $_POST['new_role'] : null;

        $me_id = 0;
        $stmt = mysqli_prepare($conexion, "SELECT id FROM admin_users WHERE username = ? LIMIT 1");
        $me   = $_SESSION['admin_user'] ?? '';
        mysqli_stmt_bind_param($stmt, 's', $me);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $me_id);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($target_id <= 0 || !$new_role) {
            $error = 'Datos no válidos.';
        } elseif ($target_id === $me_id) {
            $error = 'No puedes cambiar tu propio rol.';
        } else {
            $stmt = mysqli_prepare($conexion, "UPDATE admin_users SET role = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'si', $new_role, $target_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $success = 'Rol actualizado correctamente.';
        }
    }
}

// ── Eliminar usuario (solo admin) ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'eliminar') {
    if (!is_admin()) {
        $error = 'No tienes permisos para eliminar usuarios.';
    } else {
        $del_id = (int) ($_POST['del_id'] ?? 0);
        $me_id  = 0;

        $stmt = mysqli_prepare($conexion, "SELECT id FROM admin_users WHERE username = ? LIMIT 1");
        $me   = $_SESSION['admin_user'] ?? '';
        mysqli_stmt_bind_param($stmt, 's', $me);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $me_id);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        $total = 0;
        $res = mysqli_query($conexion, "SELECT COUNT(*) AS c FROM admin_users");
        if ($res) $total = (int) mysqli_fetch_assoc($res)['c'];

        if ($del_id <= 0) {
            $error = 'ID de usuario no válido.';
        } elseif ($del_id === $me_id) {
            $error = 'No puedes eliminar tu propia cuenta.';
        } elseif ($total <= 1) {
            $error = 'No se puede eliminar el único usuario administrador.';
        } else {
            $stmt = mysqli_prepare($conexion, "DELETE FROM admin_users WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $del_id);
            if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
                $success = 'Usuario eliminado correctamente.';
            } else {
                $error = 'No se encontró el usuario o ya fue eliminado.';
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// ── Cambiar contraseña (cualquier admin; editor solo la propia) ────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cambiar_pass') {
    $target_id = (int) ($_POST['target_id'] ?? 0);
    $new_pass  = $_POST['new_password'] ?? '';
    $new_pass2 = $_POST['new_password2'] ?? '';

    $me_id = 0;
    $stmt = mysqli_prepare($conexion, "SELECT id FROM admin_users WHERE username = ? LIMIT 1");
    $me   = $_SESSION['admin_user'] ?? '';
    mysqli_stmt_bind_param($stmt, 's', $me);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $me_id);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    $allowed = is_admin() || ($target_id === $me_id);

    if (!$allowed) {
        $error = 'Solo puedes cambiar tu propia contraseña.';
    } elseif ($target_id <= 0) {
        $error = 'Usuario no válido.';
    } elseif (strlen($new_pass) < 8) {
        $error = 'La nueva contraseña debe tener al menos 8 caracteres.';
    } elseif ($new_pass !== $new_pass2) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conexion, "UPDATE admin_users SET password_hash = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $hash, $target_id);
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Contraseña actualizada correctamente.';
        } else {
            $error = 'Error al actualizar la contraseña.';
        }
        mysqli_stmt_close($stmt);
    }
}

// ── Actualizar email ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'actualizar_email') {
    $target_id = (int) ($_POST['target_id'] ?? 0);
    $new_email = trim($_POST['new_email'] ?? '');

    $me_id = 0;
    $stmt = mysqli_prepare($conexion, "SELECT id FROM admin_users WHERE username = ? LIMIT 1");
    $me   = $_SESSION['admin_user'] ?? '';
    mysqli_stmt_bind_param($stmt, 's', $me);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $me_id);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    $allowed = is_admin() || ($target_id === $me_id);

    if (!$allowed) {
        $error = 'Solo puedes editar tu propio email.';
    } elseif ($target_id <= 0) {
        $error = 'Usuario no válido.';
    } elseif ($new_email !== '' && !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no tiene un formato válido.';
    } else {
        $email_val = $new_email === '' ? null : $new_email;
        $stmt = mysqli_prepare($conexion,
            "UPDATE admin_users SET email = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $email_val, $target_id);
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Email actualizado correctamente.';
        } else {
            $error = 'Error al guardar el email.';
        }
        mysqli_stmt_close($stmt);
    }
}

// ── Enviar reset por email (admin puede enviarlo a otro usuario) ────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'enviar_reset') {
    if (!is_admin()) {
        $error = 'No tienes permisos para esta acción.';
    } else {
        $target_id = (int) ($_POST['target_id'] ?? 0);
        $stmt = mysqli_prepare($conexion,
            "SELECT id, username, email FROM admin_users WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'i', $target_id);
        mysqli_stmt_execute($stmt);
        $res  = mysqli_stmt_get_result($stmt);
        $user = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);

        if (!$user || empty($user['email'])) {
            $error = 'Este usuario no tiene email configurado.';
        } else {
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

            $protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $reset_url = "{$protocol}://{$host}/admin/reset-password.php?token={$token}";

            $subject_text = 'Recuperación de contraseña · TUOI Admin';
            $mail_subject = '=?UTF-8?B?' . base64_encode($subject_text) . '?=';
            $mail_body    = "Hola {$user['username']},\n\n"
                . "Un administrador ha generado un enlace para restablecer tu contraseña.\n\n"
                . "Haz clic en el siguiente enlace (válido durante 1 hora):\n"
                . "{$reset_url}\n\n"
                . "Si no esperabas este correo, contacta con el administrador.\n\n"
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
                $entry  = "==== " . date('Y-m-d H:i:s') . " [PASSWORD RESET — admin] ====\n";
                $entry .= "To:      {$user['email']}\n";
                $entry .= "Subject: {$subject_text}\n";
                $entry .= "Body:\n{$mail_body}\n\n";
                @file_put_contents($log_path, $entry, FILE_APPEND);
            } else {
                @mail($user['email'], $mail_subject, $mail_body, $mail_headers);
            }

            $success = "Enlace de recuperación enviado a {$user['email']}.";
        }
    }
}

// ── Cargar lista ────────────────────────────────────────────────────────
$usuarios = [];
$res = mysqli_query($conexion,
    "SELECT id, username, email, role, created_at FROM admin_users ORDER BY id ASC");
if ($res) while ($row = mysqli_fetch_assoc($res)) $usuarios[] = $row;

$me_username = $_SESSION['admin_user'] ?? '';
$me_id_final = 0;
foreach ($usuarios as $u) {
    if ($u['username'] === $me_username) { $me_id_final = (int) $u['id']; break; }
}

$role_labels = ['admin' => 'Administrador', 'editor' => 'Editor'];
$role_colors = ['admin' => '#3730a3;background:#e0e7ff', 'editor' => '#065f46;background:#d1fae5'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>TUOI Admin — Usuarios</title>
    <link rel="stylesheet" href="../assets/fonts/inter.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .user-table { width:100%; border-collapse:collapse; font-size:14px; }
        .user-table th { text-align:left; padding:9px 14px; font-size:12px; font-weight:600;
                         text-transform:uppercase; letter-spacing:.04em; color:var(--muted);
                         border-bottom:1px solid var(--border); }
        .user-table td { padding:12px 14px; border-bottom:1px solid var(--border); vertical-align:top; }
        .user-table tr:last-child td { border-bottom:none; }
        .user-table tr:hover td { background:rgba(0,0,0,.025); }

        .badge { font-size:11px; font-weight:700; padding:3px 9px; border-radius:20px; }
        .badge-me { background:#e0e7ff; color:#3730a3; }
        .badge-admin { background:#ede9fe; color:#5b21b6; }
        .badge-editor { background:#d1fae5; color:#065f46; }

        .pass-form { display:none; margin-top:10px; padding:12px; background:var(--bg);
                     border:1px solid var(--border); border-radius:8px; }
        .pass-form.open { display:block; }
        .pass-form .form-row { display:flex; gap:8px; align-items:flex-end; flex-wrap:wrap; }
        .pass-form input[type=password] { flex:1; min-width:140px; }

        .rol-form { display:none; margin-top:8px; padding:10px 12px; background:var(--bg);
                    border:1px solid var(--border); border-radius:8px; }
        .rol-form.open { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }

        .user-actions { display:flex; gap:6px; align-items:center; flex-wrap:wrap; }
        .form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px 20px; }
        .form-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px 20px; }
        @media(max-width:640px){
            .form-grid-2, .form-grid-3 { grid-template-columns:1fr; }
        }
        .perms-notice { display:flex; gap:10px; align-items:flex-start; padding:14px 16px;
                        background:#fffbeb; border:1px solid #fde68a; border-radius:10px;
                        font-size:13px; color:#92400e; margin-bottom:20px; }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include 'partials/sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Usuarios administradores</div>
                <div class="topbar-sub">
                    Gestiona las cuentas con acceso al panel.
                    <?php if (!is_admin()): ?>
                        <span style="margin-left:6px; color:var(--naranja); font-weight:600;">
                            (Tu rol: Editor — puedes ver y cambiar tu contraseña)
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="content-area">

            <?php if ($success): ?>
                <div class="alert alert-success" style="margin-bottom:16px;"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom:16px;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!is_admin()): ?>
            <div class="perms-notice">
                <span style="font-size:18px;">ℹ️</span>
                <div>Solo los usuarios con rol <strong>Administrador</strong> pueden crear o eliminar cuentas.
                Puedes cambiar tu propia contraseña desde la tabla de abajo.</div>
            </div>
            <?php endif; ?>

            <!-- ── Lista de usuarios ─────────────────────────────────── -->
            <div class="card" style="margin-bottom:24px;">
                <div class="card-header" style="padding:16px 20px; border-bottom:1px solid var(--border);">
                    <h3 style="margin:0; font-size:15px; font-weight:600;">Usuarios existentes</h3>
                </div>
                <div style="overflow-x:auto;">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Creado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($usuarios as $u):
                            $is_me   = $u['username'] === $me_username;
                            $uid     = (int) $u['id'];
                            $created = $u['created_at']
                                ? date('d/m/Y', strtotime($u['created_at']))
                                : '—';
                            $role_label = $role_labels[$u['role']] ?? $u['role'];
                            $badge_class = 'badge-' . ($u['role'] ?? 'editor');
                        ?>
                            <tr>
                                <td style="color:var(--muted); font-size:13px;"><?= $uid ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($u['username']) ?></strong>
                                    <?php if ($is_me): ?>
                                        <span class="badge badge-me">Tú</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($u['email'])): ?>
                                        <span style="font-size:13px;"><?= htmlspecialchars($u['email']) ?></span>
                                    <?php else: ?>
                                        <span style="font-size:12px;color:var(--muted);">Sin email</span>
                                    <?php endif; ?>
                                    <!-- formulario inline editar email -->
                                    <?php if (is_admin() || $is_me): ?>
                                    <div class="pass-form" id="email-<?= $uid ?>">
                                        <form method="post">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="actualizar_email">
                                            <input type="hidden" name="target_id" value="<?= $uid ?>">
                                            <div class="form-row">
                                                <div style="flex:1;min-width:180px;">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" name="new_email" class="form-input"
                                                           placeholder="usuario@email.com"
                                                           value="<?= htmlspecialchars($u['email'] ?? '') ?>"
                                                           autocomplete="off">
                                                </div>
                                                <div style="padding-top:22px;">
                                                    <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $badge_class ?>"><?= $role_label ?></span>
                                    <?php if (is_admin() && !$is_me): ?>
                                    <!-- formulario inline cambio de rol -->
                                    <div class="rol-form" id="rol-<?= $uid ?>">
                                        <form method="post" style="display:contents;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="cambiar_rol">
                                            <input type="hidden" name="target_id" value="<?= $uid ?>">
                                            <select name="new_role" class="form-input" style="width:auto;padding:5px 10px;font-size:13px;">
                                                <option value="admin" <?= $u['role']==='admin' ? 'selected':'' ?>>Administrador</option>
                                                <option value="editor" <?= $u['role']==='editor' ? 'selected':'' ?>>Editor</option>
                                            </select>
                                            <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                                            <button type="button" class="btn btn-secondary btn-sm"
                                                    onclick="toggleRol(<?= $uid ?>)">Cancelar</button>
                                        </form>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td style="color:var(--muted); font-size:13px; white-space:nowrap;"><?= $created ?></td>
                                <td>
                                    <div class="user-actions">
                                        <?php if (is_admin() && !$is_me): ?>
                                        <button type="button" class="btn btn-secondary btn-sm"
                                                onclick="toggleRol(<?= $uid ?>)">
                                            🏷 Cambiar rol
                                        </button>
                                        <?php endif; ?>

                                        <?php if (is_admin() || $is_me): ?>
                                        <button type="button" class="btn btn-secondary btn-sm"
                                                onclick="toggleEmail(<?= $uid ?>)">
                                            ✉️ Email
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm"
                                                onclick="togglePass(<?= $uid ?>)">
                                            🔑 Contraseña
                                        </button>
                                        <?php endif; ?>

                                        <?php if (is_admin() && !$is_me && !empty($u['email'])): ?>
                                        <form method="post" style="display:inline;"
                                              onsubmit="return confirm('¿Enviar enlace de recuperación a <?= htmlspecialchars(addslashes($u['email'])) ?>?');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="enviar_reset">
                                            <input type="hidden" name="target_id" value="<?= $uid ?>">
                                            <button type="submit" class="btn btn-secondary btn-sm">
                                                📧 Enviar reset
                                            </button>
                                        </form>
                                        <?php endif; ?>

                                        <?php if (is_admin() && !$is_me && count($usuarios) > 1): ?>
                                        <form method="post" style="display:inline;"
                                              onsubmit="return confirm('¿Eliminar al usuario «<?= htmlspecialchars(addslashes($u['username'])) ?>»? Esta acción no se puede deshacer.');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="eliminar">
                                            <input type="hidden" name="del_id" value="<?= $uid ?>">
                                            <button type="submit" class="btn btn-sm"
                                                    style="background:#fee2e2;color:#991b1b;border-color:#fca5a5;">
                                                🗑 Eliminar
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>

                                    <!-- formulario inline cambio de contraseña -->
                                    <div class="pass-form" id="pass-<?= $uid ?>">
                                        <form method="post">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="cambiar_pass">
                                            <input type="hidden" name="target_id" value="<?= $uid ?>">
                                            <div class="form-row">
                                                <div style="flex:1; min-width:140px;">
                                                    <label class="form-label">Nueva contraseña</label>
                                                    <input type="password" name="new_password" class="form-input"
                                                           placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                                                </div>
                                                <div style="flex:1; min-width:140px;">
                                                    <label class="form-label">Confirmar</label>
                                                    <input type="password" name="new_password2" class="form-input"
                                                           placeholder="Repite la contraseña" autocomplete="new-password">
                                                </div>
                                                <div style="padding-top:22px;">
                                                    <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── Crear nuevo usuario (solo admin) ──────────────────── -->
            <?php if (is_admin()): ?>
            <div class="card">
                <div class="card-header" style="padding:16px 20px; border-bottom:1px solid var(--border);">
                    <h3 style="margin:0; font-size:15px; font-weight:600;">Crear nuevo usuario</h3>
                </div>
                <div style="padding:20px;">
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="crear">
                        <div class="form-grid-3" style="margin-bottom:16px;">
                            <div>
                                <label class="form-label" for="new_username">Nombre de usuario</label>
                                <input type="text" id="new_username" name="username" class="form-input"
                                       placeholder="ej. maria_admin" autocomplete="off"
                                       pattern="[a-zA-Z0-9_\-]{3,50}" maxlength="50">
                                <div style="font-size:12px;color:var(--muted);margin-top:4px;">
                                    Letras, números, guiones (3–50 caracteres).
                                </div>
                            </div>
                            <div>
                                <label class="form-label" for="new_email_create">Email <span style="font-weight:400;color:var(--muted);">(para recuperar contraseña)</span></label>
                                <input type="email" id="new_email_create" name="new_email" class="form-input"
                                       placeholder="usuario@email.com" autocomplete="off">
                            </div>
                            <div>
                                <label class="form-label" for="new_role">Rol</label>
                                <select id="new_role" name="role" class="form-input">
                                    <option value="editor" selected>Editor — puede editar contenido</option>
                                    <option value="admin">Administrador — acceso completo</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label" for="new_password">Contraseña</label>
                                <input type="password" id="new_password" name="password" class="form-input"
                                       placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                            </div>
                            <div>
                                <label class="form-label" for="new_password2">Confirmar contraseña</label>
                                <input type="password" id="new_password2" name="password2" class="form-input"
                                       placeholder="Repite la contraseña" autocomplete="new-password">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">+ Crear usuario</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
function togglePass(id) {
    const el = document.getElementById('pass-' + id);
    el.classList.toggle('open');
    if (el.classList.contains('open')) el.querySelector('input[type=password]').focus();
}
function toggleRol(id) {
    document.getElementById('rol-' + id).classList.toggle('open');
}
function toggleEmail(id) {
    const el = document.getElementById('email-' + id);
    el.classList.toggle('open');
    if (el.classList.contains('open')) el.querySelector('input[type=email]').focus();
}
</script>
</body>
</html>
