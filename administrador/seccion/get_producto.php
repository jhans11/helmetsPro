<?php
require_once '../config/DB.php';
require_once '../config/Auth.php';
require_once '../config/Middleware.php';

// Verificar autenticación
Middleware::configureSecureSession();
session_start();
Middleware::requireAdmin();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $db = DB::getInstance();
    
    $producto = $db->fetchOne("SELECT * FROM cascos WHERE id = ?", [$id]);
    
    if ($producto) {
        header('Content-Type: application/json');
        echo json_encode($producto);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Producto no encontrado']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID requerido']);
}
