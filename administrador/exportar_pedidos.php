<?php
require_once 'config/config.php';
require_once 'config/DB.php';
require_once 'config/Auth.php';
require_once 'config/Middleware.php';

// Verificar autenticación de admin
Middleware::requireAdmin();

// Configurar headers para descarga CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=pedidos_' . date('Y-m-d_H-i-s') . '.csv');

// Crear archivo CSV
$output = fopen('php://output', 'w');

// BOM para UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Encabezados CSV
fputcsv($output, [
    'ID Pedido',
    'Número Pedido',
    'Cliente',
    'Email',
    'Teléfono',
    'Fecha Creación',
    'Estado',
    'Subtotal',
    'IVA',
    'Total',
    'Dirección',
    'Ciudad',
    'Código Postal',
    'País'
]);

// Filtros
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';

// Construir consulta con filtros
$where_conditions = [];
$params = [];

if (!empty($filtro_estado)) {
    $where_conditions[] = "p.estado = ?";
    $params[] = $filtro_estado;
}

if (!empty($filtro_fecha)) {
    $where_conditions[] = "DATE(p.fecha_creacion) = ?";
    $params[] = $filtro_fecha;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Obtener pedidos
$sql = "SELECT p.*, uc.nombre, uc.apellido, uc.email, uc.telefono,
               de.direccion, de.ciudad, de.codigo_postal, de.pais
        FROM pedidos p 
        LEFT JOIN usuarios_clientes uc ON p.id_usuario = uc.id 
        LEFT JOIN direcciones_envio de ON p.id_direccion_envio = de.id_direccion
        $where_clause 
        ORDER BY p.fecha_creacion DESC";

$pedidos = DB::fetchAll($sql, $params);

// Escribir datos
foreach ($pedidos as $pedido) {
    $subtotal = $pedido['total'] / 1.19; // Asumiendo 19% IVA
    $iva = $pedido['total'] - $subtotal;
    
    fputcsv($output, [
        $pedido['id_pedido'],
        $pedido['numero_pedido'],
        $pedido['nombre'] . ' ' . $pedido['apellido'],
        $pedido['email'],
        $pedido['telefono'] ?? '',
        date('d/m/Y H:i', strtotime($pedido['fecha_creacion'])),
        $pedido['estado'],
        number_format($subtotal, 2),
        number_format($iva, 2),
        number_format($pedido['total'], 2),
        $pedido['direccion'] ?? '',
        $pedido['ciudad'] ?? '',
        $pedido['codigo_postal'] ?? '',
        $pedido['pais'] ?? ''
    ]);
}

fclose($output);
?>
