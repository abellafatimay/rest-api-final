<?php

return [
    // Public Routes
    [
        'method' => 'POST',
        'path' => '/register',
        'handler' => function () use ($authController) {
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $authController->register($data);
            $response->send();
        }
    ],
    [
        'method' => 'POST',
        'path' => '/login',
        'handler' => function () use ($authController) {
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $authController->login($data);
            $response->send();
        }
    ],

    // Protected Routes
    [
        'method' => 'GET',
        'path' => '/users',
        'handler' => Middleware::authenticate($authController, function ($userId) use ($controller) {
            $users = $controller->getAllUsers();
            (new Response(200, $users))->send();
        })
    ],
    [
        'method' => 'GET',
        'path' => '/users/{id}',
        'handler' => Middleware::authenticate($authController, function ($userId, $id) use ($controller) {
            $user = $controller->getUserById($id);
            if (!$user) {
                (new Response(404, ['error' => 'User not found']))->send();
            }
            (new Response(200, $user))->send();
        })
    ],
    [
        'method' => 'POST',
        'path' => '/users',
        'handler' => Middleware::authenticate($authController, function ($userId) use ($controller) {
            $data = json_decode(file_get_contents('php://input'), true);
            $createdUser = $controller->createUser($data);
            (new Response(201, $createdUser))->send();
        })
    ],
    [
        'method' => 'PUT',
        'path' => '/users/{id}',
        'handler' => Middleware::authenticate($authController, function ($userId, $id) use ($controller) {
            $data = json_decode(file_get_contents('php://input'), true);
            $updatedUser = $controller->updateUser($id, $data);
            if (!$updatedUser) {
                (new Response(404, ['error' => 'User not found']))->send();
            }
            (new Response(200, $updatedUser))->send();
        })
    ],
    [
        'method' => 'DELETE',
        'path' => '/users/{id}',
        'handler' => Middleware::authenticate($authController, function ($userId, $id) use ($controller) {
            $deleted = $controller->deleteUser($id);
            if (!$deleted) {
                (new Response(404, ['error' => 'User not found']))->send();
            }
            (new Response(204, null))->send(); // 204 No Content for successful deletion
        })
    ],
];

