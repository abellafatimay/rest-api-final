<?php

use Middleware\Middleware;
use Responses\Response; // Good practice to add this if you are creating Response objects

// These variables ($authController, $controller) are expected to be available
// in the scope where this file is included (i.e., index.php).

return [
    // Public Routes
    [
        'method' => 'POST',
        'path' => '/register',
        'handler' => function () use ($authController) {
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $authController->register($data); // Assuming $authController->register() returns a Response object
            return $response; // Return the response object
        }
    ],
    [
        'method' => 'POST',
        'path' => '/login',
        'handler' => function () use ($authController) {
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $authController->login($data); // Assuming $authController->login() returns a Response object
            return $response; // Return the response object
        }
    ],
    [
        'method' => 'GET',
        'path' => '/',
        'handler' => [new \Controllers\HomeController\HomeController(), 'index']
    ],

    // Protected Routes
    [
        'method' => 'GET',
        'path' => '/users',
        'handler' => Middleware::authenticate($authController, function ($userId) use ($controller) {
            // Assuming $controller->getAllUsers() returns the data (e.g., an array of users)
            // and not a Response object itself. If it returns a Response object, just return that.
            $usersData = $controller->getAllUsers();
            return new Response($usersData, 200); // Create and return the Response object
        })
    ],
    [
        'method' => 'GET',
        'path' => '/users/{id}',
        'handler' => Middleware::authenticate($authController, function ($userId, $id) use ($controller) {
            $userData = $controller->getUserById($id);
            if (!$userData) {
                return new Response(['error' => 'User not found'], 404); // Create and return
            }
            return new Response($userData, 200); // Create and return
        })
    ],
    [
        'method' => 'POST',
        'path' => '/users',
        'handler' => Middleware::authenticate($authController, function ($userId) use ($controller) {
            $data = json_decode(file_get_contents('php://input'), true);
            $createdUserData = $controller->createUser($data); // Assuming this returns the created user data
            return new Response($createdUserData, 201); // Create and return
        })
    ],
    [
        'method' => 'PUT',
        'path' => '/users/{id}',
        'handler' => Middleware::authenticate($authController, function ($userId, $id) use ($controller) {
            $data = json_decode(file_get_contents('php://input'), true);
            $updatedUserData = $controller->updateUser($id, $data);
            if (!$updatedUserData) {
                return new Response(['error' => 'User not found or not updated'], 404); // Create and return
            }
            return new Response($updatedUserData, 200); // Create and return
        })
    ],
    [
        'method' => 'DELETE',
        'path' => '/users/{id}',
        'handler' => Middleware::authenticate($authController, function ($userId, $id) use ($controller) {
            $deleted = $controller->deleteUser($id); // Assuming this returns true on success, false on failure
            if (!$deleted) {
                return new Response(['error' => 'User not found or could not be deleted'], 404); // Create and return
            }
            return new Response(null, 204); // 204 No Content for successful deletion, return
        })
    ],
];

