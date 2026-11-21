<?php
/** Listado de ventas con filtros por fecha y canal. */
require __DIR__ . '/includes/bootstrap.php';
require_login();
$desde = $_GET['desde'] ?? date('Y-m-d', strtotime('-30 days'));
$hasta = $_GET['hasta'] ?? date('Y-m-d');
$canal = $_GET['canal'] ?? '';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) $desde = date('Y-m-d', strtotime('-30 days'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) $hasta = date('Y-m-d');

$sql = 'SELECT v.id_venta, v.fecha, v.canal, v.total, c.nombre cliente, u.nombre vendedor,
        (SELECT SUM(cantidad) FROM detalle_venta d WHERE d.id_venta = v.id_venta) unidades
        FROM venta v JOIN cliente c USING (id_cliente) JOIN usuario u USING (id_usuario)
        WHERE DATE(v.fecha) BETWEEN ? AND ?';
$params = [$desde, $hasta];
if (in_array($canal, CANALES, true)) {
    $sql .= ' AND v.canal = ?';
    $params[] = $canal;
}
$st = db()->prepare($sql . ' ORDER BY v.fecha DESC');
$st->execute($params);
$ventas = $st->fetchAll();
$suma = array_sum(array_column($ventas, 'total'));

$titulo = 'Ventas';
$activo = 'ventas';
require __DIR__ . '/includes/header.php';
?>
<section class="card">
  <form class="row" method="get">
    <div><label>Desde</label><input type="date" name="desde" value="<?= e($desde) ?>"></div>
    <div><label>Hasta</label><input type="date" name="hasta" value="<?= e($hasta) ?>"></div>
    <div><label>Canal</label><select name="canal"><option value="">Todos</option>
      <?php foreach (CANALES as $c): ?><option <?= $c === $canal ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select></div>
    <div class="spacer"><button class="btn secondary">Filtrar</button></div>
    <div class="spacer"><a class="btn" href="venta_nueva.php">+ Nueva venta</a></div>
  </form>
</section>
<section class="card">
  <p><strong><?= count($ventas) ?></strong> ventas · <strong><?= soles($suma) ?></strong></p>
  <div class="table-wrap"><table>
    <tr><th>N°</th><th>Fecha</th><th>Cliente</th><th>Canal</th><th>Vendedor</th><th class="num">Unid.</th><th class="num">Total</th></tr>
    <?php foreach ($ventas as $v): ?>
      <tr><td><a href="venta_ver.php?id=<?= (int)$v['id_venta'] ?>"><?= (int)$v['id_venta'] ?></a></td><td><?= e(date('d/m/Y H:i', strtotime($v['fecha']))) ?></td>
        <td><?= e($v['cliente']) ?></td><td><span class="badge"><?= e($v['canal']) ?></span></td><td><?= e($v['vendedor']) ?></td>
        <td class="num"><?= (int)$v['unidades'] ?></td><td class="num"><?= soles($v['total']) ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$ventas): ?><tr><td colspan="7" class="muted">No hay ventas en el periodo.</td></tr><?php endif; ?>
  </table></div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
