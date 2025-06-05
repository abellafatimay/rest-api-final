<?php

namespace Models\ORM;

use Models\Database\Database;


class ORM {
    private $connection;
    private $table;
    private $sql = '';
    private $params = [];
    private $whereConditions = [];
    private $whereParams = [];
    private $orderByClause = '';
    private $joinClauses = [];

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
        // Convert string to array if string is passed
        if (is_string($fields)) {
            $fields = [$fields];
        }
        
        // For each field, prefix with table name if it doesn't contain a dot
        $selectedFields = array_map(function($field) {
            // Skip if it's already qualified with a table name or is a wildcard
            if (strpos($field, '.') !== false || $field === '*') {
                return $field;
            }
            // Add table name prefix
            return $this->table . '.' . $field;
        }, $fields);
        
        $this->sql = 'SELECT ' . implode(', ', $selectedFields) . ' FROM ' . $this->table . ' ';
        return $this;
    }

    // Add a WHERE clause
    public function where($field, $operator, $value) {
        $this->whereConditions[] = "$field $operator ?";
        $this->whereParams[] = $value;
        return $this;
    }

    // Add an OR WHERE clause
    public function orWhere($field, $operator, $value) {
        // For simplicity, treat as AND for now, or implement OR logic if needed
        $this->whereConditions[] = "$field $operator ?";
        $this->whereParams[] = $value;
        return $this;
    }

    // Order results
    public function orderBy(string $column, string $direction = 'ASC'): self {
        $direction = strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'ASC';
        }
        
        $this->orderByClause = " ORDER BY {$column} {$direction}";
        return $this;
    }

    /**
     * Add a JOIN clause to the query
     */
    public function join($table, $first, $operator, $second): self {
        // Make sure the table name is properly escaped
        $table = trim($table, '`');
        
        // Handle the case where the join is on a foreign key
        if (strpos($first, '.') === false) {
            $first = $this->table . '.' . $first;
        }
        if (strpos($second, '.') === false) {
            $second = $table . '.' . $second;
        }

        $this->joinClauses[] = " INNER JOIN `{$table}` ON {$first} {$operator} {$second}";
        return $this;
    }

    /**
     * Add a LEFT JOIN clause to the query
     */
    public function leftJoin($table, $first, $operator, $second): self {
        // Make sure the table name is properly escaped
        $table = trim($table, '`');
        $this->joinClauses[] = " LEFT JOIN `{$table}` ON {$first} {$operator} {$second}";
        return $this;
    }

    /**
     * Handle book-category many-to-many relationship
     */
    public function withCategories(): self {
        $this->join('book_categories', 'id', '=', 'book_id')
             ->join('categories', 'book_categories.category_id', '=', 'id');
        return $this;
    }

    /**
     * Add support for composite keys in WHERE clauses
     */
    public function whereComposite(array $conditions): self {
        $whereClause = [];
        foreach ($conditions as $field => $value) {
            $whereClause[] = "$field = ?";
            $this->whereConditions[] = "$field = ?";
            $this->whereParams[] = $value;
        }
        // Do NOT modify $this->sql here!
        return $this;
    }

    // Execute the query and fetch all results
    public function get() {
        // Insert JOINs after FROM <table>
        if (!empty($this->joinClauses)) {
            // Find position after 'FROM <table>'
            if (preg_match('/^(SELECT\s.+?\sFROM\s+\S+)/i', $this->sql, $matches)) {
                $fromPos = strlen($matches[1]);
                // Insert JOINs right after FROM <table>
                $this->sql = substr($this->sql, 0, $fromPos) . implode(' ', $this->joinClauses) . substr($this->sql, $fromPos);
            } else {
                // Fallback: append JOINs at the end
                $this->sql .= implode(' ', $this->joinClauses);
            }
        }

        // Add WHERE conditions
        if (!empty($this->whereConditions)) {
            $this->sql .= ' WHERE ' . implode(' AND ', $this->whereConditions);
            $this->params = array_merge($this->params, $this->whereParams);
        }

        // Add ORDER BY if set
        if (!empty($this->orderByClause)) {
            $this->sql .= $this->orderByClause;
        }

        // For debugging
        error_log("Generated SQL: " . $this->sql);
        error_log("Parameters: " . print_r($this->params, true));

        $result = $this->connection->query($this->sql, $this->params);
        $this->reset();
        return $result;
    }

    // Execute the query and fetch a single result
    public function first() {
        // If no SELECT clause exists, add one
        if (strpos($this->sql, 'SELECT') === false) {
            $this->sql = 'SELECT * FROM ' . $this->table . ' ' . $this->sql;
        }
        
        // Add LIMIT 1 to get only the first result
        $this->sql .= ' LIMIT 1';
        
        // Execute the query
        $result = $this->connection->query($this->sql, $this->params);
        
        // Reset SQL and params
        $this->reset();
        
        // Return the first row or null if nothing found
        return $result ? $result[0] : null;
    }

    // Insert a new record
    public function insert($data) {
        $fields = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $this->sql = "INSERT INTO $this->table ($fields) VALUES ($placeholders)";
        $this->params = array_values($data);
        
        // Execute the query
        $result = $this->connection->query($this->sql, $this->params);
        
        // If the insert was successful, get the last insert ID
        if ($result) {
            // Call lastInsertId() directly on the connection's PDO object
            $lastId = $this->connection->lastInsertId();
            $this->reset();
            return $lastId;
        }
        
        $this->reset();
        return false;
    }

    // Update existing records
    public function update($data) {
        $setClause = implode(', ', array_map(fn($field) => "$field = ?", array_keys($data)));
        $this->sql = "UPDATE $this->table SET $setClause";
        if (!empty($this->whereConditions)) {
            $this->sql .= ' WHERE ' . implode(' AND ', $this->whereConditions);
            $this->params = array_merge(array_values($data), $this->whereParams);
        } else {
            $this->params = array_values($data);
        }
        return $this->execute();
    }

    // Delete records
    public function delete() {
        $this->sql = "DELETE FROM $this->table";
        if (!empty($this->whereConditions)) {
            $this->sql .= ' WHERE ' . implode(' AND ', $this->whereConditions);
            $this->params = $this->whereParams;
        }
        return $this->execute();
    }

    // Execute the query
    private function execute() {
        $stmt = $this->connection->query($this->sql, $this->params);
        $this->reset(); // Reset SQL and params after execution
        return $stmt;
    }

    /**
     * Reset query builder state
     */
    private function reset(): void {
        $this->sql = '';
        $this->params = [];
        $this->whereConditions = [];
        $this->whereParams = [];
        $this->joinClauses = [];
        $this->orderByClause = '';
    }

    // Count records in the table
    public function count(string $column = '*'): int {
        $query = "SELECT COUNT($column) as count FROM {$this->table}";
        
        if (!empty($this->whereConditions)) {
            $query .= ' WHERE ' . implode(' AND ', $this->whereConditions);
            $params = $this->whereParams;
        } else {
            $params = [];
        }

        $result = $this->connection->query($query, $params);
        $this->reset();
        
        return isset($result[0]['count']) ? (int) $result[0]['count'] : 0;
    }
}