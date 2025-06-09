<?php

namespace Controllers\UserAdminController;

use Models\UserRepository\UserRepository;
use Responses\Response;
use Views\Core\View;

class UserAdminController
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Display a list of all users for the admin panel.
     */
    public function index(): Response
    {
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        
        // Ensure valid values
        $page = max(1, $page);
        $perPage = max(5, min(100, $perPage)); // Between 5 and 100
        
        // Calculate offset
        $offset = ($page - 1) * $perPage;
        
        // Get paginated users
        $users = $this->userRepository->getPaginated($perPage, $offset);
        
        // Get total count for pagination
        $totalUsers = $this->userRepository->getTotalCount();
        $totalPages = ceil($totalUsers / $perPage);
        
        $html = View::render('Admin/Users/Users.php', [
            'title' => 'Admin - User Management',
            'users' => $users,
            'heading' => 'User Management Panel',
            'pagination' => [
                'current' => $page,
                'perPage' => $perPage,
                'total' => $totalUsers,
                'totalPages' => $totalPages
            ]
        ]);
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    /**
     * Display the form to create a new user
     */
    public function create(): Response
    {
        $html = View::render('Admin/Users/Create.php', [
            'title' => 'Admin - Create User',
            'heading' => 'Create New User'
        ]);
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    /**
     * Store a new user in the database
     */
    public function store(): Response
    {
        // Get form data from request
        $data = $_POST;
        
        // Validate data
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            $html = View::render('Admin/Users/Create.php', [
                'title' => 'Admin - Create User',
                'heading' => 'Create New User',
                'error' => 'All fields are required',
                'data' => $data
            ]);
            
            return new Response($html, 400, ['Content-Type' => 'text/html']);
        }
        
        // Check if email is already in use
        $existingUser = $this->userRepository->getByEmail($data['email']);
        if ($existingUser) {
            $html = View::render('Admin/Users/Create.php', [
                'title' => 'Admin - Create User',
                'heading' => 'Create New User',
                'error' => 'Email already in use',
                'data' => $data
            ]);
            
            return new Response($html, 400, ['Content-Type' => 'text/html']);
        }
        
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Set role (default to 'user' if not provided)
        $data['role'] = $data['role'] ?? 'user';
        
        // Create user
        $userId = $this->userRepository->create($data);
        
        // Redirect to user list with success message
        return new Response(
            ['redirect' => '/admin/users?created=true'],
            303,
            ['Location' => '/admin/users?created=true']
        );
    }

    /**
     * Display a specific user
     */
    public function show(int $id): Response
    {
        $user = $this->userRepository->getById($id);
        
        if (!$user) {
            return new Response(
                ['redirect' => '/admin/users?error=user-not-found'],
                303,
                ['Location' => '/admin/users?error=user-not-found']
            );
        }
        
        $html = View::render('Admin/Users/Show.php', [
            'title' => 'Admin - User Details',
            'user' => $user,
            'heading' => 'User Details'
        ]);
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    /**
     * Display the form to edit a user
     */
    public function edit(int $id): Response
    {
        $user = $this->userRepository->getById($id);
        
        if (!$user) {
            return new Response(
                ['redirect' => '/admin/users?error=user-not-found'],
                303,
                ['Location' => '/admin/users?error=user-not-found']
            );
        }
        
        $html = View::render('Admin/Users/Edit.php', [
            'title' => 'Admin - Edit User',
            'user' => $user,
            'heading' => 'Edit User'
        ]);
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    /**
     * Update a specific user
     */
    public function update(int $id): Response
    {
        $data = [
            'username' => $this->request->post('username'),
            'email' => $this->request->post('email'),
            'role' => $this->request->post('role')
        ];

        $result = $this->userRepository->update($id, $data);

        if ($result === 'DUPLICATE_EMAIL') {
            return Response::view('Admin/Users/Edit', [
                'error' => 'A user with this email already exists',
                'user' => array_merge(['id' => $id], $data)
            ], 422);
        }

        return Response::redirect('/admin/users')
            ->withFlash('success', 'User updated successfully.');
    }

    /**
     * Delete a specific user
     */
    public function delete(int $id): Response
    {
        $user = $this->userRepository->getById($id);
        
        if (!$user) {
            return new Response(
                ['redirect' => '/admin/users?error=user-not-found'],
                303,
                ['Location' => '/admin/users?error=user-not-found']
            );
        }
        
        // Delete user
        $this->userRepository->delete($id);
        
        // Redirect to user list with success message
        return new Response(
            ['redirect' => '/admin/users?deleted=true'],
            303,
            ['Location' => '/admin/users?deleted=true']
        );
    }

    /**
     * Display a list of all users for the admin panel (API version).
     */
    public function apiIndex(): Response
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;

        $page = max(1, $page);
        $perPage = max(5, min(100, $perPage));

        $offset = ($page - 1) * $perPage;

        $users = $this->userRepository->getPaginated($perPage, $offset);
        $totalUsers = $this->userRepository->getTotalCount();

        return new Response([
            'users' => $users,
            'pagination' => [
                'current' => $page,
                'perPage' => $perPage,
                'total' => $totalUsers,
                'totalPages' => ceil($totalUsers / $perPage)
            ]
        ], 200);
    }
}