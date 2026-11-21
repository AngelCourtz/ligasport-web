<?php
/**
 * RF6: chatbot por reglas para consultas frecuentes de clientes (Anexo 2 de la tesis).
 * Responde con el mismo stock de la base de datos que ven los vendedores.
 */
require __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['error' => 'Método no permitido'], 405);
}
// Límite simple de uso por sesión para evitar abusos.
$now = time();
$_SESSION['bot_hits'] = array_values(array_filter($_SESSION['bot_hits'] ?? [], fn($t) => $t > $now - 60));
if (count($_SESSION['bot_hits']) >= 30) {
    json_out(['respuesta' => 'Recibimos muchas consultas seguidas. Espere un momento e intente de nuevo.']);
}
$_SESSION['bot_hits'][] = $now;

$in = json_decode(file_get_contents('php://input'), true);
$msg = mb_substr(trim((string)($in['mensaje'] ?? '')), 0, 300);

function norm(string $s): string
{
    $s = mb_strtolower($s, 'UTF-8');
    $s = strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n', "'" => '']);
    return preg_replace('/[^a-z0-9 ]+/', ' ', $s);
}
function tiene(string $t, array $palabras): bool
{
    foreach ($palabras as $p) {
        if (preg_match('/\b' . preg_quote($p, '/') . '/', $t)) return true;
    }
    return false;
}
$wa = 'https://wa.me/' . cfg('empresa.whatsapp');
$t = norm($msg);

// Detecta el modelo mencionado comparando palabras con el catálogo.
$modelos = db()->query('SELECT marca, modelo, color, MIN(precio) precio FROM producto GROUP BY marca, modelo, color')->fetchAll();
$stop = ['nike', 'jordan', 'air', 'de', 'la', 'el', 'en', 'y', 'retro', 'low', 'mid'];
$palabras = array_values(array_filter(explode(' ', $t), fn($w) => strlen($w) > 1 || ctype_digit($w)));
$mejor = null;
$mejorPts = 0;
foreach ($modelos as $m) {
    $toks = explode(' ', norm($m['marca'] . ' ' . $m['modelo'] . ' ' . $m['color']));
    $pts = 0;
    foreach ($toks as $tk) {
        if ($tk === '' || !in_array($tk, $palabras, true)) continue;
        if (in_array($tk, $stop, true)) {
            $pts += 0.3;
        } elseif (ctype_digit($tk)) {
            // "1", "4" o "90" identifican el modelo; un número entre 34 y 47 suele ser la talla.
            $pts += preg_match('/^(3[4-9]|4[0-7])$/', $tk) ? 0.5 : 1.5;
        } else {
            $pts += 1;
        }
    }
    if ($pts > $mejorPts) {
        $mejorPts = $pts;
        $mejor = $m;
    }
}
if ($mejorPts < 1) $mejor = null;
// Talla: número de calzado (34–47) o talla de prenda.
$talla = null;
if (preg_match('/\btalla\s*([0-9]{2}|xs|s|m|l|xl|xxl)\b/', $t, $mm) || preg_match('/\b(3[4-9]|4[0-7])\b/', $t, $mm)) {
    $talla = strtoupper($mm[1]);
}

function disponibilidad(array $m, ?string $talla, bool $conPrecio = true): string
{
    $st = db()->prepare('SELECT talla, stock_actual, precio FROM producto WHERE marca=? AND modelo=? AND color=? ORDER BY LENGTH(talla), talla');
    $st->execute([$m['marca'], $m['modelo'], $m['color']]);
    $rows = $st->fetchAll();
    $nombre = "{$m['marca']} {$m['modelo']} ({$m['color']})";
    $disp = array_values(array_filter($rows, fn($r) => (int)$r['stock_actual'] > 0));
    $precio = $conPrecio ? ' Precio: ' . soles($rows[0]['precio'] ?? 0) . '.' : '';
    if ($talla !== null) {
        foreach ($rows as $r) {
            if (strtoupper($r['talla']) === $talla) {
                return (int)$r['stock_actual'] > 0
                    ? "Sí, tenemos el $nombre en talla $talla disponible.$precio ¿Desea separarlo?"
                    : "Por ahora no tenemos el $nombre en talla $talla. " . ($disp ? 'Tallas disponibles: ' . implode(', ', array_column($disp, 'talla')) . '.' : 'Este modelo está agotado por el momento.');
            }
        }
        return "El $nombre no se vende en talla $talla. " . ($disp ? 'Tallas disponibles: ' . implode(', ', array_column($disp, 'talla')) . '.' : '');
    }
    return $disp
        ? ($conPrecio ? "El $nombre está disponible en las tallas: " : 'Tallas disponibles: ') . implode(', ', array_column($disp, 'talla')) . '.' . $precio
        : "El $nombre está agotado por el momento. Escríbanos y le avisamos cuando llegue.";
}

$menu = ['Ver disponibilidad', 'Precios', 'Ubicación y horario', 'Envíos', 'Formas de pago', 'Hablar con un vendedor'];
$respuesta = '';
$extra = [];

if (trim($t) === '' || (!$mejor && count($palabras) <= 3 && tiene($t, ['hola', 'buenas', 'buenos', 'menu']))) {
    $respuesta = '¡Hola! Soy el asistente de LIGASPORT. Puedo ayudarle con la disponibilidad de modelos y tallas, precios, ubicación, envíos y formas de pago. ¿Qué modelo busca?';
} elseif ($mejor && tiene($t, ['precio', 'cuanto', 'cuesta', 'vale', 'costo'])) {             // regla 2
    $respuesta = "El {$mejor['marca']} {$mejor['modelo']} ({$mejor['color']}) cuesta " . soles($mejor['precio']) . '. ' . disponibilidad($mejor, $talla, false);
} elseif ($mejor) {                                                                               // regla 1
    $respuesta = disponibilidad($mejor, $talla);
} elseif (tiene($t, ['stock', 'talla', 'hay', 'disponib', 'tienen', 'queda', 'modelo'])) {       // regla 1 sin modelo
    $lista = array_unique(array_map(fn($m) => "{$m['marca']} {$m['modelo']}", $modelos));
    $respuesta = 'Tenemos estos modelos: ' . implode(', ', $lista) . '. Escriba el modelo y la talla, por ejemplo: "Dunk Low talla 42".';
} elseif (tiene($t, ['precio', 'cuanto', 'cuesta', 'vale'])) {                                   // regla 2 sin modelo
    $respuesta = '¿De qué modelo desea saber el precio? Por ejemplo: "precio Air Force 1".';
} elseif (tiene($t, ['donde', 'direccion', 'local', 'tienda', 'ubicacion', 'horario', 'hora', 'abren', 'atienden'])) { // regla 3
    $respuesta = 'Nuestro local está en ' . cfg('empresa.direccion') . '. Atendemos ' . rtrim(cfg('empresa.horario'), '.') . '.';
} elseif (tiene($t, ['envio', 'delivery', 'provincia', 'shalom', 'mandan', 'llega', 'entrega'])) { // regla 4
    $respuesta = 'Puede recoger su pedido en el local. En Lima enviamos por motorizado o vehículo propio, y a otras ciudades por Shalom.';
} elseif (tiene($t, ['pago', 'pagar', 'yape', 'plin', 'transferencia', 'efectivo', 'tarjeta'])) { // regla 5
    $respuesta = 'Aceptamos ' . cfg('empresa.pagos') . '.';
} elseif (tiene($t, ['original', 'replica', 'garantia', 'falso', 'autentic'])) {                  // regla 6
    $respuesta = 'Todos nuestros productos son originales. Trabajamos con Nike y Jordan adquiridos en establecimientos y outlets autorizados.';
} elseif (tiene($t, ['mayor', 'cantidad', 'docena', 'mayorista', 'lote'])) {                       // regla 7
    $respuesta = 'Las compras por mayor se coordinan directamente con un vendedor.';
    $extra = ['whatsapp' => $wa];
} else {                                                                                          // regla 8
    $respuesta = 'No tengo una respuesta para esa consulta, pero un vendedor puede ayudarle por WhatsApp.';
    $extra = ['whatsapp' => $wa];
}
if (tiene($t, ['vendedor', 'humano', 'asesor', 'whatsapp'])) {
    $respuesta = 'Con gusto. Un vendedor le atenderá por WhatsApp.';
    $extra = ['whatsapp' => $wa];
}
json_out(['respuesta' => $respuesta, 'sugerencias' => $menu] + $extra);
