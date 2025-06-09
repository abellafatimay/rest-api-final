<?php
namespace Services;

class Validation {
    /**
     * Validate required fields
     * @param array $data Input data (e.g., $_POST)
     * @param array $required List of required field names
     * @return array [isValid, errors]
     */
    public static function requireFields(array $data, array $required): array {
        $errors = [];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst($field) . ' is required.';
            }
        }
        return [empty($errors), $errors];
    }

    /**
     * Validate email format
     */
    public static function email($email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate string length
     */
    public static function length($value, $min = 0, $max = null): bool {
        $len = mb_strlen($value);
        if ($len < $min) return false;
        if ($max !== null && $len > $max) return false;
        return true;
    }

    /**
     * Validate integer
     */
    public static function integer($value): bool {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    // Add more reusable validation methods as needed
}
