<?php
/**
 * Database Configuration
 * School Management System
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'school_management';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    
    private $pdo;
    private $stmt;
    
    public function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => true,
        ];
        
        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
    
    /**
     * Prepare statement with query
     * @param string $sql
     * @return void
     */
    public function prepare($sql) {
        $this->stmt = $this->pdo->prepare($sql);
    }
    
    /**
     * Bind values to prepared statement
     * @param string $param
     * @param mixed $value
     * @param int $type
     * @return void
     */
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        
        $this->stmt->bindValue($param, $value, $type);
    }
    
    /**
     * Execute the prepared statement
     * @return bool
     */
    public function execute() {
        return $this->stmt->execute();
    }
    
    /**
     * Get result set as array of objects
     * @return array
     */
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get single result as object
     * @return object
     */
    public function single() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Get row count
     * @return int
     */
    public function rowCount() {
        return $this->stmt->rowCount();
    }
    
    /**
     * Get last inserted ID
     * @return string
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Begin transaction
     * @return bool
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     * @return bool
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     * @return bool
     */
    public function rollBack() {
        return $this->pdo->rollBack();
    }
    
    /**
     * Get PDO instance
     * @return PDO
     */
    public function getPdo() {
        return $this->pdo;
    }
    
    /**
     * Debug query - for development only
     * @return void
     */
    public function debugDumpParams() {
        $this->stmt->debugDumpParams();
    }
}
