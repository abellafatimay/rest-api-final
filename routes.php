<?php

use Middleware\Middleware;
use Responses\Response;
use Views\Core\View;
use Controllers\HomeController\HomeController;

return [
    
    [
        'method' => 'POST',
        'path' => '/register',
        'handler' => function () use ($authController) {
            $response = $authController->processRegistration();
            
            return $response;
        }
    ],
    [
        'method' => 'POST',
        'path' => '/login',
        'handler' => function () use ($authController) {
            $response = $authController->processLogin();
            return $response; 
        }
    ],
    [
        'method' => 'GET',
        'path' => '/',
        'handler' => function() use ($catalogController) {
            return $catalogController->index();
        }
    ],
    [
        'method' => 'GET',
        'path' => '/login',
        'handler' => function () {
            $data = ['title' => 'Login', 'heading' => 'User Login'];
            $html = View::render('Auth/login.php', $data);
            return new Response($html, 200, ['Content-Type' => 'text/html']);
        }
    ],
    [
        'method' => 'GET',
        'path' => '/register',
        'handler' => function () {
            $data = ['title' => 'Register', 'heading' => 'Create an Account'];
            $html = View::render('Auth/register.php', $data);
            return new Response($html, 200, ['Content-Type' => 'text/html']);
        }
    ],

    // Protected Routes (any logged-in user)
    [
        'method' => 'GET',
        'path' => '/users',
        'handler' => Middleware::authenticate($authController, function ($userId) use ($controller) {
            return $controller->getAllUsers();
        })
    ],
    [
        'method' => 'GET',
        'path' => '/users/{id}',
        'handler' => Middleware::authenticate($authController, function ($userId, $id) use ($controller) {
            return $controller->getUserById($id);
        })
    ],
    [
        'method' => 'POST',
        'path' => '/users',
        'handler' => Middleware::authenticate($authController, function ($userId) use ($controller) {
            return $controller->createUser();
        })
    ],
    [
        'method' => 'PUT',
        'path' => '/users/{id}',
        'handler' => Middleware::authenticate($authController, function ($userId, $id) use ($controller) {
            return $controller->updateUser($id);
        })
    ],
    [
        'method' => 'DELETE',
        'path' => '/users/{id}',
        'handler' => Middleware::authenticate($authController, function ($userId, $id) use ($controller) {
            return $controller->deleteUser($id);
        })
    ],
    [
        'method' => 'GET',
        'path' => '/dashboard',
        'handler' => Middleware::authenticate($authController, function ($userId) use ($controller) {
            $userData = $controller->getUserById($userId)->getBody();
            $data = [
                'title' => 'Dashboard',
                'user_id' => $userId,
                'user_data' => $userData,
                'is_admin' => isset($userData['role']) && $userData['role'] === 'admin'
            ];
            $html = View::render('Dashboard/dashboard.php', $data);
            return new Response($html, 200, ['Content-Type' => 'text/html']);
        })
    ],
    [
        'method' => 'GET',
        'path' => '/profile',
        'handler' => Middleware::authenticate($authController, function ($userId) use ($profileController) {
            return $profileController->showProfile($userId);
        })
    ],
    [
        'method' => 'POST',
        'path' => '/profile/update',
        'handler' => Middleware::authenticate($authController, function ($userId) use ($profileController) {
            return $profileController->updateProfile($userId);
        })
    ],
    [
        'method' => 'POST',
        'path' => '/profile/change-password',
        'handler' => Middleware::authenticate($authController, function ($userId) use ($profileController) {
            return $profileController->changePassword($userId);
        })
    ],
    // Admin-only view example:
    [
        'method' => 'GET',
        'path' => '/admin',
        'handler' => Middleware::authorize($authController, 'admin', function ($userId) use ($adminController) {
            return $adminController->index();
        })
    ],
    [
        'method' => 'GET',
        'path' => '/admin/users',
        'handler' => Middleware::authorize($authController, 'admin', function ($userId) use ($userAdminController) {
            return $userAdminController->index();
        })
    ],
    [
        'method' => 'GET',
        'path' => '/admin/users/create',
        'handler' => Middleware::authorize($authController, 'admin', function ($userId) use ($userAdminController) {
            return $userAdminController->create();
        })
    ],
    [
        'method' => 'POST',
        'path' => '/admin/users',
        'handler' => Middleware::authorize($authController, 'admin', function ($userId) use ($userAdminController) {
            return $userAdminController->store();
        })
    ],
    [
        'method' => 'GET',
        'path' => '/admin/users/{id}',
        'handler' => Middleware::authorize($authController, 'admin', function ($userId, $id) use ($userAdminController) {
            return $userAdminController->show($id);
        })
    ],
    [
        'method' => 'GET',
        'path' => '/admin/users/{id}/edit',
        'handler' => Middleware::authorize($authController, 'admin', function ($userId, $id) use ($userAdminController) {
            return $userAdminController->edit($id);
        })
    ],
    [
        'method' => 'POST',
        'path' => '/admin/users/{id}',
        'handler' => Middleware::authorize($authController, 'admin', function ($userId, $id) use ($userAdminController) {
            return $userAdminController->update($id);
        })
    ],
    [
        'method' => 'POST',
        'path' => '/admin/users/{id}/delete',
        'handler' => Middleware::authorize($authController, 'admin', function ($userId, $id) use ($userAdminController) {
            return $userAdminController->delete($id);
        })
    ],
    // Logout Route
    [
        'method' => 'GET',
        'path' => '/logout',
        'handler' => function () use ($authController) {
            $response = $authController->logout();
            // Let index.php handle the response, including redirects
            return $response;
        }
    ],
    // Book routes
    [
        'method' => 'GET',
        'path' => '/admin/books',
        'handler' => Middleware::authorize($authController, 'admin', function($userId) use ($bookController) {
            return $bookController->index();
        })
    ],
    [
        'method' => 'GET',
        'path' => '/admin/books/create',
        'handler' => Middleware::authorize($authController, 'admin', function($userId) use ($bookController) {
            return $bookController->create();
        })
    ],
    [
        'method' => 'POST',
        'path' => '/admin/books/store',
        'handler' => Middleware::authorize($authController, 'admin', function($userId) use ($bookController) {
            return $bookController->store();
        })
    ],
    [
        'method' => 'GET',
        'path' => '/admin/books/{id}',
        'handler' => Middleware::authorize($authController, 'admin', function($userId, $id) use ($bookController) {
            return $bookController->show($id);
        })
    ],
    [
        'method' => 'GET',
        'path' => '/admin/books/{id}/edit',
        'handler' => Middleware::authorize($authController, 'admin', function($userId, $id) use ($bookController) {
            return $bookController->edit($id);
        })
    ],
    [
        'method' => 'POST',
        'path' => '/admin/books/{id}',
        'handler' => Middleware::authorize($authController, 'admin', function($userId, $id) use ($bookController) {
            return $bookController->update($id);
        })
    ],
    [
        'method' => 'POST',
        'path' => '/admin/books/{id}/delete',
        'handler' => Middleware::authorize($authController, 'admin', function($userId, $id) use ($bookController) {
            return $bookController->delete($id);
        })
    ],
    // Category routes (now $categoryController will be defined)
    [
        'method' => 'GET',
        'path' => '/admin/categories',
        'handler' => Middleware::authorize($authController, 'admin', function($userId) use ($categoryController) {
            return $categoryController->index();
        })
    ],
    [
        'method' => 'GET',
        'path' => '/admin/categories/create',
        'handler' => Middleware::authorize($authController, 'admin', function($userId) use ($categoryController) {
            return $categoryController->create();
        })
    ],
    [
        'method' => 'POST',
        'path' => '/admin/categories',
        'handler' => Middleware::authorize($authController, 'admin', function($userId) use ($categoryController) {
            return $categoryController->store();
        })
    ],
    [
        'method' => 'GET',
        'path' => '/admin/categories/{id}/edit',
        'handler' => Middleware::authorize($authController, 'admin', function($userId, $id) use ($categoryController) {
            return $categoryController->edit($id);
        })
    ],
    [
        'method' => 'POST',
        'path' => '/admin/categories/{id}',
        'handler' => Middleware::authorize($authController, 'admin', function($userId, $id) use ($categoryController) {
            return $categoryController->update($id);
        })
    ],
    [
        'method' => 'POST',
        'path' => '/admin/categories/{id}/delete',
        'handler' => Middleware::authorize($authController, 'admin', function($userId, $id) use ($categoryController) {
            return $categoryController->delete($id);
        })
    ],
    // Public category routes
    [
        'method' => 'GET',
        'path' => '/categories',
        'handler' => function() use ($categoryController) {
            return $categoryController->list();
        }
    ],
    [
        'method' => 'GET',
        'path' => '/categories/{id}',
        'handler' => function($id) use ($categoryController) {
            return $categoryController->view($id);
        }
    ],
    // Book-Category Management Routes
    [
        'method' => 'GET',
        'path' => '/admin/book-categories',
        'handler' => Middleware::authorize($authController, 'admin', function($userId) use ($categoryController) {
            return $categoryController->index();
        })
    ],
    [
        'method' => 'POST',
        'path' => '/admin/books/{bookId}/categories',
        'handler' => Middleware::authorize($authController, 'admin', function($userId, $bookId) use ($bookController) {
            return $bookController->updateCategories($bookId);
        })
    ],
    [
        'method' => 'GET',
        'path' => '/admin/books/{bookId}/categories',
        'handler' => Middleware::authorize($authController, 'admin', function($userId, $bookId) use ($bookController) {
            return $bookController->getCategories($bookId);
        })
    ],
    // Default homepage - show the catalog
    [
        'method' => 'GET',
        'path' => '/catalog',
        'handler' => function() use ($catalogController) {
            return $catalogController->index();
        }
    ],
    [
        'method' => 'GET',
        'path' => '/catalog/{id}',
        'handler' => function($id) use ($catalogController) {
            return $catalogController->show($id);
        }
    ],
    [
        'method' => 'GET',
        'path' => '/catalog/category/{id}',
        'handler' => function($id) use ($catalogController) {
            return $catalogController->category($id);
        }
    ],
    // Example for API route (JWT-based)
    [
        'method' => 'GET',
        'path' => '/api/books',
        'handler' => function () use ($bookController) {
            return $bookController->index();
        }
    ],
    [
        'method' => 'POST',
        'path' => '/api/books/create',
        'handler' => Middleware::authorize($authController, 'admin', function () use ($bookController) {
            return $bookController->create();
        })
    ],
    [
        'method' => 'GET',
        'path' => '/api/catalog',
        'handler' => function () use ($catalogController) {
            return $catalogController->index();
        }
    ],
    [
        'method' => 'GET',
        'path' => '/api/categories',
        'handler' => function () use ($categoryController) {
            return $categoryController->index();
        }
    ],
    [
        'method' => 'GET',
        'path' => '/api/profile',
        'handler' => Middleware::authenticate($authController, function ($userId) use ($profileController) {
            return $profileController->showProfile($userId);
        })
    ],
    [
        'method' => 'GET',
        'path' => '/api/users',
        'handler' => Middleware::authorize($authController, 'admin', function () use ($controller) {
            return $controller->getAllUsers();
        })
    ],
    [
        'method' => 'GET',
        'path' => '/api/user/{id}',
        'handler' => function ($id) use ($controller) {
            return $controller->apiGetUserById($id);
        }
    ],
    [
        'method' => 'POST',
        'path' => '/api/user/create',
        'handler' => Middleware::authorize($authController, 'admin', function () use ($controller) {
            return $controller->createUser();
        })
    ],
    [
        'method' => 'PUT',
        'path' => '/api/user/update/{id}',
        'handler' => Middleware::authorize($authController, 'admin', function ($id) use ($controller) {
            return $controller->updateUser($id);
        })
    ],
    [
        'method' => 'GET',
        'path' => '/api/admin/dashboard',
        'handler' => Middleware::authorize($authController, 'admin', function ($userId) use ($adminController) {
            return $adminController->dashboard();
        })
    ],
    [
        'method' => 'GET',
        'path' => '/api/books',
        'handler' => function () use ($bookController) {
            return $bookController->index();
        }
    ],
    [
        'method' => 'POST',
        'path' => '/api/books/create',
        'handler' => Middleware::authorize($authController, 'admin', function () use ($bookController) {
            return $bookController->create();
        })
    ],
    [
        'method' => 'GET',
        'path' => '/api/catalog',
        'handler' => function () use ($catalogController) {
            return $catalogController->index();
        }
    ],
    [
        'method' => 'GET',
        'path' => '/api/categories',
        'handler' => function () use ($categoryController) {
            return $categoryController->index();
        }
    ],
    [
        'method' => 'GET',
        'path' => '/api/profile',
        'handler' => Middleware::authenticate($authController, function ($userId) use ($profileController) {
            return $profileController->showProfile($userId);
        })
    ],
    [
        'method' => 'GET',
        'path' => '/api/users',
        'handler' => Middleware::authorize($authController, 'admin', function () use ($controller) {
            return $controller->getAllUsers();
        })
    ],
    [
        'method' => 'GET',
        'path' => '/api/user/{id}',
        'handler' => function ($id) use ($controller) {
            return $controller->apiGetUserById($id);
        }
    ],
    [
        'method' => 'POST',
        'path' => '/api/user/create',
        'handler' => Middleware::authorize($authController, 'admin', function () use ($controller) {
            return $controller->createUser();
        })
    ],
    [
        'method' => 'PUT',
        'path' => '/api/user/update/{id}',
        'handler' => Middleware::authorize($authController, 'admin', function ($id) use ($controller) {
            return $controller->updateUser($id);
        })
    ],
];