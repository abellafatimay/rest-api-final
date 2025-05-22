<?php

class Middleware {
    // JWT Authentication Middleware
    public static function authenticate($authController, $handler) {
        return function (...$args) use ($authController, $handler) {
            $headers = getallheaders();
            $token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');

            $authResult = $authController->authenticate($token);

            if (!isset($authResult['success']) || !$authResult['success']) {
                (new Response(401, ['error' => $authResult['error'] ?? 'Authentication failed']))->send();
            }

            $userId = $authResult['userId'];

            // Pass the authenticated user ID and other arguments to the handler
            return $handler($userId, ...$args);
        };
    }

    // Role-based access control Middleware
    public static function authorize($authController, $requiredRole, $handler) {
        return function (...$args) use ($authController, $requiredRole, $handler) {
            $headers = getallheaders();
            $token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');

            $authResult = $authController->authenticate($token);

            if (!isset($authResult['success']) || !$authResult['success']) {
                (new Response(401, ['error' => $authResult['error'] ?? 'Authentication failed']))->send();
            }

            $userId = $authResult['userId'];
            $user = $authController->getUserById($userId);

            if (!isset($user['role']) || $user['role'] !== $requiredRole) {
                (new Response(403, ['error' => 'Unauthorized access']))->send();
            }

            // Pass the authenticated user ID and other arguments to the handler
            return $handler($userId, ...$args);
        };
    }

    // Input validation Middleware
    public static function validateInput($rules, $handler) {
        return function (...$args) use ($rules, $handler) {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!is_array($data)) {
                (new Response(400, ['error' => 'Invalid JSON input']))->send();
            }

            foreach ($rules as $field => $rule) {
                if (!isset($data[$field])) {
                    (new Response(400, ['error' => "Missing required field: $field"]))->send();
                }
                if (!preg_match($rule, $data[$field])) {
                    (new Response(400, ['error' => "Invalid input for field: $field"]))->send();
                }
            }

            // Pass the validated data and other arguments to the handler
            return $handler($data, ...$args);
        };
    }
}
