<?php

use Middleware\Middleware;
use Responses\Response;
use Views\Core\View;
use Controllers\HomeController\HomeController;

// Remove any controller initialization from here - they should come from index.php

return [
    // Public Routes
    [
        'method' => 'POST',
        'path' => '/register',
        'handler' => function () use ($authController) {
            $response = $authController->processRegistration();
            
            // This manual redirect handling might be problematic
            if (isset($response->getHeaders()['Location'])) {
                header('Location: ' . $response->getHeaders()['Location']);
                exit;
            }
            
            return $response;
        }
    ],
    [
        'method' => 'POST',
        'path' => '/login',
        'handler' => function () use ($authController) {
            $response = $authController->processLogin();
            return $response; // Let index.php handle the response
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

    // Protected Routes
    [
        'method' => 'GET',
        'path' => '/users',
        'handler' => Middleware::authenticate($authController, function ($userId) use ($controller) {
            return $controller->getAllUsers(); // UserController already returns Response object
        })
    ],
    [
        'method' => 'GET',
        'path' => '/users/{id}',
        'handler' => Middleware::authenticate($authController, function ($userId, $id) use ($controller) {
            return $controller->getUserById($id); // UserController already returns Response object
        })
    ],
    [
        'method' => 'POST',
        'path' => '/users',
        'handler' => Middleware::authenticate($authController, function ($userId) use ($controller) {
            return $controller->createUser(); // UserController already returns Response object
        })
    ],
    [
        'method' => 'PUT',
        'path' => '/users/{id}',
        'handler' => Middleware::authenticate($authController, function ($userId, $id) use ($controller) {
            return $controller->updateUser($id); // UserController already returns Response object
        })
    ],
    [
        'method' => 'DELETE',
        'path' => '/users/{id}',
        'handler' => Middleware::authenticate($authController, function ($userId, $id) use ($controller) {
            return $controller->deleteUser($id); // UserController already returns Response object
        })
    ],
    // Protected Routes - Views that should only be accessible to logged-in users
    [
        'method' => 'GET',
        'path' => '/dashboard', // Example protected view
        'handler' => Middleware::authenticate($authController, function ($userId) use ($controller) {
            // Get user data from repository or controller
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
    // Admin-only view example:
    [
        'method' => 'GET',
        'path' => '/admin',
        'handler' => Middleware::authorize($authController, 'admin', function ($userId) use ($adminController) {
            return $adminController->index(); // This remains for the main admin dashboard
        })
    ],
    [
        'method' => 'GET',
        'path' => '/admin/users',
        'handler' => Middleware::authorize($authController, 'admin', function ($userId) use ($userAdminController) { // <<< CHANGED to $userAdminController
            return $userAdminController->index(); // <<< Calls the method in UserAdminController
        })
    ],
    // Admin user management routes
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
        'method' => 'POST', // Using POST with a _method field for DELETE since HTML forms don't support DELETE natively
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
            
            // This manual redirect handling might be problematic
            if (isset($response->getHeaders()['Location'])) {
                header('Location: ' . $response->getHeaders()['Location']);
                exit;
            }
            
            return $response;
        }
    ],
    // Process profile update
    [
        'method' => 'POST',
        'path' => '/profile/update',
        'handler' => Middleware::authenticate($authController, function ($userId) use ($profileController) {
            return $profileController->updateProfile($userId);
        })
    ],
    // Process password change
    [
        'method' => 'POST',
        'path' => '/profile/change-password',
        'handler' => Middleware::authenticate($authController, function ($userId) use ($profileController) {
            return $profileController->changePassword($userId);
        })
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
    // You'll also need routes for edit, show, etc.
    [
        'method' => 'GET',
        'path' => '/admin/books/{id}',
        'handler' => Middleware::authorize($authController, 'admin', function($userId, $id) use ($bookController) {
            return $bookController->show($id); // Fixed - direct parameter
        })
    ],
    [
        'method' => 'GET',
        'path' => '/admin/books/{id}/edit',
        'handler' => Middleware::authorize($authController, 'admin', function($userId, $id) use ($bookController) {
            // Assuming BookController will have an edit method
            return $bookController->edit($id); 
        })
    ],
    [
        'method' => 'POST', // Or PUT, but POST is consistent with user updates
        'path' => '/admin/books/{id}',
        'handler' => Middleware::authorize($authController, 'admin', function($userId, $id) use ($bookController) {
            // Assuming BookController will have an update method
            return $bookController->update($id); 
        })
    ],
    [
        'method' => 'POST', // Consistent with user delete route (using POST for form submission)
        'path' => '/admin/books/{id}/delete',
        'handler' => Middleware::authorize($authController, 'admin', function($userId, $id) use ($bookController) {
            // Assuming BookController will have a delete method
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
            return $catalogController->index(); // Use the method that exists in your CatalogController
        }
    ],
    [
        'method' => 'GET',
        'path' => '/catalog/{id}',
        'handler' => function($id) use ($catalogController) {
            return $catalogController->show($id); // Use the method that exists in your CatalogController
        }
    ],
    [
        'method' => 'GET',
        'path' => '/catalog/category/{id}',
        'handler' => function($id) use ($catalogController) {
            return $catalogController->category($id); // Use the method that exists in your CatalogController
        }
    ],
];