<?php
/**
 * Plantillas de email para diferentes tipos de notificaciones
 */
class PlantillasEmail {
    
    /**
     * Plantilla base para todos los emails
     */
    public static function getPlantillaBase($contenido, $titulo = 'HelmetsPro') {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>$titulo</title>
            <style>
                body { 
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                    line-height: 1.6; 
                    color: #333; 
                    margin: 0; 
                    padding: 0; 
                    background-color: #f4f4f4; 
                }
                .container { 
                    max-width: 600px; 
                    margin: 20px auto; 
                    background: white; 
                    border-radius: 10px; 
                    overflow: hidden; 
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1); 
                }
                .header { 
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                    color: white; 
                    padding: 30px; 
                    text-align: center; 
                }
                .header h1 { margin: 0; font-size: 28px; }
                .header p { margin: 10px 0 0 0; opacity: 0.9; }
                .content { 
                    padding: 30px; 
                    background: white; 
                }
                .footer { 
                    background: #f8f9fa; 
                    padding: 20px; 
                    text-align: center; 
                    color: #666; 
                    font-size: 12px; 
                    border-top: 1px solid #dee2e6; 
                }
                .btn { 
                    display: inline-block; 
                    background: #007bff; 
                    color: white; 
                    padding: 12px 25px; 
                    text-decoration: none; 
                    border-radius: 5px; 
                    margin: 15px 0; 
                    font-weight: bold; 
                }
                .btn:hover { background: #0056b3; }
                .info-box { 
                    background: #f8f9fa; 
                    border-left: 4px solid #007bff; 
                    padding: 15px; 
                    margin: 15px 0; 
                    border-radius: 0 5px 5px 0; 
                }
                .success-box { 
                    background: #d4edda; 
                    border-left: 4px solid #28a745; 
                    color: #155724; 
                }
                .warning-box { 
                    background: #fff3cd; 
                    border-left: 4px solid #ffc107; 
                    color: #856404; 
                }
                .text-center { text-align: center; }
                .text-muted { color: #6c757d; }
                .mb-0 { margin-bottom: 0; }
                .mt-3 { margin-top: 15px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🛒 HelmetsPro</h1>
                    <p>Tu tienda de confianza para cascos de calidad</p>
                </div>
                
                <div class='content'>
                    $contenido
                </div>
                
                <div class='footer'>
                    <p>© 2024 HelmetsPro. Todos los derechos reservados.</p>
                    <p>Este es un email automático, por favor no respondas a este mensaje.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Plantilla para confirmación de pedido
     */
    public static function getPlantillaConfirmacion($pedido) {
        $contenido = "
            <h2>¡Pedido Confirmado!</h2>
            <p>Hola <strong>{$pedido['nombre']} {$pedido['apellido']}</strong>,</p>
            
            <p>¡Gracias por tu compra en HelmetsPro! Tu pedido ha sido confirmado y estamos procesándolo.</p>
            
            <div class='info-box success-box'>
                <h3>📋 Detalles del Pedido</h3>
                <p><strong>Número de pedido:</strong> #{$pedido['numero_pedido']}</p>
                <p><strong>Total:</strong> $" . number_format($pedido['total'], 2) . "</p>
                <p><strong>Fecha:</strong> " . date('d/m/Y H:i', strtotime($pedido['fecha_creacion'])) . "</p>
                <p><strong>Estado:</strong> Pendiente de procesamiento</p>
            </div>
            
            <p>Estaremos procesando tu pedido y te mantendremos informado sobre su estado.</p>
            
            <div class='text-center'>
                <a href='https://helmetspro.com/mi-cuenta.php' class='btn'>
                    👤 Ver Mi Cuenta
                </a>
            </div>
            
            <p class='mt-3'>¡Gracias por confiar en HelmetsPro!</p>
            
            <p>Saludos,<br>
            <strong>Equipo HelmetsPro</strong></p>";

        return self::getPlantillaBase($contenido, "Confirmación de Pedido #{$pedido['numero_pedido']}");
    }

    /**
     * Plantilla para cambio de estado
     */
    public static function getPlantillaCambioEstado($pedido, $estado_anterior, $nuevo_estado, $comentario = '') {
        $estado_color = self::getColorEstado($nuevo_estado);
        $estado_icono = self::getIconoEstado($nuevo_estado);

        $contenido = "
            <h2>Actualización de Pedido</h2>
            <p>Hola <strong>{$pedido['nombre']} {$pedido['apellido']}</strong>,</p>
            
            <p>Tu pedido ha cambiado de estado:</p>
            
            <div class='text-center'>
                <div style='display: inline-block; background: $estado_color; color: white; padding: 10px 20px; border-radius: 25px; font-weight: bold; margin: 10px 0;'>
                    $estado_icono $nuevo_estado
                </div>
            </div>
            
            <div class='info-box'>
                <h3>📋 Detalles del Pedido</h3>
                <p><strong>Número de pedido:</strong> #{$pedido['numero_pedido']}</p>
                <p><strong>Estado anterior:</strong> $estado_anterior</p>
                <p><strong>Nuevo estado:</strong> $nuevo_estado</p>
                <p><strong>Total:</strong> $" . number_format($pedido['total'], 2) . "</p>
                <p><strong>Fecha:</strong> " . date('d/m/Y H:i', strtotime($pedido['fecha_creacion'])) . "</p>
            </div>";

        if (!empty($comentario)) {
            $contenido .= "
            <div class='info-box warning-box'>
                <strong>💬 Comentario:</strong><br>
                $comentario
            </div>";
        }

        $contenido .= "
            <div class='text-center'>
                <a href='https://helmetspro.com/mi-cuenta.php' class='btn'>
                    👤 Ver Mi Cuenta
                </a>
            </div>
            
            <p class='mt-3'>Si tienes alguna pregunta, no dudes en contactarnos.</p>
            
            <p>Gracias por confiar en HelmetsPro,<br>
            <strong>Equipo HelmetsPro</strong></p>";

        return self::getPlantillaBase($contenido, "Actualización de Pedido #{$pedido['numero_pedido']}");
    }

    /**
     * Obtener color para el estado
     */
    private static function getColorEstado($estado) {
        switch ($estado) {
            case 'Pendiente': return '#ffc107';
            case 'Pagado': return '#17a2b8';
            case 'Enviado': return '#007bff';
            case 'Entregado': return '#28a745';
            default: return '#6c757d';
        }
    }

    /**
     * Obtener icono para el estado
     */
    private static function getIconoEstado($estado) {
        switch ($estado) {
            case 'Pendiente': 
                return '⏳'; // Esperando
            case 'Pagado': 
                return '💰'; // Pago realizado
            case 'Enviado': 
                return '📦'; // Paquete en camino
            case 'Entregado': 
                return '✅'; // Confirmado/recibido
            default: 
                return '📋'; // Otro estado
        }
    }
    
}
?>
