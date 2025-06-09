<?php

namespace Middleware;

use Controllers\AuthController\AuthController;
use Responses\Response;

class Middleware {
    public static function authenticate(AuthController $authController, callable $next): callable
    {
        return function (...$params) use ($authController, $next) {
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';

            $tokenFromHeader = null;
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
            if ($authHeader && preg_match('/Bearer\\s(\\S+)/', $authHeader, $matches)) {
                $tokenFromHeader = $matches[1];
            }

            $authResult = $authController->authenticate($tokenFromHeader);

            $isApiRequest = strpos($requestUri, '/api/') !== false;

            if (!isset($authResult['status']) || $authResult['status'] !== 'success') {
                $errorMessage = $authResult['message'] ?? 'Authentication failed';
                if ($isApiRequest) {
                    Response::json(['error' => $errorMessage], 401)->send();
                    exit;
                } else {
                    Response::redirect('/login')->withFlash('error', $errorMessage)->send();
                    exit;
                }
            }

            $userId = $authResult['user_id'] ?? null;
            if ($userId) {
                array_unshift($params, $userId);
            } else {
                if ($isApiRequest) {
                    Response::json(['error' => 'Authentication succeeded but user identification failed.'], 500)->send();
                    exit;
                }
            }
            
            return $next(...$params);
        };
    }

    // Role-based access control Middleware
    public static function authorize(AuthController $authController, string $requiredRole, callable $next): callable
    {
        return function (...$params) use ($authController, $requiredRole, $next) {
            $tokenFromHeader = null;
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
            if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $tokenFromHeader = $matches[1];
            }

            // Determine if the original request is likely an API request based on the path
            $isLikelyApiRequest = isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false;

            $authResult = $authController->authenticate($tokenFromHeader);

            if ($authResult['status'] !== 'success' || empty($authResult['user_id'])) {
                if ($isLikelyApiRequest) {
                    Response::json(['error' => $authResult['message'] ?? 'Unauthorized'], 401)->send();
                    exit;
                }
                $isWebRequest = (!isset($_SERVER['HTTP_ACCEPT']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') === false);
                if ($isWebRequest) {
                    error_log("Middleware: Auth failed for authorization. Redirecting to login. Message: " . ($authResult['message'] ?? 'Unknown auth error'));
                    Response::redirect('/login')->withFlash('error', 'Please log in')->send();
                    exit;
                } else {

                    Response::json(['error' => $authResult['message'] ?? 'Unauthorized'], 401)->send();
                    exit;
                }
            }

            $userId = $authResult['user_id'];
            $userData = $authController->getUserById($userId);

            if (!$userData || !isset($userData['role']) || $userData['role'] !== $requiredRole) {
                if ($isLikelyApiRequest) {
                    Response::json(['error' => 'Forbidden. Insufficient permissions.'], 403)->send();
                    exit;
                }
                $isWebRequest = (!isset($_SERVER['HTTP_ACCEPT']) || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') === false);
                if ($isWebRequest) {
                    error_log("Middleware: Authorization failed. User role: " . ($userData['role'] ?? 'Not set') . ". Required: " . $requiredRole);
                
                    Response::error('Access Denied. You do not have the required permissions.', 403)->send();
                    exit;
                } else {
                    Response::json(['error' => 'Forbidden. Insufficient permissions.'], 403)->send();
                    exit;
                }
            }

            array_unshift($params, $userId);
            return $next(...$params);
        };
    }

    public static function validateInput($rules, $handler): callable {
        return function (...$args) use ($rules, $handler) {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!is_array($data)) {
                Response::error('Invalid JSON input', 400, true)->send();
                exit;
            }

            foreach ($rules as $field => $rule) {
                if (!isset($data[$field])) {
                    Response::error("Missing required field: $field", 400, true)->send();
                    exit;
                }
                if (!preg_match($rule, $data[$field])) {
                    Response::error("Invalid input for field: $field", 400, true)->send();
                    exit;
                }
            }

            return $handler($data, ...$args);
        };
    }
}
