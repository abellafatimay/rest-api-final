<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('memory_limit', '256M');

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
use Router\Router;
use classes\RouteMatcher;
use Responses\Response;
use Controllers\CatalogController\CatalogController;
use Requests\Request\Request;

$db = new Database('localhost', 'root', 'root', 'rest_api');
$orm = new ORM($db);
$request = new Request();

//repositories
$userRepository = new UserRepository($orm);
$bookRepository = new BookRepository($orm);
$categoryRepository = new CategoryRepository($orm);
$bookCategoryRepository = new BookCategoryRepository($orm);

//controllers
$authController = new AuthController($userRepository);
$controller = new UserController($userRepository, $request);
$profileController = new ProfileController($userRepository, $request, $authController);
$adminController = new AdminController($userRepository, $bookRepository, $categoryRepository);
$userAdminController = new UserAdminController($userRepository);
$bookController = new BookController($bookRepository,$bookCategoryRepository,$request,$categoryRepository);
$categoryController = new CategoryController($categoryRepository, $bookRepository, $request);
$catalogController = new CatalogController($bookRepository, $categoryRepository);

$routes = include __DIR__ . '/routes.php';

$router = new Router($request, new RouteMatcher());

foreach ($routes as $route) {
    $router->addRoute($route['method'], $route['path'], $route['handler']);
}

$response = $router->dispatch();
error_log('Router dispatch complete');
error_log('Response type: ' . (is_object($response) ? get_class($response) : gettype($response)));

if ($response instanceof Response) {
    $response->send();
} else {
    
    http_response_code(200);    
    echo json_encode($response);
}
