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
        $u    = mysqli_real_escape_string($conexion, $username);
        $res  = mysqli_query($conexion, "SELECT id, password_hash FROM admin_users WHERE username = '$u' LIMIT 1");
        $user = $res ? mysqli_fetch_assoc($res) : null;

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user']      = $username;
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
</div>
</body>
</html>
