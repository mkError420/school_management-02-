<?php
/**
 * Database Connection Class
 * School Management System
 */

require_once CONFIG_PATH . '/config.php';

class DatabaseConnection {
    private static $instance = null;
    private $connection;
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (APP_ENV === 'development') {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("Database connection failed. Please try again later.");
            }
        }
    }
    
    /**
     * Get singleton instance
     * @return DatabaseConnection
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {}
}

/**
 * Database Query Builder Class
 */
class QueryBuilder {
    private $pdo;
    private $query;
    private $bindings = [];
    private $table;
    
    public function __construct($table = null) {
        $this->pdo = DatabaseConnection::getInstance()->getConnection();
        if ($table) {
            $this->table = $table;
        }
    }
    
    /**
     * Set table name
     * @param string $table
     * @return self
     */
    public function table($table) {
        $this->table = $table;
        return $this;
    }
    
    /**
     * Select query
     * @param string $columns
     * @return self
     */
    public function select($columns = '*') {
        $this->query = "SELECT $columns FROM {$this->table}";
        return $this;
    }
    
    /**
     * Insert query
     * @param array $data
     * @return bool
     */
    public function insert($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $this->query = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $this->bindings = array_values($data);
        
        return $this->execute();
    }
    
    /**
     * Update query
     * @param array $data
     * @return self
     */
    public function update($data) {
        $setClause = [];
        foreach ($data as $column => $value) {
            $setClause[] = "$column = ?";
        }
        
        $this->query = "UPDATE {$this->table} SET " . implode(', ', $setClause);
        $this->bindings = array_merge(array_values($data), $this->bindings);
        
        return $this;
    }
    
    /**
     * Delete query
     * @return self
     */
    public function delete() {
        $this->query = "DELETE FROM {$this->table}";
        return $this;
    }
    
    /**
     * Where clause
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return self
     */
    public function where($column, $operator = '=', $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        if (empty($this->query)) {
            $this->query = "SELECT * FROM {$this->table}";
        }
        
        $this->query .= " WHERE $column $operator ?";
        $this->bindings[] = $value;
        
        return $this;
    }
    
    /**
     * And where clause
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return self
     */
    public function andWhere($column, $operator = '=', $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->query .= " AND $column $operator ?";
        $this->bindings[] = $value;
        
        return $this;
    }
    
    /**
     * Or where clause
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return self
     */
    public function orWhere($column, $operator = '=', $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        // Initialize query if empty
        if (empty($this->query)) {
            $this->query = "SELECT * FROM {$this->table}";
        }
        
        // If no WHERE clause exists, add WHERE instead of OR
        if (strpos($this->query, 'WHERE') === false) {
            $this->query .= " WHERE $column $operator ?";
        } else {
            $this->query .= " OR $column $operator ?";
        }
        $this->bindings[] = $value;
        
        return $this;
    }
    
    /**
     * Order by clause
     * @param string $column
     * @param string $direction
     * @return self
     */
    public function orderBy($column, $direction = 'ASC') {
        $this->query .= " ORDER BY $column $direction";
        return $this;
    }
    
    /**
     * Limit clause
     * @param int $limit
     * @param int $offset
     * @return self
     */
    public function limit($limit, $offset = 0) {
        $this->query .= " LIMIT $limit OFFSET $offset";
        return $this;
    }
    
    /**
     * Join clause
     * @param string $table
     * @param string $first
     * @param string $operator
     * @param string $second
     * @param string $type
     * @return self
     */
    public function join($table, $first, $operator, $second, $type = 'INNER') {
        $this->query .= " $type JOIN $table ON $first $operator $second";
        return $this;
    }
    
    /**
     * Left join
     * @param string $table
     * @param string $first
     * @param string $operator
     * @param string $second
     * @return self
     */
    public function leftJoin($table, $first, $operator, $second) {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }
    
    /**
     * Right join
     * @param string $table
     * @param string $first
     * @param string $operator
     * @param string $second
     * @return self
     */
    public function rightJoin($table, $first, $operator, $second) {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }
    
    /**
     * Group by clause
     * @param string $columns
     * @return self
     */
    public function groupBy($columns) {
        $this->query .= " GROUP BY $columns";
        return $this;
    }
    
    /**
     * Having clause
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return self
     */
    public function having($column, $operator, $value) {
        $this->query .= " HAVING $column $operator ?";
        $this->bindings[] = $value;
        return $this;
    }
    
    /**
     * Execute query
     * @return bool
     */
    public function execute() {
        try {
            $stmt = $this->pdo->prepare($this->query);
            return $stmt->execute($this->bindings);
        } catch (PDOException $e) {
            if (APP_ENV === 'development') {
                die("Query failed: " . $e->getMessage());
            } else {
                return false;
            }
        }
    }
    
    /**
     * Get all results
     * @return array
     */
    public function get() {
        // If no query is set, create a basic select query
        if (empty($this->query)) {
            $this->query = "SELECT * FROM {$this->table}";
        }
        
        try {
            $stmt = $this->pdo->prepare($this->query);
            $stmt->execute($this->bindings);
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            if (APP_ENV === 'development') {
                die("Query failed: " . $e->getMessage());
            } else {
                return [];
            }
        }
    }
    
    /**
     * Get first result
     * @return object|null
     */
    public function first() {
        // If no query is set, create a basic select query
        if (empty($this->query)) {
            $this->query = "SELECT * FROM {$this->table}";
        }
        
        try {
            $stmt = $this->pdo->prepare($this->query);
            $stmt->execute($this->bindings);
            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            if (APP_ENV === 'development') {
                die("Query failed: " . $e->getMessage());
            } else {
                return null;
            }
        }
    }
    
    /**
     * Get row count
     * @return int
     */
    public function count() {
        // If no query is set, create a basic count query
        if (empty($this->query)) {
            $this->query = "SELECT * FROM {$this->table}";
        }
        
        try {
            $stmt = $this->pdo->prepare($this->query);
            $stmt->execute($this->bindings);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            if (APP_ENV === 'development') {
                die("Query failed: " . $e->getMessage());
            } else {
                return 0;
            }
        }
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
     * Get raw query (for debugging)
     * @return string
     */
    public function getQuery() {
        return $this->query;
    }
    
    /**
     * Get bindings (for debugging)
     * @return array
     */
    public function getBindings() {
        return $this->bindings;
    }
}
