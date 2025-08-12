<?php
require_once 'config/config.php';
require_once 'config/DB.php';
require_once 'config/Auth.php';
require_once 'config/Middleware.php';
require_once 'config/EmailNotifier.php';

// Verificar autenticación de admin
Middleware::requireAdmin();

// Configurar respuesta JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $pedido_id = filter_input(INPUT_POST, 'pedido_id', FILTER_VALIDATE_INT);
    $nuevo_estado = filter_input(INPUT_POST, 'nuevo_estado', FILTER_SANITIZE_STRING);
    $comentario = filter_input(INPUT_POST, 'comentario', FILTER_SANITIZE_STRING);

    if (!$pedido_id || !$nuevo_estado) {
        throw new Exception('Datos inválidos');
    }

    // Estados válidos
    $estados_validos = ['Pendiente', 'Pagado', 'Enviado', 'Entregado'];
    if (!in_array($nuevo_estado, $estados_validos)) {
        throw new Exception('Estado no válido');
    }

    // Obtener pedido actual
    $sql_pedido = "SELECT p.*, uc.nombre, uc.apellido, uc.email 
                   FROM pedidos p 
                   LEFT JOIN usuarios_clientes uc ON p.id_usuario = uc.id 
                   WHERE p.id_pedido = ?";
    $pedido = DB::fetchOne($sql_pedido, [$pedido_id]);

    if (!$pedido) {
        throw new Exception('Pedido no encontrado');
    }

    $estado_anterior = $pedido['estado'];

    // Iniciar transacción
    DB::beginTransaction();

    try {
        // Actualizar estado del pedido
        $sql_update = "UPDATE pedidos SET estado = ?, fecha_actualizacion = NOW() WHERE id_pedido = ?";
        DB::update($sql_update, [$nuevo_estado, $pedido_id]);

        // Registrar cambio en historial
        $sql_historial = "INSERT INTO historial_estados_pedido 
                         (id_pedido, estado_anterior, estado_nuevo, comentario, fecha_cambio, id_admin) 
                         VALUES (?, ?, ?, ?, NOW(), ?)";
        DB::insert($sql_historial, [
            $pedido_id, 
            $estado_anterior, 
            $nuevo_estado, 
            $comentario, 
            $_SESSION['admin_id'] ?? 1
        ]);

        // Enviar notificación por email
        $email_enviado = false;
        if ($nuevo_estado !== $estado_anterior) {
            $email_enviado = EmailNotifier::enviarNotificacionEstado(
                $pedido, 
                $estado_anterior, 
                $nuevo_estado, 
                $comentario
            );

            // Registrar envío en logs
            EmailNotifier::registrarEnvio(
                $pedido_id,
                'cambio_estado',
                $pedido['email'],
                "Actualización de Pedido #{$pedido['numero_pedido']}",
                $email_enviado
            );
        }

        DB::commit();

        $response = [
            'success' => true, 
            'message' => 'Estado actualizado correctamente',
            'nuevo_estado' => $nuevo_estado,
            'email_enviado' => $email_enviado
        ];

        if ($email_enviado) {
            $response['message'] .= ' y notificación enviada al cliente';
        }

        echo json_encode($response);

    } catch (Exception $e) {
        DB::rollback();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>
