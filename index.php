<?php
header('Content-Type: application/json');

require_once 'init.php';

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
if ($response instanceof Response) {
    $response->send();
} else {
    // fallback in case something else is returned (not required if all handlers return Response)
    http_response_code(200);    
    echo json_encode($response);
}
