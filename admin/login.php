<?php
session_start();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once dirname(__DIR__) . '/config/conexion.php';

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password && isset($conexion)) {
        $stmt = mysqli_prepare($conexion, "SELECT id, password_hash, role FROM admin_users WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $res  = mysqli_stmt_get_result($stmt);
        $user = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user']      = $username;
            $_SESSION['admin_role']      = $user['role'] ?? 'editor';
            header('Location: index.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    } else {
        $error = 'Completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>TUOI Admin — Login</title>
    <link rel="stylesheet" href="../assets/fonts/inter.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="login-page">
<div class="login-box">
    <div class="login-logo">
        <div class="brand">TUOI</div>
        <div class="sub">Panel de Administración</div>
    </div>

    <h2 class="login-title">Bienvenido</h2>
    <p class="login-sub">Inicia sesión para gestionar el contenido del sitio.</p>

    <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label class="form-label" for="username">Usuario</label>
            <input id="username" name="username" type="text" class="form-control"
                   placeholder="admin" autocomplete="username" required
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label class="form-label" for="password">Contraseña</label>
            <input id="password" name="password" type="password" class="form-control"
                   placeholder="••••••••" autocomplete="current-password" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:8px;">
            Entrar →
        </button>
    </form>
    <p style="text-align:center;margin-top:16px;">
        <a href="forgot-password.php" style="color:var(--naranja);font-size:14px;">
            ¿Olvidaste tu contraseña?
        </a>
    </p>
</div>
</body>
</html>
