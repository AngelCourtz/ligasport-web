<?php
/** RF5: reportes de ventas y de stock por periodo (con exportación a CSV para Excel). */
require __DIR__ . '/includes/bootstrap.php';
require_login();
$pdo = db();
$bajo = (int)cfg('stock_bajo');
$desde = $_GET['desde'] ?? date('Y-m-01');
$hasta = $_GET['hasta'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) $desde = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) $hasta = date('Y-m-d');
$rango = [$desde, $hasta];

$st = $pdo->prepare('SELECT COUNT(*) n, COALESCE(SUM(total),0) t FROM venta WHERE DATE(fecha) BETWEEN ? AND ?');
$st->execute($rango);
$res = $st->fetch();
$st = $pdo->prepare('SELECT COALESCE(SUM(d.cantidad),0) FROM detalle_venta d JOIN venta v USING (id_venta) WHERE DATE(v.fecha) BETWEEN ? AND ?');
$st->execute($rango);
$unid = (int)$st->fetchColumn();

$st = $pdo->prepare('SELECT canal, COUNT(*) n, SUM(total) t FROM venta WHERE DATE(fecha) BETWEEN ? AND ? GROUP BY canal ORDER BY t DESC');
$st->execute($rango);
$porCanal = $st->fetchAll();

$st = $pdo->prepare('SELECT p.marca, p.modelo, p.color, SUM(d.cantidad) unidades, SUM(d.cantidad * d.precio_unitario) monto
    FROM detalle_venta d JOIN venta v USING (id_venta) JOIN producto p USING (id_producto)
    WHERE DATE(v.fecha) BETWEEN ? AND ? GROUP BY p.marca, p.modelo, p.color ORDER BY unidades DESC, monto DESC LIMIT 8');
$st->execute($rango);
$top = $st->fetchAll();

$st = $pdo->prepare('SELECT p.*, COALESCE((SELECT SUM(d.cantidad) FROM detalle_venta d JOIN venta v USING (id_venta)
    WHERE d.id_producto = p.id_producto AND DATE(v.fecha) BETWEEN ? AND ?),0) vendidas
    FROM producto p WHERE p.stock_actual <= ? ORDER BY p.stock_actual, vendidas DESC');
$st->execute([$desde, $hasta, $bajo]);
$stockBajo = $st->fetchAll();

if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="ventas_' . $desde . '_' . $hasta . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['N° venta', 'Fecha', 'Canal', 'Cliente', 'Vendedor', 'Marca', 'Modelo', 'Color', 'Talla', 'Cantidad', 'Precio unitario', 'Subtotal'], ';');
    $st = $pdo->prepare('SELECT v.id_venta, v.fecha, v.canal, c.nombre cliente, u.nombre vendedor, p.marca, p.modelo, p.color, p.talla, d.cantidad, d.precio_unitario,
        d.cantidad * d.precio_unitario subtotal FROM venta v JOIN detalle_venta d USING (id_venta) JOIN producto p USING (id_producto)
        JOIN cliente c USING (id_cliente) JOIN usuario u USING (id_usuario) WHERE DATE(v.fecha) BETWEEN ? AND ? ORDER BY v.fecha');
    $st->execute($rango);
    while ($r = $st->fetch()) {
        fputcsv($out, array_values($r), ';');
    }
    exit;
}

$maxCanal = max(array_merge([1], array_column($porCanal, 't')));
$maxTop = max(array_merge([1], array_column($top, 'unidades')));
$titulo = 'Reportes';
$activo = 'reportes';
require __DIR__ . '/includes/header.php';
?>
<section class="card no-print">
  <form class="row" method="get">
    <div><label>Desde</label><input type="date" name="desde" value="<?= e($desde) ?>"></div>
    <div><label>Hasta</label><input type="date" name="hasta" value="<?= e($hasta) ?>"></div>
    <div class="spacer"><button class="btn secondary">Actualizar</button></div>
    <div class="spacer"><a class="btn secondary" href="?desde=<?= e($desde) ?>&hasta=<?= e($hasta) ?>&export=csv">Exportar a Excel (CSV)</a></div>
    <div class="spacer"><button type="button" class="btn secondary" onclick="print()">Imprimir</button></div>
  </form>
</section>
<p class="muted">Periodo: <?= e(date('d/m/Y', strtotime($desde))) ?> al <?= e(date('d/m/Y', strtotime($hasta))) ?></p>
<div class="grid kpis">
  <div class="kpi"><span>Ventas</span><strong><?= soles($res['t']) ?></strong></div>
  <div class="kpi"><span>Número de ventas</span><strong><?= (int)$res['n'] ?></strong></div>
  <div class="kpi"><span>Unidades vendidas</span><strong><?= $unid ?></strong></div>
  <div class="kpi"><span>Ticket promedio</span><strong><?= soles($res['n'] ? $res['t'] / $res['n'] : 0) ?></strong></div>
</div>
<div class="grid two">
  <section class="card">
    <h2>Ventas por canal</h2>
    <?php foreach ($porCanal as $c): ?>
      <div class="bar"><span class="lbl"><?= e($c['canal']) ?> <span class="muted">(<?= (int)$c['n'] ?>)</span></span>
        <span class="track"><span class="fill" style="display:block;width:<?= round($c['t'] / $maxCanal * 100) ?>%"></span></span><span class="val"><?= soles($c['t']) ?></span></div>
    <?php endforeach; ?>
    <?php if (!$porCanal): ?><p class="muted">Sin ventas en el periodo.</p><?php endif; ?>
  </section>
  <section class="card">
    <h2>Modelos más vendidos</h2>
    <?php foreach ($top as $t): ?>
      <div class="bar"><span class="lbl"><?= e($t['modelo']) ?></span>
        <span class="track"><span class="fill" style="display:block;width:<?= round($t['unidades'] / $maxTop * 100) ?>%"></span></span><span class="val"><?= (int)$t['unidades'] ?> ud. · <?= soles($t['monto']) ?></span></div>
    <?php endforeach; ?>
    <?php if (!$top): ?><p class="muted">Sin ventas en el periodo.</p><?php endif; ?>
  </section>
</div>
<section class="card" id="stock">
  <h2>Productos con stock bajo (<?= $bajo ?> unidades o menos)</h2>
  <p class="muted">Ordenados por stock y por unidades vendidas en el periodo: ayudan a decidir qué reponer en Minka, outlets o proveedores.</p>
  <div class="table-wrap"><table>
    <tr><th>Producto</th><th>Color</th><th>Talla</th><th class="num">Vendidas en el periodo</th><th class="num">Stock</th></tr>
    <?php foreach ($stockBajo as $p): ?>
      <tr><td><?= e($p['marca'] . ' ' . $p['modelo']) ?></td><td><?= e($p['color']) ?></td><td><?= e($p['talla']) ?></td>
        <td class="num"><?= (int)$p['vendidas'] ?></td><td class="num"><span class="badge <?= $p['stock_actual'] == 0 ? 'out' : 'low' ?>"><?= (int)$p['stock_actual'] ?></span></td></tr>
    <?php endforeach; ?>
  </table></div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
