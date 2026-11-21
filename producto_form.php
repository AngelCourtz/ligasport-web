<?php
/** RF1: alta y edición de productos (solo administrador). El stock solo cambia mediante movimientos o ventas. */
require __DIR__ . '/includes/bootstrap.php';
$u = require_admin();
$pdo = db();
$id = (int)($_GET['id'] ?? 0);
$p = ['marca' => '', 'modelo' => '', 'color' => '', 'talla' => '', 'precio' => '', 'stock_actual' => 0];
if ($id) {
    $st = $pdo->prepare('SELECT * FROM producto WHERE id_producto = ?');
    $st->execute([$id]);
    $p = $st->fetch() ?: redirect('productos.php');
}
$errores = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $data = [
        'marca' => post_str('marca'), 'modelo' => post_str('modelo'), 'color' => post_str('color'),
        'talla' => strtoupper(post_str('talla')), 'precio' => str_replace(',', '.', post_str('precio')),
    ];
    $inicial = max(0, post_int('stock_inicial'));
    foreach (['marca' => 'la marca', 'modelo' => 'el modelo', 'color' => 'el color', 'talla' => 'la talla'] as $k => $n) {
        if ($data[$k] === '') $errores[] = "Ingrese $n.";
    }
    if (!is_numeric($data['precio']) || (float)$data['precio'] < 0) $errores[] = 'Ingrese un precio válido.';
    if (mb_strlen($data['talla']) > 5) $errores[] = 'La talla admite hasta 5 caracteres.';
    if (!$errores) {
        try {
            $pdo->beginTransaction();
            if ($id) {
                $pdo->prepare('UPDATE producto SET marca=?, modelo=?, color=?, talla=?, precio=? WHERE id_producto=?')
                    ->execute([$data['marca'], $data['modelo'], $data['color'], $data['talla'], $data['precio'], $id]);
            } else {
                $pdo->prepare('INSERT INTO producto (marca, modelo, color, talla, precio, stock_actual) VALUES (?,?,?,?,?,0)')
                    ->execute([$data['marca'], $data['modelo'], $data['color'], $data['talla'], $data['precio']]);
                $id = (int)$pdo->lastInsertId();
                if ($inicial > 0) {
                    $pdo->prepare("INSERT INTO movimiento_inventario (id_producto, id_usuario, tipo, cantidad) VALUES (?,?, 'entrada', ?)")
                        ->execute([$id, $u['id_usuario'], $inicial]);
                    $pdo->prepare('UPDATE producto SET stock_actual = stock_actual + ? WHERE id_producto = ?')->execute([$inicial, $id]);
                }
            }
            $pdo->commit();
            flash('Producto guardado correctamente.');
            redirect('productos.php');
        } catch (PDOException $ex) {
            $pdo->rollBack();
            $errores[] = $ex->getCode() === '23000' ? 'Ya existe un producto con la misma marca, modelo, color y talla.' : 'No se pudo guardar el producto.';
        }
    }
    $p = array_merge($p, $data);
}
$titulo = $id ? 'Editar producto' : 'Nuevo producto';
$activo = 'productos';
require __DIR__ . '/includes/header.php';
?>
<section class="card" style="max-width:720px">
  <?php foreach ($errores as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post">
    <?= csrf_field() ?>
    <div class="row">
      <div><label>Marca</label><input name="marca" value="<?= e($p['marca']) ?>" list="marcas" required><datalist id="marcas"><option>Nike</option><option>Jordan</option></datalist></div>
      <div><label>Modelo</label><input name="modelo" value="<?= e($p['modelo']) ?>" required></div>
    </div><br>
    <div class="row">
      <div><label>Color</label><input name="color" value="<?= e($p['color']) ?>" required></div>
      <div><label>Talla</label><input name="talla" value="<?= e($p['talla']) ?>" maxlength="5" required></div>
      <div><label>Precio (S/)</label><input name="precio" value="<?= e($p['precio']) ?>" inputmode="decimal" required></div>
      <?php if (!$id): ?><div><label>Stock inicial</label><input name="stock_inicial" type="number" min="0" value="0"></div><?php endif; ?>
    </div>
    <?php if ($id): ?><p class="muted">Stock actual: <strong><?= (int)$p['stock_actual'] ?></strong>. Para cambiarlo, registre una entrada o salida en <a href="inventario.php?id=<?= $id ?>">Inventario</a>.</p><?php endif; ?>
    <br><button class="btn">Guardar</button> <a class="btn secondary" href="productos.php">Cancelar</a>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
