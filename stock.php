<?php
/** RF4: consulta rápida de stock por modelo y talla (reemplaza la búsqueda física de más de 10 minutos). */
require __DIR__ . '/includes/bootstrap.php';
require_login();
$tallas = db()->query('SELECT DISTINCT talla FROM producto ORDER BY LENGTH(talla), talla')->fetchAll(PDO::FETCH_COLUMN);
$titulo = 'Consultar stock';
$activo = 'stock';
$scripts = ['assets/js/stock.js'];
require __DIR__ . '/includes/header.php';
?>
<section class="card">
  <div class="row">
    <div style="flex:3"><label for="q">¿Qué modelo busca el cliente?</label>
      <input id="q" class="search-big" placeholder="Ej.: dunk, jordan 1, air force blanco…" autofocus autocomplete="off"></div>
    <div><label for="talla">Talla</label><select id="talla" class="search-big"><option value="">Todas</option>
      <?php foreach ($tallas as $t): ?><option><?= e($t) ?></option><?php endforeach; ?></select></div>
  </div>
  <div class="timer" id="timer"></div>
</section>
<div id="res"></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
