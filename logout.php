<?php
session_start();
require_once 'clases/Usuario.php';

// Crear instancia de usuario y hacer logout
$usuario = new Usuario();
$usuario->logout();

// Limpiar carrito también
if (isset($_SESSION['carrito'])) {
    unset($_SESSION['carrito']);
}

// Mensaje de éxito
session_start();
$_SESSION['mensaje'] = 'Sesión cerrada correctamente';
$_SESSION['tipo_mensaje'] = 'success';

// Redirigir al inicio
header('Location: index.php');
exit();
?>