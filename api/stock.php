<?php
/** RF4: consulta de stock por producto y talla (JSON). Usado por la consulta rápida y por el registro de ventas. */
require __DIR__ . '/../includes/bootstrap.php';
if (!user()) {
    json_out(['error' => 'Sesión expirada'], 401);
}
$q = trim((string)($_GET['q'] ?? ''));
$talla = strtoupper(trim((string)($_GET['talla'] ?? '')));
$sql = 'SELECT id_producto, marca, modelo, color, talla, precio, stock_actual FROM producto WHERE 1';
$params = [];
foreach (preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY) as $w) {
    if (ctype_digit($w)) {
        // Números: palabra completa en el modelo ("Jordan 1" no debe traer la talla 41) o talla exacta.
        $sql .= ' AND (CONCAT_WS(" ", marca, modelo, color) REGEXP ? OR talla = ?)';
        $params[] = '\\b' . $w . '\\b';
        $params[] = $w;
    } else {
        $sql .= ' AND (CONCAT_WS(" ", marca, modelo, color) LIKE ? OR talla = ?)';
        $params[] = '%' . $w . '%';
        $params[] = strtoupper($w);
    }
}
if ($talla !== '') {
    $sql .= ' AND talla = ?';
    $params[] = $talla;
}
$sql .= ' ORDER BY marca, modelo, color, LENGTH(talla), talla LIMIT 80';
$st = db()->prepare($sql);
$st->execute($params);
json_out(['productos' => $st->fetchAll(), 'stock_bajo' => (int)cfg('stock_bajo')]);
