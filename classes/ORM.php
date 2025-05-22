<?php
class ORM {
    private $connection;
    private $table;
    private $sql = '';
    private $params = [];

    public function __construct(Database $db) {
        $this->connection = $db;
    }

    // Set the table to operate on
    public function table($table) {
        $this->table = $table;
        return $this;
    }

    // Select fields from the table
    public function select($fields = ['*']) {
        $this->sql = 'SELECT ' . implode(', ', $fields) . ' FROM ' . $this->table;
        return $this;
    }

    // Add a WHERE clause
    public function where($field, $operator, $value) {
        $this->sql .= (strpos($this->sql, 'WHERE') === false ? ' WHERE ' : ' AND ') . "$field $operator ?";
        $this->params[] = $value;
        return $this;
    }

    // Add an OR WHERE clause
    public function orWhere($field, $operator, $value) {
        $this->sql .= (strpos($this->sql, 'WHERE') === false ? ' WHERE ' : ' OR ') . "$field $operator ?";
        $this->params[] = $value;
        return $this;
    }

    // Execute the query and fetch all results
    public function get() {
        $stmt = $this->connection->query($this->sql, $this->params);
        $this->reset(); // Reset SQL and params after execution
        return $stmt;
    }

    // Execute the query and fetch a single result
    public function first() {
        $stmt = $this->connection->query($this->sql, $this->params);
        $this->reset(); // Reset SQL and params after execution
        return $stmt[0] ?? null;
    }

    // Insert a new record
    public function insert($data) {
        $fields = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $this->sql = "INSERT INTO $this->table ($fields) VALUES ($placeholders)";
        $this->params = array_values($data);
        return $this->execute();
    }

    // Update existing records
    public function update($data) {
        $setClause = implode(', ', array_map(fn($field) => "$field = ?", array_keys($data)));
        $this->sql = "UPDATE $this->table SET $setClause " . $this->sql;
        $this->params = array_merge(array_values($data), $this->params);
        return $this->execute();
    }

    // Delete records
    public function delete() {
        $this->sql = "DELETE FROM $this->table " . $this->sql;
        return $this->execute();
    }

    // Execute the query
    private function execute() {
        $stmt = $this->connection->query($this->sql, $this->params);
        $this->reset(); // Reset SQL and params after execution
        return $stmt;
    }

    // Reset SQL and parameters
    private function reset() {
        $this->sql = '';
        $this->params = [];
    }
}