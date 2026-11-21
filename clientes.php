<?php
/** Registro y consulta de clientes (entidad CLIENTE). */
require __DIR__ . '/includes/bootstrap.php';
require_login();
$pdo = db();
$edit = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $id = post_int('id_cliente');
    $nombre = post_str('nombre');
    $tel = post_str('telefono');
    $dir = post_str('direccion');
    if ($nombre === '') {
        flash('Ingrese el nombre del cliente.', 'error');
    } elseif ($id) {
        $pdo->prepare('UPDATE cliente SET nombre=?, telefono=?, direccion=? WHERE id_cliente=?')->execute([$nombre, $tel, $dir, $id]);
        flash('Cliente actualizado.');
    } else {
        $pdo->prepare('INSERT INTO cliente (nombre, telefono, direccion) VALUES (?,?,?)')->execute([$nombre, $tel, $dir]);
        flash('Cliente registrado. Ya puede seleccionarlo en una nueva venta.');
    }
    redirect('clientes.php');
}

$c = ['id_cliente' => 0, 'nombre' => '', 'telefono' => '', 'direccion' => ''];
if ($edit) {
    $st = $pdo->prepare('SELECT * FROM cliente WHERE id_cliente = ?');
    $st->execute([$edit]);
    $c = $st->fetch() ?: $c;
}
$clientes = $pdo->query('SELECT c.*, COUNT(v.id_venta) compras, COALESCE(SUM(v.total),0) monto FROM cliente c
    LEFT JOIN venta v USING (id_cliente) GROUP BY c.id_cliente ORDER BY c.nombre')->fetchAll();

$titulo = 'Clientes';
$activo = 'clientes';
require __DIR__ . '/includes/header.php';
?>
<section class="card">
  <h2><?= $c['id_cliente'] ? 'Editar cliente' : 'Registrar cliente' ?></h2>
  <form method="post" class="row">
    <?= csrf_field() ?><input type="hidden" name="id_cliente" value="<?= (int)$c['id_cliente'] ?>">
    <div style="flex:2"><label>Nombre</label><input name="nombre" value="<?= e($c['nombre']) ?>" required></div>
    <div><label>Teléfono</label><input name="telefono" value="<?= e($c['telefono']) ?>"></div>
    <div style="flex:2"><label>Dirección o destino de envío</label><input name="direccion" value="<?= e($c['direccion']) ?>"></div>
    <div class="spacer"><button class="btn">Guardar</button></div>
  </form>
</section>
<section class="card">
  <div class="table-wrap"><table>
    <tr><th>Cliente</th><th>Teléfono</th><th>Dirección</th><th class="num">Compras</th><th class="num">Monto</th><th></th></tr>
    <?php foreach ($clientes as $r): ?>
      <tr><td><?= e($r['nombre']) ?></td><td><?= e($r['telefono']) ?></td><td><?= e($r['direccion']) ?></td>
        <td class="num"><?= (int)$r['compras'] ?></td><td class="num"><?= soles($r['monto']) ?></td>
        <td class="right"><a class="btn small secondary" href="clientes.php?id=<?= (int)$r['id_cliente'] ?>">Editar</a></td></tr>
    <?php endforeach; ?>
  </table></div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
