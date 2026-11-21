<?php
/** RNF3: usuarios con contraseña cifrada y permisos por rol (solo administrador). */
require __DIR__ . '/includes/bootstrap.php';
$me = require_admin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'crear') {
        $nombre = post_str('nombre');
        $usuario = strtolower(post_str('usuario'));
        $pass = (string)($_POST['contrasena'] ?? '');
        $rol = ($_POST['rol'] ?? '') === 'administrador' ? 'administrador' : 'vendedor';
        if ($nombre === '' || !preg_match('/^[a-z0-9._-]{3,50}$/', $usuario) || strlen($pass) < 8) {
            flash('Complete el nombre, un usuario válido (3 a 50 caracteres sin espacios) y una contraseña de al menos 8 caracteres.', 'error');
        } else {
            try {
                $pdo->prepare('INSERT INTO usuario (nombre, usuario, contrasena, rol) VALUES (?,?,?,?)')
                    ->execute([$nombre, $usuario, password_hash($pass, PASSWORD_DEFAULT), $rol]);
                flash("Usuario $usuario creado.");
            } catch (PDOException $ex) {
                flash('Ese nombre de usuario ya existe.', 'error');
            }
        }
    } elseif ($accion === 'clave') {
        $id = post_int('id_usuario');
        $pass = (string)($_POST['contrasena'] ?? '');
        if (strlen($pass) < 8) {
            flash('La contraseña debe tener al menos 8 caracteres.', 'error');
        } else {
            $pdo->prepare('UPDATE usuario SET contrasena = ? WHERE id_usuario = ?')->execute([password_hash($pass, PASSWORD_DEFAULT), $id]);
            flash('Contraseña actualizada.');
        }
    }
    redirect('usuarios.php');
}
$usuarios = $pdo->query('SELECT u.id_usuario, u.nombre, u.usuario, u.rol, (SELECT COUNT(*) FROM venta v WHERE v.id_usuario = u.id_usuario) ventas
    FROM usuario u ORDER BY u.rol, u.nombre')->fetchAll();

$titulo = 'Usuarios';
$activo = 'usuarios';
require __DIR__ . '/includes/header.php';
?>
<section class="card">
  <h2>Nuevo usuario</h2>
  <form method="post" class="row" autocomplete="off">
    <?= csrf_field() ?><input type="hidden" name="accion" value="crear">
    <div style="flex:2"><label>Nombre</label><input name="nombre" required></div>
    <div><label>Usuario</label><input name="usuario" required></div>
    <div><label>Contraseña</label><input name="contrasena" type="password" minlength="8" required autocomplete="new-password"></div>
    <div><label>Rol</label><select name="rol"><option value="vendedor">Vendedor</option><option value="administrador">Administrador</option></select></div>
    <div class="spacer"><button class="btn">Crear</button></div>
  </form>
  <p class="muted">El vendedor registra ventas, movimientos y clientes. El administrador además gestiona productos, precios y usuarios.</p>
</section>
<section class="card">
  <div class="table-wrap"><table>
    <tr><th>Nombre</th><th>Usuario</th><th>Rol</th><th class="num">Ventas registradas</th><th>Cambiar contraseña</th></tr>
    <?php foreach ($usuarios as $r): ?>
      <tr><td><?= e($r['nombre']) ?></td><td><?= e($r['usuario']) ?></td><td><span class="badge"><?= e($r['rol']) ?></span></td><td class="num"><?= (int)$r['ventas'] ?></td>
        <td><form method="post" class="row" style="gap:6px"><?= csrf_field() ?><input type="hidden" name="accion" value="clave"><input type="hidden" name="id_usuario" value="<?= (int)$r['id_usuario'] ?>">
          <input name="contrasena" type="password" minlength="8" placeholder="Nueva contraseña" required autocomplete="new-password"><button class="btn small secondary spacer">Guardar</button></form></td></tr>
    <?php endforeach; ?>
  </table></div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
