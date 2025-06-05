<?php

namespace Models\Database;

class Database {
    private $connection;

    public function __construct($host, $user, $password, $dbname) {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION, 
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC     
        ];
        $this->connection = new \PDO($dsn, $user, $password, $options);
    }

    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $success = $stmt->execute($params);
        
        // For SELECT queries - return the results
        if (stripos(trim($sql), 'SELECT') === 0) {
            return $stmt->fetchAll();
        }
        
        // For INSERT, UPDATE, DELETE queries - return true/false
        return $success;
    }
    
    // Add this method to return the ID of the last inserted row
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    // Add this method to your existing Database class
    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }
}