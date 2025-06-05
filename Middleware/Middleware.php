<?php

namespace Middleware;

use Controllers\AuthController\AuthController;
use Responses\Response;

class Middleware {
    // JWT Authentication Middleware with session support
    public static function authenticate(AuthController $authController, callable $next)
    {
        return function (...$params) use ($authController, $next) {
            error_log("Authenticate middleware executing");
            
            $authResult = $authController->authenticate();
            error_log("Auth result: " . print_r($authResult, true));

            if ($authResult['status'] !== 'success') {
                error_log("Authentication failed, redirecting to login");
                header('Location: /login');
                exit;
            }
            
            error_log("Authentication successful for user: " . $authResult['user_id']);
            array_unshift($params, $authResult['user_id']);
            return $next(...$params);
        };
    }

    // Role-based access control Middleware - Updated to support sessions
    public static function authorize(AuthController $authController, string $requiredRole, callable $next)
    {
        return function (...$params) use ($authController, $requiredRole, $next) {
            $tokenFromHeader = null;
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
            if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $tokenFromHeader = $matches[1];
            }

            $authResult = $authController->authenticate($tokenFromHeader);

            if ($authResult['status'] !== 'success' || empty($authResult['user_id'])) {
                $isWebRequest = (!isset($_SERVER['HTTP_ACCEPT']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') === false);
                if ($isWebRequest) {
                    error_log("Middleware: Auth failed for authorization. Redirecting to login. Message: " . ($authResult['message'] ?? 'Unknown auth error'));
                    header('Location: /login');
                    exit;
                } else {
                    (new Response(['error' => $authResult['message'] ?? 'Unauthorized'], 401))->send();
                    exit;
                }
            }

            // User is authenticated, now check role
            $userId = $authResult['user_id'];
            $userData = $authController->getUserById($userId); // Assuming AuthController has this or get it from $authResult['user_data']

            // Ensure $userData is an array and 'role' key exists
            if (!$userData || !isset($userData['role']) || $userData['role'] !== $requiredRole) {
                $isWebRequest = (!isset($_SERVER['HTTP_ACCEPT']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') === false);
                if ($isWebRequest) {
                    error_log("Middleware: Authorization failed. User role: " . ($userData['role'] ?? 'Not set') . ". Required: " . $requiredRole);
                    // Redirect to an access denied page or home
                    // For simplicity, redirecting to login, but an "access-denied" page is better.
                    (new Response('Access Denied. You do not have the required permissions.', 403, ['Content-Type' => 'text/html']))->send(); // Or redirect
                    exit;
                } else {
                    (new Response(['error' => 'Forbidden. Insufficient permissions.'], 403))->send();
                    exit;
                }
            }

            // If authorized, add user_id to params for the next handler
            array_unshift($params, $userId);
            return $next(...$params);
        };
    }

    // Input validation Middleware (unchanged)
    public static function validateInput($rules, $handler) {
        // Your existing code
        return function (...$args) use ($rules, $handler) {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!is_array($data)) {
                return new Response(['error' => 'Invalid JSON input'], 400);
            }

            foreach ($rules as $field => $rule) {
                if (!isset($data[$field])) {
                    return new Response(['error' => "Missing required field: $field"], 400);
                }
                if (!preg_match($rule, $data[$field])) {
                    return new Response(['error' => "Invalid input for field: $field"], 400);
                }
            }

            // Pass the validated data and other arguments to the handler
            return $handler($data, ...$args);
        };
    }
}
