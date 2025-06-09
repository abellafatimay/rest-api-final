<?php

namespace Controllers\UserController;

use Models\DataRepositoryInterface\DataRepositoryInterface;
use Requests\RequestInterface\RequestInterface;
use Responses\Response;

class UserController {
    private $userRepository;
    private $request;

    public function __construct(DataRepositoryInterface $userRepository, RequestInterface $request) {
        $this->userRepository = $userRepository;
        $this->request = $request;
    }

    public function getAllUsers() {
        // Fixed parameter order - body first, then status code
        $users = $this->userRepository->getAll();
        return Response::json($users); // Always return JSON for this API-like method
    }

    public function getUserById($id) {
        $user = $this->userRepository->getById($id);
        if (empty($user)) {
            // Fixed parameter order
            return Response::json(['error' => 'User not found'], 404); // Always JSON
        }
        
        // Fixed parameter order and added safe array access
        // The original logic for $user[0] might be specific, review if it's always an array
        if (is_array($user) && isset($user[0]) && count($user) === 1 && is_array($user[0])) {
             return Response::json($user[0]); // Always JSON
        } else {
            // If $user isn't an array with index 0, return the entire user object
            return Response::json($user); // Always JSON
        }
    }

    public function createUser() {
        $data = $this->request->getBody();
        // Add validation for $data here before creating user
        $createdUser = $this->userRepository->create($data); // Assuming create returns the created user or its ID
        // Fixed parameter order
        return Response::json(['message' => 'User created', 'user' => $createdUser], 201); // Always JSON
    }

    public function updateUser($id, $data = null) {
        // If no data provided, get it from the request
        if ($data === null) {
            $data = $this->request->getBody();
        }
        
        // Get current user data
        $currentUser = $this->userRepository->getById($id);
        if (!$currentUser) {
            return Response::json(['error' => 'User not found'], 404); // Always JSON
        }
        
        // Update only the provided fields
        $updateData = [];
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        if (isset($data['password'])) {
            $updateData['password'] = $data['password'];
        }
        
        // Only update if there's something to update
        if (!empty($updateData)) {
            $this->userRepository->update($id, $updateData);
        }
        
        return Response::json(['message' => 'User updated'], 200); // Always JSON
    }

    public function deleteUser($id) {
        $user = $this->userRepository->getById($id);
        if (!$user) {
            return Response::json(['error' => 'User not found'], 404);
        }
        $this->userRepository->delete($id);
        // Fixed parameter order
        return Response::json(null, 204); // Always JSON, 204 means no content
    }

    /**
     * API endpoint for user dashboard data
     */
    public function apiDashboard($userId) {
        $user = $this->userRepository->getById($userId);
        if (!$user) {
            return Response::json(['error' => 'User not found'], 404); // Use Response::json
        }
        // Example: add more dashboard data as needed
        $dashboard = [
            'user_id' => $userId,
            'user_data' => $user,
            'recent_activity' => [
                'last_login' => date('Y-m-d H:i:s'),
                // Add more activity if available
            ],
            'quick_actions' => [
                ['label' => 'Edit Profile', 'url' => '/profile'],
                ['label' => 'Change Password', 'url' => '/profile']
            ]
        ];
        return Response::json($dashboard); // Use Response::json
    }

    public function apiGetUserById($id) {
        $user = $this->userRepository->getById($id);
        if (empty($user)) {
            return Response::json(['error' => 'User not found'], 404); // Use Response::json
        }

        return Response::json($user); // Use Response::json
    }

    public function apiCreateUser() {
        $data = $this->request->getBody();
        // Add validation for $data here
        $createdUser = $this->userRepository->create($data); // Assuming create returns the created user or ID

        return Response::json(['message' => 'User created', 'user' => $createdUser], 201); // Use Response::json
    }

    public function apiUpdateUser($id) {
        $data = $this->request->getBody();
        // Add validation for $data here
        $this->userRepository->update($id, $data);

        return Response::json(['message' => 'User updated'], 200); // Use Response::json
    }

    // It's good practice to have a consistent way to check for API requests if some methods serve both.
    // However, for UserController, most methods seem API-oriented already.
    // If any method needs to serve both HTML and JSON, add isApiRequest() similar to other controllers.
}