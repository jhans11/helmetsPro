<?php
require_once 'config.php';
require_once 'DB.php';

/**
 * Clase para manejar notificaciones por email
 */
class EmailNotifier {
    
    // Configuración de email
    private static $config = [
        'from_email' => 'noreply@helmetspro.com',
        'from_name' => 'HelmetsPro',
        'reply_to' => 'soporte@helmetspro.com',
        'smtp_host' => 'localhost', // Cambiar por tu servidor SMTP
        'smtp_port' => 587,
        'smtp_username' => '',
        'smtp_password' => '',
        'smtp_secure' => 'tls'
    ];

    /**
     * Enviar notificación de cambio de estado
     */
    public static function enviarNotificacionEstado($pedido, $estado_anterior, $nuevo_estado, $comentario = '') {
        try {
            $asunto = self::generarAsuntoEstado($pedido, $nuevo_estado);
            $mensaje = self::generarMensajeEstado($pedido, $estado_anterior, $nuevo_estado, $comentario);
            $html = self::generarHTMLEstado($pedido, $estado_anterior, $nuevo_estado, $comentario);

            return self::enviarEmail($pedido['email'], $asunto, $mensaje, $html);
        } catch (Exception $e) {
            error_log("Error enviando notificación de estado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar notificación de pedido creado
     */
    public static function enviarConfirmacionPedido($pedido) {
        try {
            $asunto = "Confirmación de Pedido #{$pedido['numero_pedido']} - HelmetsPro";
            $mensaje = self::generarMensajeConfirmacion($pedido);
            $html = self::generarHTMLConfirmacion($pedido);

            return self::enviarEmail($pedido['email'], $asunto, $mensaje, $html);
        } catch (Exception $e) {
            error_log("Error enviando confirmación de pedido: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generar asunto del email según estado
     */
    private static function generarAsuntoEstado($pedido, $nuevo_estado) {
        $numero_pedido = $pedido['numero_pedido'];
        
        switch ($nuevo_estado) {
            case 'Pagado':
                return "¡Pago Confirmado! Pedido #$numero_pedido - HelmetsPro";
            case 'Enviado':
                return "¡Tu Pedido #$numero_pedido ha sido Enviado! - HelmetsPro";
            case 'Entregado':
                return "¡Pedido #$numero_pedido Entregado! - HelmetsPro";
            default:
                return "Actualización de Pedido #$numero_pedido - HelmetsPro";
        }
    }

    /**
     * Generar mensaje de texto plano para cambio de estado
     */
    private static function generarMensajeEstado($pedido, $estado_anterior, $nuevo_estado, $comentario) {
        $nombre = $pedido['nombre'] . ' ' . $pedido['apellido'];
        $numero_pedido = $pedido['numero_pedido'];
        $total = number_format($pedido['total'], 2);

        $mensaje = "Hola $nombre,\n\n";
        $mensaje .= "Tu pedido #$numero_pedido ha cambiado de estado.\n\n";
        $mensaje .= "Estado anterior: $estado_anterior\n";
        $mensaje .= "Nuevo estado: $nuevo_estado\n\n";

        if (!empty($comentario)) {
            $mensaje .= "Comentario: $comentario\n\n";
        }

        $mensaje .= "Detalles del pedido:\n";
        $mensaje .= "- Número de pedido: #$numero_pedido\n";
        $mensaje .= "- Total: $$total\n";
        $mensaje .= "- Fecha: " . date('d/m/Y H:i', strtotime($pedido['fecha_creacion'])) . "\n\n";

        $mensaje .= "Puedes consultar el estado de tu pedido en tu cuenta:\n";
        $mensaje .= "https://helmetspro.com/mi-cuenta.php\n\n";

        $mensaje .= "Si tienes alguna pregunta, no dudes en contactarnos.\n\n";
        $mensaje .= "Gracias por confiar en HelmetsPro,\n";
        $mensaje .= "Equipo HelmetsPro\n\n";
        $mensaje .= "---\n";
        $mensaje .= "Este es un email automático, por favor no respondas a este mensaje.";

        return $mensaje;
    }

    /**
     * Generar HTML para cambio de estado
     */
    private static function generarHTMLEstado($pedido, $estado_anterior, $nuevo_estado, $comentario) {
        $nombre = $pedido['nombre'] . ' ' . $pedido['apellido'];
        $numero_pedido = $pedido['numero_pedido'];
        $total = number_format($pedido['total'], 2);
        $fecha = date('d/m/Y H:i', strtotime($pedido['fecha_creacion']));

        $estado_color = self::getColorEstado($nuevo_estado);
        $estado_icono = self::getIconoEstado($nuevo_estado);

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Actualización de Pedido</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .estado-badge { display: inline-block; background: $estado_color; color: white; padding: 10px 20px; border-radius: 25px; font-weight: bold; margin: 10px 0; }
                .pedido-info { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #007bff; }
                .btn { display: inline-block; background: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px; }
                .comentario { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🛒 HelmetsPro</h1>
                    <h2>Actualización de Pedido</h2>
                </div>
                
                <div class='content'>
                    <p>Hola <strong>$nombre</strong>,</p>
                    
                    <p>Tu pedido ha cambiado de estado:</p>
                    
                    <div style='text-align: center;'>
                        <div class='estado-badge'>
                            $estado_icono $nuevo_estado
                        </div>
                    </div>
                    
                    <div class='pedido-info'>
                        <h3>📋 Detalles del Pedido</h3>
                        <p><strong>Número de pedido:</strong> #$numero_pedido</p>
                        <p><strong>Estado anterior:</strong> $estado_anterior</p>
                        <p><strong>Nuevo estado:</strong> $nuevo_estado</p>
                        <p><strong>Total:</strong> $$total</p>
                        <p><strong>Fecha:</strong> $fecha</p>
                    </div>";

        if (!empty($comentario)) {
            $html .= "
                    <div class='comentario'>
                        <strong>💬 Comentario:</strong><br>
                        $comentario
                    </div>";
        }

        $html .= "
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='https://helmetspro.com/mi-cuenta.php' class='btn'>
                            👤 Ver Mi Cuenta
                        </a>
                    </div>
                    
                    <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
                    
                    <p>Gracias por confiar en HelmetsPro,<br>
                    <strong>Equipo HelmetsPro</strong></p>
                </div>
                
                <div class='footer'>
                    <p>Este es un email automático, por favor no respondas a este mensaje.</p>
                    <p>© 2024 HelmetsPro. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Generar mensaje de confirmación de pedido
     */
    private static function generarMensajeConfirmacion($pedido) {
        $nombre = $pedido['nombre'] . ' ' . $pedido['apellido'];
        $numero_pedido = $pedido['numero_pedido'];
        $total = number_format($pedido['total'], 2);

        $mensaje = "¡Hola $nombre!\n\n";
        $mensaje .= "¡Gracias por tu compra en HelmetsPro!\n\n";
        $mensaje .= "Tu pedido ha sido confirmado:\n\n";
        $mensaje .= "📋 Número de pedido: #$numero_pedido\n";
        $mensaje .= "💰 Total: $$total\n";
        $mensaje .= "�� Fecha: " . date('d/m/Y H:i', strtotime($pedido['fecha_creacion'])) . "\n\n";
        $mensaje .= "Estaremos procesando tu pedido y te mantendremos informado sobre su estado.\n\n";
        $mensaje .= "Puedes consultar el estado de tu pedido en tu cuenta:\n";
        $mensaje .= "https://helmetspro.com/mi-cuenta.php\n\n";
        $mensaje .= "¡Gracias por confiar en HelmetsPro!\n\n";
        $mensaje .= "Saludos,\n";
        $mensaje .= "Equipo HelmetsPro";

        return $mensaje;
    }

    /**
     * Generar HTML de confirmación de pedido
     */
    private static function generarHTMLConfirmacion($pedido) {
        $nombre = $pedido['nombre'] . ' ' . $pedido['apellido'];
        $numero_pedido = $pedido['numero_pedido'];
        $total = number_format($pedido['total'], 2);
        $fecha = date('d/m/Y H:i', strtotime($pedido['fecha_creacion']));

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Confirmación de Pedido</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .pedido-info { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745; }
                .btn { display: inline-block; background: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
                .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🛒 HelmetsPro</h1>
                    <h2>¡Pedido Confirmado!</h2>
                </div>
                
                <div class='content'>
                    <p>¡Hola <strong>$nombre</strong>!</p>
                    
                    <p>¡Gracias por tu compra en HelmetsPro! Tu pedido ha sido confirmado y estamos procesándolo.</p>
                    
                    <div class='pedido-info'>
                        <h3>📋 Detalles del Pedido</h3>
                        <p><strong>Número de pedido:</strong> #$numero_pedido</p>
                        <p><strong>Total:</strong> $$total</p>
                        <p><strong>Fecha:</strong> $fecha</p>
                        <p><strong>Estado:</strong> Pendiente de procesamiento</p>
                    </div>
                    
                    <p>Estaremos procesando tu pedido y te mantendremos informado sobre su estado a través de este email.</p>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='https://helmetspro.com/mi-cuenta.php' class='btn'>
                            👤 Ver Mi Cuenta
                        </a>
                    </div>
                    
                    <p>¡Gracias por confiar en HelmetsPro!</p>
                    
                    <p>Saludos,<br>
                    <strong>Equipo HelmetsPro</strong></p>
                </div>
                
                <div class='footer'>
                    <p>© 2024 HelmetsPro. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Obtener color para el estado
     */
    private static function getColorEstado($estado) {
        switch ($estado) {
            case 'Pendiente':
                return '#ffc107';
            case 'Pagado':
                return '#17a2b8';
            case 'Enviado':
                return '#007bff';
            case 'Entregado':
                return '#28a745';
            default:
                return '#6c757d';
        }
    }

    /**
     * Obtener icono para el estado
     */
    private static function getIconoEstado($estado) {
        switch ($estado) {
            case 'Pendiente':
                return '⏳'; // Reloj de arena en espera
            case 'Pagado':
                return '💳'; // Tarjeta de crédito (pago realizado)
            case 'Enviado':
                return '📦'; // Paquete en camino
            case 'Entregado':
                return '📬'; // Sobre/paquete entregado
            default:
                return '📋'; // Lista de control
        }
    }
    

    /**
     * Enviar email usando PHP mail() o SMTP
     */
    private static function enviarEmail($to, $subject, $text_message, $html_message = '') {
        // En desarrollo, simular envío
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            return self::simularEnvio($to, $subject, $text_message, $html_message);
        }

        // En producción, usar PHPMailer o similar
        return self::enviarEmailReal($to, $subject, $text_message, $html_message);
    }

    /**
     * Simular envío de email (para desarrollo)
     */
    private static function simularEnvio($to, $subject, $text_message, $html_message) {
        $log_file = __DIR__ . '/../../logs/emails.log';
        $log_dir = dirname($log_file);
        
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }

        $log_entry = date('Y-m-d H:i:s') . " | TO: $to | SUBJECT: $subject\n";
        $log_entry .= "TEXT: $text_message\n";
        if ($html_message) {
            $log_entry .= "HTML: $html_message\n";
        }
        $log_entry .= str_repeat('-', 80) . "\n";

        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);

        // También mostrar en consola si es CLI
        if (php_sapi_name() === 'cli') {
            echo "EMAIL SIMULADO:\n";
            echo "Para: $to\n";
            echo "Asunto: $subject\n";
            echo "Mensaje: $text_message\n\n";
        }

        return true;
    }

    /**
     * Enviar email real (para producción)
     */
    private static function enviarEmailReal($to, $subject, $text_message, $html_message) {
        // Aquí implementarías PHPMailer o similar
        // Por ahora, usar mail() básico
        
        $headers = [
            'From: ' . self::$config['from_name'] . ' <' . self::$config['from_email'] . '>',
            'Reply-To: ' . self::$config['reply_to'],
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8'
        ];

        $message = $html_message ?: $text_message;

        return mail($to, $subject, $message, implode("\r\n", $headers));
    }

    /**
     * Registrar intento de envío en la base de datos
     */
    public static function registrarEnvio($pedido_id, $tipo, $destinatario, $asunto, $exitoso) {
        try {
            $sql = "INSERT INTO logs_emails (id_pedido, tipo, destinatario, asunto, exitoso, fecha_envio) 
                    VALUES (?, ?, ?, ?, ?, NOW())";
            
            return DB::insert($sql, [$pedido_id, $tipo, $destinatario, $asunto, $exitoso ? 1 : 0]);
        } catch (Exception $e) {
            error_log("Error registrando envío de email: " . $e->getMessage());
            return false;
        }
    }
}
?>
