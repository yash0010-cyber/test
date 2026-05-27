<?php
/**
 * Database Configuration
 * 
 * This class handles all database connections and queries
 */

class Database
{
    private $host;
    private $db_name;
    private $user;
    private $password;
    private $charset = 'utf8mb4';
    private $conn;

    public function __construct()
    {
        $this->loadEnvironment();
    }

    /**
     * Load environment variables from .env file
     */
    private function loadEnvironment()
    {
        if (file_exists(__DIR__ . '/../.env')) {
            $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    $_ENV[$key] = $value;
                }
            }
        }

        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->db_name = $_ENV['DB_NAME'] ?? 'house_rental';
        $this->user = $_ENV['DB_USERNAME'] ?? 'root';
        $this->password = $_ENV['DB_PASSWORD'] ?? '';
    }

    /**
     * Connect to database
     */
    public function connect()
    {
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            
            $this->conn = new PDO(
                $dsn,
                $this->user,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            return $this->conn;
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please contact administrator.");
        }
    }

    /**
     * Get database connection
     */
    public function getConnection()
    {
        if ($this->conn === null) {
            $this->connect();
        }
        return $this->conn;
    }

    /**
     * Execute a query
     */
    public function execute($query, $params = [])
    {
        try {
            $stmt = $this->getConnection()->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query Execution Error: " . $e->getMessage());
            throw new Exception("Database query failed");
        }
    }

    /**
     * Fetch single row
     */
    public function fetchRow($query, $params = [])
    {
        $stmt = $this->execute($query, $params);
        return $stmt->fetch();
    }

    /**
     * Fetch all rows
     */
    public function fetchAll($query, $params = [])
    {
        $stmt = $this->execute($query, $params);
        return $stmt->fetchAll();
    }

    /**
     * Insert data and return last insert ID
     */
    public function insert($table, $data)
    {
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));
        $query = "INSERT INTO $table ($columns) VALUES ($values)";
        
        $this->execute($query, array_values($data));
        return $this->getConnection()->lastInsertId();
    }

    /**
     * Update data
     */
    public function update($table, $data, $where)
    {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($data)));
        $whereClause = implode(' AND ', array_map(fn($k) => "$k = ?", array_keys($where)));
        
        $query = "UPDATE $table SET $set WHERE $whereClause";
        $params = array_merge(array_values($data), array_values($where));
        
        return $this->execute($query, $params);
    }

    /**
     * Delete data
     */
    public function delete($table, $where)
    {
        $whereClause = implode(' AND ', array_map(fn($k) => "$k = ?", array_keys($where)));
        $query = "DELETE FROM $table WHERE $whereClause";
        
        return $this->execute($query, array_values($where));
    }

    /**
     * Count rows
     */
    public function count($table, $where = [])
    {
        $query = "SELECT COUNT(*) as count FROM $table";
        
        if (!empty($where)) {
            $whereClause = implode(' AND ', array_map(fn($k) => "$k = ?", array_keys($where)));
            $query .= " WHERE $whereClause";
            $result = $this->fetchRow($query, array_values($where));
        } else {
            $result = $this->fetchRow($query);
        }
        
        return $result['count'] ?? 0;
    }

    /**
     * Close connection
     */
    public function closeConnection()
    {
        $this->conn = null;
    }
}
