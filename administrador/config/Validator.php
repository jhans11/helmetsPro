<?php
/**
 * Clase Validator - Validación y sanitización segura de formularios
 * Implementa filter_input() y validaciones específicas
 */
class Validator {
    
    /**
     * Sanitizar entrada de texto
     */
    public static function sanitizeText($input, $filter = FILTER_SANITIZE_STRING) {
        return filter_input(INPUT_POST, $input, $filter);
    }
    
    /**
     * Sanitizar entrada de email
     */
    public static function sanitizeEmail($input) {
        return filter_input(INPUT_POST, $input, FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Validar email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    /**
     * Sanitizar entrada numérica
     */
    public static function sanitizeNumber($input) {
        return filter_input(INPUT_POST, $input, FILTER_SANITIZE_NUMBER_INT);
    }
    
    /**
     * Validar número entero
     */
    public static function validateInteger($number, $min = null, $max = null) {
        $options = [];
        if ($min !== null) $options['min_range'] = $min;
        if ($max !== null) $options['max_range'] = $max;
        
        return filter_var($number, FILTER_VALIDATE_INT, ['options' => $options]);
    }
    
    /**
     * Validar número decimal
     */
    public static function validateFloat($number, $min = null, $max = null) {
        $options = [];
        if ($min !== null) $options['min_range'] = $min;
        if ($max !== null) $options['max_range'] = $max;
        
        return filter_var($number, FILTER_VALIDATE_FLOAT, ['options' => $options]);
    }
    
    /**
     * Validar URL
     */
    public static function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL);
    }
    
    /**
     * Validar longitud de texto
     */
    public static function validateLength($text, $min = 1, $max = 255) {
        $length = strlen(trim($text));
        return $length >= $min && $length <= $max;
    }
    
    /**
     * Validar tipo MIME de imagen
     */
    public static function validateImageMime($file) {
        $allowed_types = [
            'image/jpeg',
            'image/jpg', 
            'image/png',
            'image/gif',
            'image/webp'
        ];
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        return in_array($mime_type, $allowed_types);
    }
    
    /**
     * Validar tamaño de archivo
     */
    public static function validateFileSize($file, $max_size = 5242880) { // 5MB por defecto
        return $file['size'] <= $max_size;
    }
    
    /**
     * Generar nombre único para archivo
     */
    public static function generateUniqueFilename($original_name, $extension = null) {
        if (!$extension) {
            $extension = pathinfo($original_name, PATHINFO_EXTENSION);
        }
        
        return uniqid() . '_' . time() . '.' . $extension;
    }
    
    /**
     * Validar token CSRF
     */
    public static function validateCSRF($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Obtener token CSRF para formularios
     */
    public static function getCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validar datos requeridos
     */
    public static function validateRequired($data, $fields) {
        $errors = [];
        
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                $errors[] = "El campo '$field' es requerido.";
            }
        }
        
        return $errors;
    }
}
?> 