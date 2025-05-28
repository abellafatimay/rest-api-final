<?php

namespace Router;

use Requests\RequestInterface\RequestInterface;
use Responses\Response;
use classes\RouteMatcher;
use Views\Core\View;

class Router {
    private $request;
    private $routeMatcher;
    private $routes = [];

    public function __construct(RequestInterface $request, RouteMatcher $routeMatcher) {
        $this->request = $request;
        $this->routeMatcher = $routeMatcher;
    }

    public function addRoute($method, $path, $handler) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function dispatch() {
        $match = $this->routeMatcher->match(
            $this->routes,
            $this->request->getMethod(),
            $this->request->getPath()
        );

        if ($match) {
            // $match['handler'] would be for example: [new App\Controllers\HomeController(), 'index']
            // $match['params'] would be for example: ['id' => '123', 'name' => 'John']
            // call_user_func_array will call HomeController->index() or HomeController->showUser('123', 'John')
            // The controller action now returns a Response object containing HTML.
            return call_user_func_array($match['handler'], array_values($match['params']));
        }

        // If no route matched
        try {
            $htmlError = View::render('Errors/404.php'); // Assuming you create Views/Errors/404.php
            return new Response($htmlError, 404, ['Content-Type' => 'text/html; charset=UTF-8']);
        } catch (\Exception $e) {
            // Fallback if 404 view itself is missing or causes error
            return new Response('<h1>404 - Page Not Found</h1><p>The requested page could not be located.</p>', 404, ['Content-Type' => 'text/html; charset=UTF-8']);
        }
    }
}