<?php

namespace Models\Database;

class Database {
    private $connection;

    public function __construct($host, $user, $password, $dbname) {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION, // Uses PDO constants
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC     // Uses PDO constants
        ];
        $this->connection = new \PDO($dsn, $user, $password, $options); // Creates a PDO object
    }

    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}