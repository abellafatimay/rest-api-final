<?php
// At the very top of the file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('memory_limit', '256M');

// Enable error display
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'init.php';

use Models\Database\Database;
use Models\ORM\ORM;
use Models\UserRepository\UserRepository;
use Models\BookRepository\BookRepository;
use Models\CategoryRepository\CategoryRepository;
use Models\BookCategoryRepository\BookCategoryRepository;
use Controllers\AuthController\AuthController;
use Controllers\UserController\UserController;
use Controllers\ProfileController\ProfileController;
use Controllers\AdminController\AdminController;
use Controllers\UserAdminController\UserAdminController;
use Controllers\BookController\BookController;
use Controllers\CategoryController\CategoryController;
use Requests\Request\Request;
use Router\Router;
use classes\RouteMatcher;
use Responses\Response;
use Controllers\CatalogController\CatalogController;

// Initialize the database connection
$db = new Database('localhost', 'root', 'root', 'rest_api');

// Initialize the ORM
$orm = new ORM($db);

// Initialize the request object
$request = new Request();

// Initialize repositories
$userRepository = new UserRepository($orm);
$bookRepository = new BookRepository($orm);
$categoryRepository = new CategoryRepository($orm);
$bookCategoryRepository = new BookCategoryRepository($orm);

// Initialize controllers
$authController = new AuthController($userRepository);
$controller = new UserController($userRepository, $request);
$profileController = new ProfileController($userRepository, $request, $authController);
$adminController = new AdminController($userRepository, $bookRepository, $categoryRepository);
$userAdminController = new UserAdminController($userRepository);
$bookController = new BookController($bookRepository, $bookCategoryRepository);
$categoryController = new CategoryController($categoryRepository, $bookRepository);
$catalogController = new CatalogController($bookRepository, $categoryRepository);

// Load routes and pass $authController and $controller
$routes = include __DIR__ . '/routes.php';

// Initialize the router
$router = new Router($request, new RouteMatcher());

// Register routes
foreach ($routes as $route) {
    $router->addRoute($route['method'], $route['path'], $route['handler']);
}

$response = $router->dispatch();
error_log('Router dispatch complete');
error_log('Response type: ' . (is_object($response) ? get_class($response) : gettype($response)));

if ($response instanceof Response) {
    $response->send();
} else {
    // Log what we got instead
    error_log('Unexpected response type: ' . print_r($response, true));
    http_response_code(200);    
    echo json_encode($response);
}
