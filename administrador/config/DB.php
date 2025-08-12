<?php
/**
 * Clase DB - Gestión segura de conexión a base de datos
 * Helmets Pro v2.0 - Versión Mejorada
 * 
 * Características:
 * - Patrón Singleton para conexión única
 * - Configuración centralizada
 * - Logging de consultas
 * - Manejo de errores mejorado
 * - Validación de parámetros
 * - Métodos adicionales útiles
 */

require_once 'config.php';

class DB {
    private static $instance = null;
    private $connection;
    private $queryLog = [];
    private $lastQuery;
    private $lastParams;
    
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Establecer conexión a la base de datos
     */
    private function connect() {
        try {
            $config = Config::getDBConfig();
            
            // Usar variables de entorno si están disponibles
            $host = Config::env('DB_HOST', $config['host']);
            $dbname = Config::env('DB_NAME', $config['dbname']);
            $username = Config::env('DB_USER', $config['username']);
            $password = Config::env('DB_PASS', $config['password']);
            $charset = $config['charset'];
            $options = $config['options'];
            
            $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
            
            $this->connection = new PDO($dsn, $username, $password, $options);
            
            // Log de conexión exitosa
            $this->logQuery("Conexión establecida", [], 'CONNECTION');
            
        } catch (PDOException $e) {
            $this->logError("Error de conexión: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }
    
    /**
     * Patrón Singleton: Retorna instancia única de la DB
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Retorna la conexión PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Validar parámetros de consulta
     */
    private function validateParams($params) {
        if (!is_array($params)) {
            throw new Exception("Los parámetros deben ser un array");
        }
        
        foreach ($params as $key => $value) {
            if (is_null($value)) {
                throw new Exception("Parámetro '$key' no puede ser null");
            }
        }
        
        return true;
    }
    
    /**
     * Log de consultas
     */
    private function logQuery($sql, $params = [], $type = 'QUERY') {
        if (Config::get('logging.log_queries', false)) {
            $logEntry = [
                'timestamp' => date('Y-m-d H:i:s'),
                'type' => $type,
                'sql' => $sql,
                'params' => $params,
                'execution_time' => microtime(true)
            ];
            
            $this->queryLog[] = $logEntry;
            
            // Guardar en archivo si está habilitado
            $logFile = Config::get('logging.log_file');
            if ($logFile) {
                $logMessage = sprintf(
                    "[%s] %s: %s | Params: %s\n",
                    $logEntry['timestamp'],
                    $type,
                    $sql,
                    json_encode($params)
                );
                error_log($logMessage, 3, $logFile);
            }
        }
    }
    
    /**
     * Log de errores
     */
    private function logError($message) {
        if (Config::get('logging.log_errors', false)) {
            $logFile = Config::get('logging.log_file');
            if ($logFile) {
                $logMessage = sprintf(
                    "[%s] ERROR: %s\n",
                    date('Y-m-d H:i:s'),
                    $message
                );
                error_log($logMessage, 3, $logFile);
            }
        }
    }
    
    /**
     * Método para realizar consultas seguras con logging
     */
    public function query($sql, $params = []) {
        try {
            // Validar parámetros
            $this->validateParams($params);
            
            // Guardar para logging
            $this->lastQuery = $sql;
            $this->lastParams = $params;
            
            // Preparar y ejecutar consulta
            $startTime = microtime(true);
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            $executionTime = microtime(true) - $startTime;
            
            // Log de consulta
            $this->logQuery($sql, $params, 'QUERY');
            
            return $stmt;
            
        } catch (PDOException $e) {
            $this->logError("Error en consulta: " . $e->getMessage() . " | SQL: $sql | Params: " . json_encode($params));
            throw new Exception("Error en consulta: " . $e->getMessage());
        }
    }
    
    /**
     * Método para obtener un solo registro
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Método para obtener múltiples registros
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Método para insertar datos
     */
    public function insert($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $this->connection->lastInsertId();
    }
    
    /**
     * Método para actualizar datos
     */
    public function update($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Método para eliminar datos
     */
    public function delete($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Obtener el último ID insertado
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Obtener el número de filas afectadas
     */
    public function rowCount() {
        return $this->lastQuery ? $this->connection->rowCount() : 0;
    }
    
    /**
     * Verificar si la conexión está activa
     */
    public function isConnected() {
        try {
            $this->connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Obtener estadísticas de consultas
     */
    public function getQueryStats() {
        return [
            'total_queries' => count($this->queryLog),
            'last_query' => $this->lastQuery,
            'last_params' => $this->lastParams,
            'is_connected' => $this->isConnected()
        ];
    }
    
    /**
     * Obtener log de consultas
     */
    public function getQueryLog() {
        return $this->queryLog;
    }
    
    /**
     * Limpiar log de consultas
     */
    public function clearQueryLog() {
        $this->queryLog = [];
    }
    
    /**
     * Iniciar transacción
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Confirmar transacción
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Revertir transacción
     */
    public function rollback() {
        return $this->connection->rollback();
    }
    
    /**
     * Verificar si hay una transacción activa
     */
    public function inTransaction() {
        return $this->connection->inTransaction();
    }
    
    /**
     * Escapar string para consultas
     */
    public function escape($string) {
        return $this->connection->quote($string);
    }
    
    /**
     * Obtener información de la base de datos
     */
    public function getDatabaseInfo() {
        try {
            $version = $this->fetchOne("SELECT VERSION() as version");
            $database = $this->fetchOne("SELECT DATABASE() as database_name");
            
            return [
                'version' => $version['version'] ?? 'Unknown',
                'database' => $database['database_name'] ?? 'Unknown',
                'connection_status' => $this->isConnected() ? 'Connected' : 'Disconnected'
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'connection_status' => 'Error'
            ];
        }
    }
}
?> 