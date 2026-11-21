<?php
require __DIR__ . '/includes/bootstrap.php';
if (user()) {
    redirect('dashboard.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $st = db()->prepare('SELECT id_usuario, nombre, usuario, contrasena, rol FROM usuario WHERE usuario = ?');
    $st->execute([post_str('usuario')]);
    $row = $st->fetch();
    if ($row && password_verify((string)($_POST['contrasena'] ?? ''), $row['contrasena'])) {
        session_regenerate_id(true);
        unset($row['contrasena']);
        $_SESSION['user'] = $row;
        redirect('dashboard.php');
    }
    $error = 'Usuario o contraseña incorrectos.';
}
?><!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ingresar · LIGASPORT</title>
<link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<div class="login-page">
  <form class="login-box" method="post" autocomplete="off">
    <img src="assets/img/logo.jpg" alt="LIGASPORT">
    <h1>LIGASPORT</h1>
    <p class="muted">Sistema de ventas e inventario</p>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <label for="u">Usuario</label>
    <input id="u" name="usuario" required autofocus value="<?= e(post_str('usuario')) ?>">
    <label for="p">Contraseña</label>
    <input id="p" name="contrasena" type="password" required>
    <button class="btn" type="submit">Ingresar</button>
  </form>
</div>
</body>
</html>
