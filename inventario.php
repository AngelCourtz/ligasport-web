<?php
/** RF2: registrar entradas y salidas de inventario, actualizando el stock automáticamente. */
require __DIR__ . '/includes/bootstrap.php';
$u = require_login();
$pdo = db();
$sel = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $idp = post_int('id_producto');
    $tipo = $_POST['tipo'] ?? '';
    $cant = post_int('cantidad');
    if (!in_array($tipo, ['entrada', 'salida'], true) || $cant <= 0 || !$idp) {
        flash('Complete el producto, el tipo y una cantidad mayor que cero.', 'error');
        redirect('inventario.php');
    }
    try {
        $pdo->beginTransaction();
        $st = $pdo->prepare('SELECT stock_actual, CONCAT(marca," ",modelo," T",talla) nombre FROM producto WHERE id_producto = ? FOR UPDATE');
        $st->execute([$idp]);
        $prod = $st->fetch();
        if (!$prod) throw new RuntimeException('El producto no existe.');
        if ($tipo === 'salida' && $cant > (int)$prod['stock_actual']) {
            throw new RuntimeException("No hay stock suficiente: quedan {$prod['stock_actual']} unidades.");
        }
        $pdo->prepare('INSERT INTO movimiento_inventario (id_producto, id_usuario, tipo, cantidad) VALUES (?,?,?,?)')
            ->execute([$idp, $u['id_usuario'], $tipo, $cant]);
        $pdo->prepare('UPDATE producto SET stock_actual = stock_actual ' . ($tipo === 'entrada' ? '+' : '-') . ' ? WHERE id_producto = ?')
            ->execute([$cant, $idp]);
        $pdo->commit();
        flash(ucfirst($tipo) . " de $cant unidad(es) registrada: {$prod['nombre']}.");
    } catch (RuntimeException $ex) {
        $pdo->rollBack();
        flash($ex->getMessage(), 'error');
    }
    redirect('inventario.php?id=' . $idp);
}

$productos = $pdo->query('SELECT id_producto, marca, modelo, color, talla, stock_actual FROM producto ORDER BY marca, modelo, color, LENGTH(talla), talla')->fetchAll();
$sql = 'SELECT m.*, p.marca, p.modelo, p.color, p.talla, u.nombre usuario FROM movimiento_inventario m
        JOIN producto p USING (id_producto) JOIN usuario u USING (id_usuario)';
$params = [];
if ($sel) {
    $sql .= ' WHERE m.id_producto = ?';
    $params[] = $sel;
}
$st = $pdo->prepare($sql . ' ORDER BY m.fecha DESC, m.id_movimiento DESC LIMIT 60');
$st->execute($params);
$movs = $st->fetchAll();

$titulo = 'Inventario';
$activo = 'inventario';
require __DIR__ . '/includes/header.php';
?>
<section class="card">
  <h2>Registrar movimiento</h2>
  <form method="post" class="row">
    <?= csrf_field() ?>
    <div style="flex:3"><label>Producto</label>
      <select name="id_producto" required><option value="">Seleccione…</option>
        <?php foreach ($productos as $p): ?>
          <option value="<?= (int)$p['id_producto'] ?>" <?= $sel === (int)$p['id_producto'] ? 'selected' : '' ?>>
            <?= e("{$p['marca']} {$p['modelo']} · {$p['color']} · Talla {$p['talla']} (stock: {$p['stock_actual']})") ?></option>
        <?php endforeach; ?></select></div>
    <div><label>Tipo</label><select name="tipo"><option value="entrada">Entrada (compra o reposición)</option><option value="salida">Salida (ajuste, daño o devolución)</option></select></div>
    <div><label>Cantidad</label><input name="cantidad" type="number" min="1" value="1" required></div>
    <div class="spacer"><button class="btn">Registrar</button></div>
  </form>
  <p class="muted">Las ventas descuentan el stock automáticamente; aquí se registran las compras en Minka, outlets o proveedores y los ajustes del conteo físico.</p>
</section>
<section class="card">
  <div class="row" style="align-items:center"><h2 style="margin:0">Historial de movimientos<?= $sel ? ' del producto' : '' ?></h2><span class="spacer"></span><?php if ($sel): ?><a class="btn small secondary" href="inventario.php">Ver todos</a><?php endif; ?></div>
  <div class="table-wrap"><table>
    <tr><th>Fecha</th><th>Producto</th><th>Talla</th><th>Tipo</th><th class="num">Cantidad</th><th>Registrado por</th></tr>
    <?php foreach ($movs as $m): ?>
      <tr><td><?= e(date('d/m/Y H:i', strtotime($m['fecha']))) ?></td><td><?= e("{$m['marca']} {$m['modelo']}") ?> <span class="muted"><?= e($m['color']) ?></span></td>
        <td><?= e($m['talla']) ?></td><td><span class="badge <?= $m['tipo'] === 'entrada' ? 'ok' : 'low' ?>"><?= e($m['tipo']) ?></span></td>
        <td class="num"><?= (int)$m['cantidad'] ?></td><td><?= e($m['usuario']) ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$movs): ?><tr><td colspan="6" class="muted">Sin movimientos registrados.</td></tr><?php endif; ?>
  </table></div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
