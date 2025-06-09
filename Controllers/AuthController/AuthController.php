<?php

namespace Controllers\AuthController;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Models\UserRepository\UserRepository;
use Responses\Response;
use Requests\Request\Request;

class AuthController
{
    private UserRepository $userRepository;
    private string $secretKey = 'your_strong_secret_key';
    private Request $request;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->request = new Request();
    }

    public function register($data) {
        
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return new Response(['error' => 'All fields are required'], 400);
        }

        if ($this->userRepository->getByEmail($data['email'])) {
            return new Response(['error' => 'Email already exists'], 409);
        }

        $userData = [
            'name' => $data['username'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Create user and get ID
        $userId = $this->userRepository->create($userData);
        
        if (!$userId) {
            return new Response(['error' => 'Registration failed'], 500);
        }

        //  JWT token
        try {            $user = $this->userRepository->getById($userId);
            $payload = [
                'iss' => 'your-app',
                'iat' => time(),
                'exp' => time() + 3600,
                'user_id' => $userId,
                'role' => $user['role'] ?? 'user'
            ];
            $token = JWT::encode($payload, $this->secretKey, 'HS256');
            
            $updated = $this->userRepository->updateToken($userId, $token);
            
            if (!$updated) {
                error_log("Failed to save token for user $userId");
            } else {
                error_log("Token saved successfully for user $userId");
            } 
            
            return new Response([
                'message' => 'User registered successfully',
                'token' => $token,
                'user_id' => $userId
            ], 201);
        } catch (\Exception $e) {
            error_log("JWT error: " . $e->getMessage());
            return new Response(['error' => 'Token generation failed'], 500);
        }
    }
     public function processRegistration() {
        
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isApiRequest = strpos($contentType, 'application/json') !== false;
        
        if ($isApiRequest) {
            $data = json_decode(file_get_contents('php://input'), true);
        } else {
            $data = [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'confirm_password' => $_POST['confirm_password'] ?? ''
            ];
        }
        
        if ($data['password'] !== ($data['confirm_password'] ?? '')) {
            if ($isApiRequest) {
                return new Response(['error' => 'Passwords do not match'], 400);
            } else {
                return $this->renderRegistrationForm('Passwords do not match', 400);
            }
        }
        
        $response = $this->register($data);
        
        if (!$isApiRequest) {
            if ($response->getStatusCode() !== 201) {
                $responseData = $response->getBody();
                $error = is_array($responseData) ? ($responseData['error'] ?? 'Registration failed') : 'Registration failed';
                return $this->renderRegistrationForm($error);
            }
            
            return new Response(
                ['redirect' => '/login?registered=true'], 
                303, 
                ['Location' => '/login?registered=true']
            );
        }
        
        return $response;
    }
    private function renderRegistrationForm($error = null, $statusCode = 200) {
        $data = [
            'title' => 'Register', 
            'heading' => 'Create an Account'
        ];
        
        if ($error) {
            $data['error'] = $error;
        }
        
        $html = \Views\Core\View::render('Auth/register.php', $data);
        return new Response($html, $statusCode, ['Content-Type' => 'text/html']);
    }

    public function processLogin()
    {
        error_log("Process login called with method: " . $this->request->getMethod());
        
        if (!$this->request->isMethod('POST')) {
            error_log("Invalid method for login: " . $this->request->getMethod());
            return new Response(['error' => 'Method not allowed'], 405);
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isApiRequest = strpos($contentType, 'application/json') !== false;

        if ($isApiRequest) {
            $data = json_decode(file_get_contents('php://input'), true);
        } else {
            $data = [
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? ''
            ];
        }

        error_log("Login data received: " . print_r($data, true));

        if (empty($data['email']) || empty($data['password'])) {
            error_log("Missing email or password");
            if ($isApiRequest) {
                return new Response(['error' => 'Email and password are required'], 400);
            } else {
                return new Response(
                    \Views\Core\View::render('Auth/login.php', [
                        'title' => 'Login', 
                        'heading' => 'User Login',
                        'error' => 'Email and password are required'
                    ]), 
                    200, 
                    ['Content-Type' => 'text/html']
                );
            }
        }

        $user = $this->userRepository->getByEmail($data['email']);
        error_log("User lookup result: " . ($user ? "Found with ID: {$user['id']}" : "User not found"));

        if (!$user || !password_verify($data['password'], $user['password'])) {
            error_log("Invalid credentials for email: " . $data['email']);
            if ($isApiRequest) {
                return new Response(['error' => 'Invalid credentials'], 401);
            } else {
                return new Response(
                    \Views\Core\View::render('Auth/login.php', [
                        'title' => 'Login', 
                        'heading' => 'User Login',
                        'error' => 'Invalid credentials'
                    ]), 
                    200, 
                    ['Content-Type' => 'text/html']
                );
            }
        }

        $payload = [
            'iss' => 'your-app',
            'iat' => time(),
            'exp' => time() + (60 * 60), // 1 hour
            'user_id' => $user['id'],
            'role' => $user['role']
        ];

        $jwt = JWT::encode($payload, $this->secretKey, 'HS256');

        if ($isApiRequest) {
            return new Response([
                'message' => 'Login successful',
                'token' => $jwt,
                'user_id' => $user['id'],
                'role' => $user['role']
            ], 200);
        } else {
            
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['user_token'] = $jwt;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];

            error_log("Login successful. User ID: {$user['id']}, Role: {$user['role']}");
            error_log("Token stored in session: " . $jwt);
            error_log("Session data: " . print_r($_SESSION, true));

            return new Response(
                ['message' => 'Login successful'],
                303,
                ['Location' => '/dashboard']
            );
        }
    }

    public function authenticate($providedToken = null)
{
    error_log("Authentication requested. Provided token: " . ($providedToken ? "yes" : "no"));
    
    $token = $providedToken;
    
    if ($token === null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = $_SESSION['user_token'] ?? null;
        error_log("Token from session: " . ($token ? "found" : "not found"));
    }
    
    if ($token === null) {
        error_log("No token found for authentication");
        return ['status' => 'error', 'message' => 'Authentication required', 'user_id' => null];
    }
    
    try {
        $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
        error_log("Token decoded successfully: " . print_r($decoded, true));
        
        $userId = $decoded->user_id;
        
        return [
            'status' => 'success',
            'message' => 'Authenticated',
            'user_id' => $userId,
            'user_data' => [
                'id' => $userId,
                'role' => $decoded->role
            ]
        ];
    } catch (\Exception $e) {
        error_log("Token validation error: " . $e->getMessage());
        return ['status' => 'error', 'message' => 'Invalid token', 'user_id' => null];
    }
}

    public function getUserById($userId) {
        return $this->userRepository->getById($userId);}
    
    public function changePassword($userId, $currentPassword, $newPassword) {
        
        $user = $this->userRepository->getById($userId);
        
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
    
        if (!password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
        
        
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        
        
        $updated = $this->userRepository->update($userId, ['password' => $hashedPassword]);
        
        if (!$updated) {
            return ['success' => false, 'message' => 'Failed to update password'];
        }
        
        return ['success' => true];
    }
    /**
     * Process user logout and destroy session
     * 
     * @return Response 
     */
    public function logout()
    {
        error_log("Logout requested");
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        
        error_log("Logout completed, redirecting to login");
        return new Response(
            ['message' => 'Logged out successfully'],
            303,
            ['Location' => '/login']
        );
    }
    public function login() {
        $email = $this->request->post('email');
        $password = $this->request->post('password');

        if (empty($email) || empty($password)) {
            return Response::view('Auth/Login', [
                'error' => 'Email and password are required'
            ], 422);
        }

        $user = $this->userRepository->getByEmail($email);
        error_log("Login attempt for " . $email . ": " . ($user ? "User found" : "User not found"));
        
        if ($user && password_verify($password, $user['password'])) {
            $payload = [
                'iss' => 'your-domain.com',
                'user_id' => $user['id'],
                'role' => $user['role'],
                'iat' => time(),
                'exp' => time() + 3600
            ];
            $token = JWT::encode($payload, $this->secretKey, 'HS256');

            $this->userRepository->updateToken($user['id'], $token);

            return new Response(['token' => $token, 'user_id' => $user['id']], 200);
        } else {
            error_log("Password verification failed for " . $email);
        }

        return new Response(['error' => 'Invalid credentials'], 401);
    }
}
