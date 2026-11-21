<?php
/** @var string $titulo */
/** @var string $activo */
$u = user();
$menu = [
    'dashboard'  => ['dashboard.php', 'Inicio', '▦'],
    'stock'      => ['stock.php', 'Consultar stock', '⌕'],
    'ventas'     => ['ventas.php', 'Ventas', '◎'],
    'productos'  => ['productos.php', 'Productos', '▤'],
    'inventario' => ['inventario.php', 'Inventario', '⇅'],
    'clientes'   => ['clientes.php', 'Clientes', '☺'],
    'reportes'   => ['reportes.php', 'Reportes', '▥'],
];
if (is_admin()) {
    $menu['usuarios'] = ['usuarios.php', 'Usuarios', '⚿'];
}
$f = flash();
?><!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titulo) ?> · LIGASPORT</title>
<link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<div class="layout">
  <aside class="sidebar" id="sidebar">
    <a class="brand" href="dashboard.php"><img src="assets/img/logo.jpg" alt=""><span>LIGASPORT</span></a>
    <nav>
      <?php foreach ($menu as $key => [$href, $label, $ico]): ?>
        <a href="<?= e($href) ?>" class="<?= $activo === $key ? 'active' : '' ?>"><span class="ico"><?= $ico ?></span><?= e($label) ?></a>
      <?php endforeach; ?>
      <a href="chatbot.php" target="_blank"><span class="ico">✉</span>Chatbot (clientes)</a>
    </nav>
    <div class="me">
      <div><strong><?= e($u['nombre']) ?></strong><small><?= e(ucfirst($u['rol'])) ?></small></div>
      <a href="logout.php" class="btn-link">Salir</a>
    </div>
  </aside>
  <main class="content">
    <header class="topbar">
      <button class="menu-btn" onclick="document.getElementById('sidebar').classList.toggle('open')" aria-label="Menú">☰</button>
      <h1><?= e($titulo) ?></h1>
    </header>
    <?php if ($f): ?><div class="alert <?= e($f['type']) ?>"><?= e($f['msg']) ?></div><?php endif; ?>
