<?php
/**
 * Configuración del sistema. En XAMPP los valores por defecto funcionan sin cambios.
 * En un hosting, defina las variables de entorno o edite estos valores.
 */
return [
    'db' => [
        'host' => getenv('LIGA_DB_HOST') ?: '127.0.0.1',
        'port' => getenv('LIGA_DB_PORT') ?: '3306',
        'name' => getenv('LIGA_DB_NAME') ?: 'ligasport_db',
        'user' => getenv('LIGA_DB_USER') ?: 'root',
        'pass' => getenv('LIGA_DB_PASS') ?: '',
    ],
    'empresa' => [
        'nombre'    => 'LIGASPORT',
        'direccion' => 'Av. 25 de Diciembre 1940, Collique, Comas',
        // Datos de ejemplo para el chatbot: ajústelos con la información vigente de la empresa.
        'horario'   => 'lunes a sábado de 10:00 a. m. a 8:00 p. m.',
        'pagos'     => 'efectivo, transferencia bancaria y billeteras digitales (Yape o Plin)',
        'whatsapp'  => '51900000000', // número de ejemplo, reemplazar por el real
    ],
    'stock_bajo' => 2, // se considera stock bajo cuando quedan 2 unidades o menos
];
