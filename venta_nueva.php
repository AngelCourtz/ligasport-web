<?php
/** RF3: registrar cada venta con su canal de origen, actualizando el stock al confirmarla (registro único). */
require __DIR__ . '/includes/bootstrap.php';
$u = require_login();
$pdo = db();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $canal = $_POST['canal'] ?? '';
    $idCliente = post_int('id_cliente');
    $items = json_decode((string)($_POST['items'] ?? '[]'), true);
    try {
        if (!in_array($canal, CANALES, true)) throw new RuntimeException('Seleccione el canal de la venta.');
        if (!is_array($items) || !$items) throw new RuntimeException('Agregue al menos un producto.');
        $pdo->beginTransaction();
        $st = $pdo->prepare('SELECT id_cliente FROM cliente WHERE id_cliente = ?');
        $st->execute([$idCliente]);
        if (!$st->fetchColumn()) throw new RuntimeException('Seleccione un cliente válido.');

        // Bloquea las filas de los productos para evitar vender un stock que ya no existe.
        $lock = $pdo->prepare('SELECT id_producto, precio, stock_actual, CONCAT(marca," ",modelo," T",talla) nombre FROM producto WHERE id_producto = ? FOR UPDATE');
        $lineas = [];
        $total = 0.0;
        foreach ($items as $it) {
            $idp = (int)($it['id'] ?? 0);
            $cant = (int)($it['cantidad'] ?? 0);
            if ($idp <= 0 || $cant <= 0) throw new RuntimeException('Hay una línea de venta no válida.');
            $lock->execute([$idp]);
            $p = $lock->fetch();
            if (!$p) throw new RuntimeException('Un producto de la venta ya no existe.');
            $yaPedido = $lineas[$idp]['cantidad'] ?? 0;
            if ($cant + $yaPedido > (int)$p['stock_actual']) {
                throw new RuntimeException("Stock insuficiente para {$p['nombre']}: quedan {$p['stock_actual']} unidades.");
            }
            $lineas[$idp] = ['cantidad' => $cant + $yaPedido, 'precio' => (float)$p['precio']];
            $total += $cant * (float)$p['precio'];
        }
        $pdo->prepare('INSERT INTO venta (id_cliente, id_usuario, canal, total) VALUES (?,?,?,?)')
            ->execute([$idCliente, $u['id_usuario'], $canal, round($total, 2)]);
        $idVenta = (int)$pdo->lastInsertId();
        $det = $pdo->prepare('INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (?,?,?,?)');
        $upd = $pdo->prepare('UPDATE producto SET stock_actual = stock_actual - ? WHERE id_producto = ?');
        foreach ($lineas as $idp => $l) {
            $det->execute([$idVenta, $idp, $l['cantidad'], $l['precio']]);
            $upd->execute([$l['cantidad'], $idp]);
        }
        $pdo->commit();
        flash("Venta N° $idVenta registrada. El stock se actualizó automáticamente.");
        redirect('venta_ver.php?id=' . $idVenta);
    } catch (RuntimeException $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $ex->getMessage();
    }
}

$clientes = $pdo->query('SELECT id_cliente, nombre, telefono FROM cliente ORDER BY id_cliente = 1 DESC, nombre')->fetchAll();
$titulo = 'Nueva venta';
$activo = 'ventas';
$scripts = ['assets/js/venta.js'];
require __DIR__ . '/includes/header.php';
?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post" id="form-venta">
  <?= csrf_field() ?>
  <input type="hidden" name="items" id="items">
  <div class="grid two">
    <section class="card">
      <h2>1. Buscar producto</h2>
      <div class="row">
        <div style="flex:3"><input id="buscar" placeholder="Modelo, marca o color" autocomplete="off"></div>
        <div><input id="talla" placeholder="Talla" autocomplete="off"></div>
      </div>
      <div class="results" id="resultados"></div>
    </section>
    <section class="card">
      <h2>2. Datos de la venta</h2>
      <div class="row">
        <div style="flex:2"><label>Cliente</label><select name="id_cliente" required>
          <?php foreach ($clientes as $c): ?><option value="<?= (int)$c['id_cliente'] ?>" <?= (int)($_POST['id_cliente'] ?? 1) === (int)$c['id_cliente'] ? 'selected' : '' ?>><?= e($c['nombre'] . ($c['telefono'] ? " · {$c['telefono']}" : '')) ?></option><?php endforeach; ?>
        </select></div>
        <div><label>Canal</label><select name="canal" required>
          <?php foreach (CANALES as $c): ?><option <?= ($_POST['canal'] ?? '') === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select></div>
      </div>
      <p class="muted"><a href="clientes.php">+ Registrar cliente nuevo</a></p>
    </section>
  </div>
  <section class="card">
    <h2>3. Detalle</h2>
    <div class="table-wrap"><table id="carrito">
      <thead><tr><th>Producto</th><th>Talla</th><th class="num">Precio</th><th>Cantidad</th><th class="num">Subtotal</th><th></th></tr></thead>
      <tbody></tbody>
    </table></div>
    <div class="row" style="align-items:center;margin-top:14px">
      <span class="total-box">Total: <span id="total">S/ 0.00</span></span><span class="spacer"></span>
      <button class="btn" id="confirmar" disabled>Confirmar venta</button>
    </div>
  </section>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
