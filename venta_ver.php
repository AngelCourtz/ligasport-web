<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();
$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare('SELECT v.*, c.nombre cliente, c.telefono, c.direccion, u.nombre vendedor FROM venta v
    JOIN cliente c USING (id_cliente) JOIN usuario u USING (id_usuario) WHERE v.id_venta = ?');
$st->execute([$id]);
$v = $st->fetch();
if (!$v) {
    flash('La venta no existe.', 'error');
    redirect('ventas.php');
}
$st = db()->prepare('SELECT d.*, p.marca, p.modelo, p.color, p.talla FROM detalle_venta d JOIN producto p USING (id_producto) WHERE d.id_venta = ?');
$st->execute([$id]);
$det = $st->fetchAll();

$titulo = 'Venta N° ' . $id;
$activo = 'ventas';
require __DIR__ . '/includes/header.php';
?>
<section class="card" style="max-width:820px">
  <div class="row">
    <div><label>Fecha</label><?= e(date('d/m/Y H:i', strtotime($v['fecha']))) ?></div>
    <div><label>Cliente</label><?= e($v['cliente']) ?><br><span class="muted"><?= e(trim($v['telefono'] . ' ' . $v['direccion'])) ?></span></div>
    <div><label>Canal</label><span class="badge"><?= e($v['canal']) ?></span></div>
    <div><label>Registrada por</label><?= e($v['vendedor']) ?></div>
  </div>
  <br>
  <div class="table-wrap"><table>
    <tr><th>Producto</th><th>Talla</th><th class="num">Cant.</th><th class="num">Precio</th><th class="num">Subtotal</th></tr>
    <?php foreach ($det as $d): ?>
      <tr><td><?= e("{$d['marca']} {$d['modelo']}") ?> <span class="muted"><?= e($d['color']) ?></span></td><td><?= e($d['talla']) ?></td>
        <td class="num"><?= (int)$d['cantidad'] ?></td><td class="num"><?= soles($d['precio_unitario']) ?></td>
        <td class="num"><?= soles($d['cantidad'] * $d['precio_unitario']) ?></td></tr>
    <?php endforeach; ?>
    <tr><td colspan="4" class="right"><strong>Total</strong></td><td class="num"><strong><?= soles($v['total']) ?></strong></td></tr>
  </table></div>
  <br>
  <div class="no-print"><a class="btn secondary" href="ventas.php">Volver</a> <button class="btn secondary" onclick="print()">Imprimir</button> <a class="btn" href="venta_nueva.php">+ Nueva venta</a></div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
