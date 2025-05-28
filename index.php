<?php

require_once 'init.php';

use Models\Database\Database;
use Models\ORM\ORM;
use Models\UserRepository\UserRepository;
use Controllers\AuthController\AuthController;
use Requests\Request\Request;
use Controllers\UserController\UserController;
use Router\Router;
use classes\RouteMatcher;
use Responses\Response;

// Initialize the database connection
$db = new Database('localhost', 'root', 'root', 'rest_api');

// Initialize the ORM
$orm = new ORM($db);

// Initialize the user repository
$userRepository = new UserRepository($orm);

// Initialize the auth controller
$authController = new AuthController($userRepository);

// Initialize the request object
$request = new Request();

// Initialize the user controller with dependencies
$controller = new UserController($userRepository, $request);

// Load routes and pass $authController and $controller
$routes = include __DIR__ . '/routes.php';

// Initialize the router
$router = new Router($request, new RouteMatcher());

// Register routes
foreach ($routes as $route) {
    $router->addRoute($route['method'], $route['path'], $route['handler']);
}

$response = $router->dispatch();
error_log('Response type: ' . get_class($response));
if ($response instanceof Response) {
    $response->send();
} else {
    // Log what we got instead
    error_log('Unexpected response type: ' . print_r($response, true));
    http_response_code(200);    
    echo json_encode($response);
}
