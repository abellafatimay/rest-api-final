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
    private $joinClauses = [];  // Store JOIN clauses
    private $limitClause = '';
    private $offsetClause = '';
    private $currentWhereGroupType = 'AND'; // To track if we are in an AND or OR chain
    private $groupByClause = ''; // Add this property with the other private properties

    public function __construct(Database $db) {
        $this->connection = $db;
    }

    // Set the table to operate on
    public function table($table) {
        $this->table = $table;
        // Reset parts of the query that are table-specific or might carry over
        $this->sql = ''; // select() will rebuild this
        $this->params = []; // Reset general params
        $this->whereConditions = [];
        $this->whereParams = [];
        $this->joinClauses = [];
        // Keep orderBy, limit, offset if you want them to persist across table changes,
        // otherwise reset them too. For typical use, resetting them is safer.
        $this->orderByClause = '';
        $this->limitClause = '';
        $this->offsetClause = '';
        $this->currentWhereGroupType = 'AND'; // Reset group type

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
            // Ensure $this->table is set before calling this, which table() does.
            if (empty($this->table)) {
                // Or throw an exception: "Table not set before select"
                error_log("ORM Warning: Table not set before select. Field '{$field}' may not be qualified.");
                return $field; 
            }
            return $this->table . '.' . $field;
        }, $fields);
        
        // If $this->sql is already partially built (e.g. by table()), append, otherwise start new.
        // However, table() now resets $this->sql, so this should always be a new SELECT.
        $this->sql = 'SELECT ' . implode(', ', $selectedFields) . ' FROM `' . $this->table . '` '; // Added backticks for table
        return $this;
    }

    // Add a WHERE clause
    public function where($field, $operator, $value) {
        // If this is the first condition, or the previous was an OR, start a new AND group
        if (empty($this->whereConditions) || $this->currentWhereGroupType === 'OR_PENDING') {
            $this->whereConditions[] = ['type' => 'AND', 'condition' => "$field $operator ?"];
        } else { // Add to existing AND group
            $this->whereConditions[count($this->whereConditions)-1]['condition'] .= " AND $field $operator ?";
        }
        $this->whereParams[] = $value;
        $this->currentWhereGroupType = 'AND';
        return $this;
    }

    // Add an OR WHERE clause
    public function orWhere($field, $operator, $value) {
        if (empty($this->whereConditions)) {
            // If orWhere is the first condition, treat it as a normal where
            return $this->where($field, $operator, $value);
        }
        // Add as a new OR condition group
        $this->whereConditions[] = ['type' => 'OR', 'condition' => "$field $operator ?"];
        $this->whereParams[] = $value;
        $this->currentWhereGroupType = 'OR_PENDING'; // Signal that the next 'where' should start a new AND group if not another orWhere
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
        // Store the JOIN clause
        $this->joinClauses[] = "INNER JOIN `$table` ON $first $operator $second";
        return $this;
    }
    
    /**
     * Add a LEFT JOIN clause to the query
     */
    public function leftJoin($table, $first, $operator, $second): self {
        // Store the LEFT JOIN clause
        $this->joinClauses[] = "LEFT JOIN `$table` ON $first $operator $second";
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

    /**
     * Execute query and get results
     */
    public function get() {
        // Start with basic SELECT
        if (strpos($this->sql, 'SELECT') === false) {
            $this->select();
        }
        
        // Insert JOINs after FROM table
        if (!empty($this->joinClauses)) {
            // Find position after 'FROM table'
            if (preg_match('/^(SELECT\s.+?\sFROM\s+\S+\s*)/i', $this->sql, $matches)) {
                $fromPos = strlen($matches[1]);
                // Insert JOINs right after FROM table
                $this->sql = substr($this->sql, 0, $fromPos) . ' ' . 
                             implode(' ', $this->joinClauses) . 
                             substr($this->sql, $fromPos);
            } else {
                // Fallback: append JOINs at the end
                $this->sql .= ' ' . implode(' ', $this->joinClauses);
            }
        }
        
        // Add WHERE conditions
        if (!empty($this->whereConditions)) {
            $sqlWhereParts = [];
            $firstCondition = true;
            foreach ($this->whereConditions as $group) {
                if (!$firstCondition) {
                    $sqlWhereParts[] = $group['type']; // Add OR or AND between groups
                }
                $sqlWhereParts[] = "({$group['condition']})"; // Group each condition for clarity
                $firstCondition = false;
            }
            $this->sql .= ' WHERE ' . implode(' ', $sqlWhereParts);
            $this->params = array_merge($this->params ?? [], $this->whereParams);
        }
        
        // Add GROUP BY if needed
        if (!empty($this->groupByClause)) {
            $this->sql .= $this->groupByClause;
        }
        
        // Add ORDER BY if needed
        if (!empty($this->orderByClause)) {
            $this->sql .= $this->orderByClause;
        }

        // Add limit and offset if they exist
        if (!empty($this->limitClause)) {
            $this->sql .= $this->limitClause;
        }
        
        if (!empty($this->offsetClause)) {
            $this->sql .= $this->offsetClause;
        }

        // Debug
        error_log("Generated SQL: " . $this->sql);
        error_log("Parameters: " . print_r($this->params, true));
        
        $result = $this->connection->query($this->sql, $this->params);
        $this->reset();
        return $result;
    }

    /**
     * Get the first result
     */
    public function first() {
        // Ensure we're starting with a SELECT
        if (strpos($this->sql, 'SELECT') === false) {
            $this->select();
        }
        
        // Insert JOINs after FROM table
        if (!empty($this->joinClauses)) {
            // Find position after 'FROM table'
            if (preg_match('/^(SELECT\s.+?\sFROM\s+\S+\s*)/i', $this->sql, $matches)) {
                $fromPos = strlen($matches[1]);
                // Insert JOINs right after FROM table
                $this->sql = substr($this->sql, 0, $fromPos) . ' ' . 
                             implode(' ', $this->joinClauses) . 
                             substr($this->sql, $fromPos);
            } else {
                // Fallback: append JOINs at the end
                $this->sql .= ' ' . implode(' ', $this->joinClauses);
            }
        }
        
        // Add WHERE conditions
        if (!empty($this->whereConditions)) {
            $sqlWhereParts = [];
            $firstCondition = true;
            foreach ($this->whereConditions as $group) {
                if (!$firstCondition) {
                    $sqlWhereParts[] = $group['type'];
                }
                $sqlWhereParts[] = "({$group['condition']})";
                $firstCondition = false;
            }
            $this->sql .= ' WHERE ' . implode(' ', $sqlWhereParts);
            $this->params = array_merge($this->params ?? [], $this->whereParams);
        }
        
        // Add LIMIT 1
        $this->sql .= ' LIMIT 1';
        
        // Debug
        error_log("Generated SQL (first): " . $this->sql);
        error_log("Parameters (first): " . print_r($this->params, true));
        
        $result = $this->connection->query($this->sql, $this->params);
        $this->reset();
        return !empty($result) ? $result[0] : null;
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
        $set = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            $set[] = "$column = ?";  // Use ? instead of :$column
            $params[] = $value;      // Add value to simple array, not associative
        }
        
        $setClause = implode(', ', $set);
        
        // Make sure whereConditions is built correctly and parameters are captured
        $whereClause = $this->buildWhereClause();
        
        $this->sql = "UPDATE {$this->table} SET $setClause" . $whereClause;
        
        // Merge the SET parameters with the WHERE parameters
        $this->params = array_merge($params, $this->whereParams);
        
        return $this->execute();
    }

    private function buildWhereClause() {
        if (empty($this->whereConditions)) {
            return '';
        }
        
        $sqlWhereParts = [];
        $firstCondition = true;
        foreach ($this->whereConditions as $group) {
            if (!$firstCondition) {
                $sqlWhereParts[] = $group['type'];
            }
            $sqlWhereParts[] = "({$group['condition']})";
            $firstCondition = false;
        }
        
        return ' WHERE ' . implode(' ', $sqlWhereParts);
    }

    // Delete records
    public function delete() {
        $this->sql = "DELETE FROM {$this->table}";
        if (!empty($this->whereConditions)) {
            $sqlWhereParts = [];
            $firstCondition = true;
            foreach ($this->whereConditions as $group) {
                if (!$firstCondition) {
                    $sqlWhereParts[] = $group['type'];
                }
                $sqlWhereParts[] = "({$group['condition']})";
                $firstCondition = false;
            }
            $this->sql .= ' WHERE ' . implode(' ', $sqlWhereParts);
            $this->params = $this->whereParams;
        }
        return $this->execute();
    }

    /**
     * Set limit for query
     * @param int $limit
     * @return $this
     */
    public function limit($limit) {
        $this->limitClause = " LIMIT $limit";
        return $this;
    }

    /**
     * Set offset for query
     * @param int $offset
     * @return $this
     */
    public function offset($offset) {
        $this->offsetClause = " OFFSET $offset";
        return $this;
    }

    /**
     * Add GROUP BY clause to the query
     * @param string $column Column to group by
     * @return $this
     */
    public function groupBy($column) {
        $this->groupByClause = " GROUP BY $column";
        return $this;
    }

    // Execute the query
    private function execute() {
        $stmt = $this->connection->query($this->sql, $this->params);
        $this->reset(); // Reset SQL and params after execution
        return $stmt;
    }

    /**
     * Reset query state
     */
    private function reset() {
        $this->sql = '';
        $this->params = [];
        $this->whereConditions = [];
        $this->whereParams = [];
        $this->joinClauses = [];
        $this->orderByClause = '';
        $this->groupByClause = ''; // Add this line
        $this->limitClause = '';
        $this->offsetClause = '';
        $this->currentWhereGroupType = 'AND';
    }

    /**
     * Adds a nested group of conditions ANDed with preceding conditions.
     * e.g., ->where('status', '=', 1)->andWhereNested(function($q) {
     *        $q->where('type', '=', 'A')->orWhere('type', '=', 'B');
     *   })
     * results in: WHERE (status = 1) AND ((type = 'A') OR (type = 'B'))
     * Note: This is a simplified version. A full implementation might need more robust state management.
     */
    public function andWhereNested(callable $callback): self {
        $nestedOrm = new self($this->connection); // Create a new ORM instance for the nested query
        // We don't need to set $nestedOrm->table as the callback will primarily use where/orWhere,
        // and field names should be qualified by the caller if necessary.

        call_user_func($callback, $nestedOrm); // Execute the callback to build nested conditions

        if (!empty($nestedOrm->whereConditions)) {
            $nestedSqlParts = [];
            $firstNestedCondition = true;
            foreach ($nestedOrm->whereConditions as $group) {
                if (!$firstNestedCondition) {
                    $nestedSqlParts[] = $group['type']; // This will be 'OR' for the inner group
                }
                // The group['condition'] itself might be an ANDed string if multiple wheres were called in the nested callback
                // but for a simple B OR C OR D, each will be its own group.
                $nestedSqlParts[] = "({$group['condition']})"; 
                $firstNestedCondition = false;
            }
            $nestedConditionString = implode(' ', $nestedSqlParts); // This forms the (B OR C OR D) part

            // Now, add this as an AND condition to the main query's whereConditions array
            $conditionToAdd = "($nestedConditionString)"; // Enclose the whole nested group in parentheses

            if (empty($this->whereConditions) || $this->currentWhereGroupType === 'OR_PENDING') {
                // If it's the first main condition, or follows an OR, start a new AND group for this nested block
                $this->whereConditions[] = ['type' => 'AND', 'condition' => $conditionToAdd];
            } else {
                // Append to the last AND group of the main query
                // This ensures it's ANDed with the previous set of conditions
                $this->whereConditions[count($this->whereConditions)-1]['condition'] .= " AND " . $conditionToAdd;
            }
            
            // Merge parameters from the nested query
            $this->whereParams = array_merge($this->whereParams, $nestedOrm->whereParams);
            // The overall group type remains AND or becomes AND
            $this->currentWhereGroupType = 'AND'; 
        }
        return $this;
    }

    // Count records in the table
    public function count(string $column = '*'): int {
        // Determine the field to count. For safety with joins, COUNT(DISTINCT main_table.id) is often best.
        $countField = $column;
        if ($column === '*' && !empty($this->joinClauses)) {
            // If joins are present and user wants COUNT(*), it's safer to count distinct IDs of the primary table
            $countField = "DISTINCT {$this->table}.id"; // Assuming 'id' is the primary key
        } else if (strpos($column, '.') === false && $column !== '*') {
            // If column is not qualified and not '*', qualify with the current table
            $countField = "{$this->table}.{$column}";
        }
        // If $column is already qualified (e.g., "DISTINCT books.id") or is just "*", use as is.


        $query = "SELECT COUNT({$countField}) as count_result FROM {$this->table}";
        $paramsForCount = []; // Use a local params array for this count query

        // Insert JOINs if any
        if (!empty($this->joinClauses)) {
            $query .= ' ' . implode(' ', $this->joinClauses);
        }
        
        // Add WHERE conditions (using the same logic as get()/first())
        if (!empty($this->whereConditions)) {
            $sqlWhereParts = [];
            $firstCondition = true;
            foreach ($this->whereConditions as $group) {
                if (!$firstCondition) {
                    $sqlWhereParts[] = $group['type']; 
                }
                $sqlWhereParts[] = "({$group['condition']})";
                $firstCondition = false;
            }
            $query .= ' WHERE ' . implode(' ', $sqlWhereParts);
            $paramsForCount = $this->whereParams; // Use the accumulated whereParams
        }
        
        error_log("Generated SQL (count): " . $query);
        error_log("Parameters (count): " . print_r($paramsForCount, true));

        $result = $this->connection->query($query, $paramsForCount);
        
        // The count method is a terminal operation, it executes and returns a value.
        // It should not affect the state of a query being built for a subsequent get() or first().
        // However, the current ORM design calls reset() in get/first/execute.
        // To maintain consistency and allow BookRepository to call ->count() after building conditions:
        // We will NOT reset the main ORM state here. The BookRepository will call ->get() or ->first()
        // on the same ORM instance if it needs the data, or it will build a new query.
        // If count is the *only* thing needed, the calling code should expect the ORM state to persist
        // or manage it. For BookRepository, it builds conditions, then calls count(), then separately calls get().
        // So, the state should persist after count for the get. The reset in get/first is key.

        return isset($result[0]['count_result']) ? (int) $result[0]['count_result'] : 0;
    }

    public function getJoinClauses(): array { // Add this getter
        return $this->joinClauses;
    }
}