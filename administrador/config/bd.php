<?php
require_once __DIR__ . '/DB.php';

try {
    $db = DB::getInstance();
    $conexion = $db->getConnection();
} catch (Exception $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>