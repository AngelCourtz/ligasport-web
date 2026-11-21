<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();
$pdo = db();
$bajo = (int)cfg('stock_bajo');

$hoy = $pdo->query("SELECT COUNT(*) n, COALESCE(SUM(total),0) t FROM venta WHERE DATE(fecha) = CURDATE()")->fetch();
$unidadesHoy = (int)$pdo->query("SELECT COALESCE(SUM(d.cantidad),0) FROM detalle_venta d JOIN venta v USING (id_venta) WHERE DATE(v.fecha) = CURDATE()")->fetchColumn();
$mes = $pdo->query("SELECT COUNT(*) n, COALESCE(SUM(total),0) t FROM venta WHERE fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01')")->fetch();
$st = $pdo->prepare("SELECT COUNT(*) FROM producto WHERE stock_actual <= ?");
$st->execute([$bajo]);
$nBajo = (int)$st->fetchColumn();
$totalUnid = (int)$pdo->query("SELECT COALESCE(SUM(stock_actual),0) FROM producto")->fetchColumn();

$ultimas = $pdo->query("SELECT v.id_venta, v.fecha, v.canal, v.total, c.nombre cliente, u.nombre vendedor
    FROM venta v JOIN cliente c USING (id_cliente) JOIN usuario u USING (id_usuario)
    ORDER BY v.fecha DESC LIMIT 8")->fetchAll();
$st = $pdo->prepare("SELECT id_producto, marca, modelo, color, talla, stock_actual FROM producto WHERE stock_actual <= ? ORDER BY stock_actual, modelo LIMIT 8");
$st->execute([$bajo]);
$alertas = $st->fetchAll();

$titulo = 'Inicio';
$activo = 'dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="grid kpis">
  <div class="kpi"><span>Ventas de hoy</span><strong><?= soles($hoy['t']) ?></strong><span><?= (int)$hoy['n'] ?> ventas · <?= $unidadesHoy ?> unidades</span></div>
  <div class="kpi"><span>Ventas del mes</span><strong><?= soles($mes['t']) ?></strong><span><?= (int)$mes['n'] ?> ventas</span></div>
  <div class="kpi"><span>Unidades en stock</span><strong><?= $totalUnid ?></strong><span>pares y prendas</span></div>
  <div class="kpi alert-kpi"><span>Productos con stock bajo</span><strong><?= $nBajo ?></strong><span><?= $bajo ?> unidades o menos</span></div>
</div>
<div class="grid two">
  <section class="card">
    <div class="row" style="align-items:center;margin-bottom:8px"><h2 style="margin:0">Últimas ventas</h2><span class="spacer"></span><a class="btn small" href="venta_nueva.php">+ Nueva venta</a></div>
    <div class="table-wrap"><table>
      <tr><th>N°</th><th>Fecha</th><th>Cliente</th><th>Canal</th><th class="num">Total</th></tr>
      <?php foreach ($ultimas as $v): ?>
        <tr><td><a href="venta_ver.php?id=<?= (int)$v['id_venta'] ?>"><?= (int)$v['id_venta'] ?></a></td>
          <td><?= e(date('d/m H:i', strtotime($v['fecha']))) ?></td><td><?= e($v['cliente']) ?></td>
          <td><span class="badge"><?= e($v['canal']) ?></span></td><td class="num"><?= soles($v['total']) ?></td></tr>
      <?php endforeach; ?>
    </table></div>
  </section>
  <section class="card">
    <h2>Alertas de stock bajo</h2>
    <div class="table-wrap"><table>
      <tr><th>Producto</th><th>Talla</th><th class="num">Stock</th></tr>
      <?php foreach ($alertas as $p): ?>
        <tr><td><?= e($p['marca'] . ' ' . $p['modelo']) ?> <span class="muted"><?= e($p['color']) ?></span></td><td><?= e($p['talla']) ?></td>
          <td class="num"><span class="badge <?= $p['stock_actual'] == 0 ? 'out' : 'low' ?>"><?= (int)$p['stock_actual'] ?></span></td></tr>
      <?php endforeach; ?>
      <?php if (!$alertas): ?><tr><td colspan="3" class="muted">No hay productos con stock bajo.</td></tr><?php endif; ?>
    </table></div>
    <p><a href="reportes.php#stock">Ver reporte de stock</a></p>
  </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
