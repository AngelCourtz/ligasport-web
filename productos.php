<?php
/** RF1: registrar y consultar productos con marca, modelo, talla, color y precio. */
require __DIR__ . '/includes/bootstrap.php';
require_login();
$bajo = (int)cfg('stock_bajo');

$q = trim((string)($_GET['q'] ?? ''));
$marca = trim((string)($_GET['marca'] ?? ''));
$sql = 'SELECT * FROM producto WHERE 1';
$params = [];
if ($q !== '') {
    $sql .= ' AND CONCAT_WS(" ", marca, modelo, color) LIKE ?';
    $params[] = '%' . $q . '%';
}
if ($marca !== '') {
    $sql .= ' AND marca = ?';
    $params[] = $marca;
}
$sql .= ' ORDER BY marca, modelo, color, LENGTH(talla), talla';
$st = db()->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();
$marcas = db()->query('SELECT DISTINCT marca FROM producto ORDER BY marca')->fetchAll(PDO::FETCH_COLUMN);

$titulo = 'Productos';
$activo = 'productos';
require __DIR__ . '/includes/header.php';
?>
<section class="card">
  <form class="row" method="get">
    <div><label>Buscar</label><input name="q" value="<?= e($q) ?>" placeholder="Modelo, marca o color"></div>
    <div><label>Marca</label><select name="marca"><option value="">Todas</option>
      <?php foreach ($marcas as $m): ?><option <?= $m === $marca ? 'selected' : '' ?>><?= e($m) ?></option><?php endforeach; ?></select></div>
    <div class="spacer"><button class="btn secondary">Filtrar</button></div>
    <?php if (is_admin()): ?><div class="spacer"><a class="btn" href="producto_form.php">+ Nuevo producto</a></div><?php endif; ?>
  </form>
</section>
<section class="card">
  <p class="muted"><?= count($rows) ?> productos (cada talla se registra como un producto)</p>
  <div class="table-wrap"><table>
    <tr><th>Código</th><th>Marca</th><th>Modelo</th><th>Color</th><th>Talla</th><th class="num">Precio</th><th class="num">Stock</th><?php if (is_admin()): ?><th></th><?php endif; ?></tr>
    <?php foreach ($rows as $p):
        $s = (int)$p['stock_actual'];
        $cls = $s === 0 ? 'out' : ($s <= $bajo ? 'low' : 'ok'); ?>
      <tr><td class="muted">P<?= str_pad((string)$p['id_producto'], 4, '0', STR_PAD_LEFT) ?></td><td><?= e($p['marca']) ?></td><td><?= e($p['modelo']) ?></td>
        <td><?= e($p['color']) ?></td><td><?= e($p['talla']) ?></td><td class="num"><?= soles($p['precio']) ?></td>
        <td class="num"><span class="badge <?= $cls ?>"><?= $s ?></span></td>
        <?php if (is_admin()): ?><td class="right"><a class="btn small secondary" href="producto_form.php?id=<?= (int)$p['id_producto'] ?>">Editar</a></td><?php endif; ?></tr>
    <?php endforeach; ?>
  </table></div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
