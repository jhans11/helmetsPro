<?php
/**
 * Clase para manejo de filtros de productos
 * Helmets Pro v2.0 - Sistema de Filtros
 */

class FiltrosProductos {
    private $db;
    
    public function __construct() {
        require_once 'administrador/config/DB.php';
        $this->db = DB::getInstance();
    }
    
    /**
     * Obtener todas las categorías disponibles
     */
    public function obtenerCategorias() {
        $sql = "SELECT DISTINCT categoria FROM cascos WHERE activo = 1 ORDER BY categoria";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Obtener rango de precios (mínimo y máximo)
     */
    public function obtenerRangoPrecios() {
        $sql = "SELECT MIN(precio) as precio_min, MAX(precio) as precio_max 
                FROM cascos WHERE activo = 1";
        return $this->db->fetchOne($sql);
    }
    
    /**
     * Aplicar filtros a los productos
     */
    public function filtrarProductos($filtros = []) {
        $sql = "SELECT * FROM cascos WHERE activo = 1";
        $params = [];
        
        // Filtro por categoría
        if (!empty($filtros['categoria']) && $filtros['categoria'] !== 'todas') {
            $sql .= " AND categoria = ?";
            $params[] = $filtros['categoria'];
        }
        
        // Filtro por rango de precios
        if (!empty($filtros['precio_min'])) {
            $sql .= " AND precio >= ?";
            $params[] = (float)$filtros['precio_min'];
        }
        
        if (!empty($filtros['precio_max'])) {
            $sql .= " AND precio <= ?";
            $params[] = (float)$filtros['precio_max'];
        }
        
        // Filtro por búsqueda de texto
        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (nombre LIKE ? OR descripcion LIKE ?)";
            $busqueda = '%' . $filtros['busqueda'] . '%';
            $params[] = $busqueda;
            $params[] = $busqueda;
        }
        
        // Ordenamiento
        $orden = !empty($filtros['orden']) ? $filtros['orden'] : 'nombre';
        $direccion = !empty($filtros['direccion']) ? $filtros['direccion'] : 'ASC';
        
        // Validar campos de ordenamiento permitidos
        $camposPermitidos = ['nombre', 'precio', 'categoria', 'stock'];
        $orden = in_array($orden, $camposPermitidos) ? $orden : 'nombre';
        $direccion = in_array(strtoupper($direccion), ['ASC', 'DESC']) ? $direccion : 'ASC';
        
        $sql .= " ORDER BY $orden $direccion";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Contar productos con filtros aplicados
     */
    public function contarProductos($filtros = []) {
        $sql = "SELECT COUNT(*) as total FROM cascos WHERE activo = 1";
        $params = [];
        
        // Aplicar los mismos filtros que en filtrarProductos
        if (!empty($filtros['categoria']) && $filtros['categoria'] !== 'todas') {
            $sql .= " AND categoria = ?";
            $params[] = $filtros['categoria'];
        }
        
        if (!empty($filtros['precio_min'])) {
            $sql .= " AND precio >= ?";
            $params[] = (float)$filtros['precio_min'];
        }
        
        if (!empty($filtros['precio_max'])) {
            $sql .= " AND precio <= ?";
            $params[] = (float)$filtros['precio_max'];
        }
        
        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (nombre LIKE ? OR descripcion LIKE ?)";
            $busqueda = '%' . $filtros['busqueda'] . '%';
            $params[] = $busqueda;
            $params[] = $busqueda;
        }
        
        $resultado = $this->db->fetchOne($sql, $params);
        return $resultado['total'];
    }
    
    /**
     * Formatear precio para mostrar
     */
    public static function formatearPrecio($precio) {
        return '$' . number_format($precio, 2, '.', ',');
    }
    
    /**
     * Validar y sanitizar filtros
     */
    public static function sanitizarFiltros($filtros) {
        $filtrosLimpios = [];
        
        // Categoría
        if (!empty($filtros['categoria'])) {
            $filtrosLimpios['categoria'] = filter_var($filtros['categoria'], FILTER_SANITIZE_STRING);
        }
        
        // Precios
        if (!empty($filtros['precio_min'])) {
            $filtrosLimpios['precio_min'] = filter_var($filtros['precio_min'], FILTER_VALIDATE_FLOAT);
        }
        
        if (!empty($filtros['precio_max'])) {
            $filtrosLimpios['precio_max'] = filter_var($filtros['precio_max'], FILTER_VALIDATE_FLOAT);
        }
        
        // Búsqueda
        if (!empty($filtros['busqueda'])) {
            $filtrosLimpios['busqueda'] = filter_var($filtros['busqueda'], FILTER_SANITIZE_STRING);
        }
        
        // Ordenamiento
        if (!empty($filtros['orden'])) {
            $filtrosLimpios['orden'] = filter_var($filtros['orden'], FILTER_SANITIZE_STRING);
        }
        
        if (!empty($filtros['direccion'])) {
            $filtrosLimpios['direccion'] = filter_var($filtros['direccion'], FILTER_SANITIZE_STRING);
        }
        
        return $filtrosLimpios;
    }
}
?>
