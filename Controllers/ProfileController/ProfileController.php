<?php

namespace Controllers\ProfileController;

use Models\DataRepositoryInterface\DataRepositoryInterface;
use Controllers\AuthController\AuthController;
use Requests\RequestInterface\RequestInterface;
use Responses\Response;
use Views\Core\View;

class ProfileController {
    private $userRepository;
    private $request;
    private $authController;

    public function __construct(
        DataRepositoryInterface $userRepository, 
        RequestInterface $request,
        AuthController $authController
    ) {
        $this->userRepository = $userRepository;
        $this->request = $request;
        $this->authController = $authController;
    }

    public function showProfile($userId, array $additionalData = []) {
        $userData = $this->userRepository->getById($userId);
        
        $data = [
            'title' => 'My Profile',
            'user_id' => $userId,
            'user_data' => $userData,
            'is_admin' => isset($userData['role']) && $userData['role'] === 'admin'
        ];
        
        // Merge any additional data (like error/success messages)
        $data = array_merge($data, $additionalData);

        if ($this->isApiRequest()) {
            // Remove HTML specific data for API response
            unset($data['title']);
            unset($data['is_admin']); // Or keep if relevant for API
            return Response::json($data);
        }
        
        $html = View::render('Profile/profile.php', $data);
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    public function updateProfile($userId) {
        // Get form data
        $data = [
            'name' => $this->request->getParam('username')
        ];
        
        // Validate input
        if (empty($data['name'])) {
            if ($this->isApiRequest()) {
                return Response::json(['error' => 'Username cannot be empty'], 422);
            }
            return $this->showProfile($userId, ['error' => 'Username cannot be empty']);
        }
        
        // Get current user data to check for changes
        $userData = $this->userRepository->getById($userId);
        
        // Compare with existing data
        if ($userData['name'] === $data['name']) {
            if ($this->isApiRequest()) {
                return Response::json(['message' => 'No changes were made']);
            }
            return $this->showProfile($userId, ['message' => 'No changes were made']);
        }
        
        // Update user data
        $this->userRepository->update($userId, ['name' => $data['name']]);
        
        if ($this->isApiRequest()) {
            return Response::json(['message' => 'Profile updated successfully']);
        }
        // Show profile with success message
        return $this->showProfile($userId, ['message' => 'Profile updated successfully']);
    }
    
    public function changePassword($userId) {
        $currentPassword = $this->request->getParam('current_password');
        $newPassword = $this->request->getParam('new_password');
        $confirmPassword = $this->request->getParam('confirm_password');
        
        // Validation
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            if ($this->isApiRequest()) {
                return Response::json(['error' => 'All password fields are required'], 422);
            }
            return $this->showProfile($userId, ['error' => 'All password fields are required']);
        }
        
        if ($newPassword !== $confirmPassword) {
            if ($this->isApiRequest()) {
                return Response::json(['error' => 'New passwords do not match'], 422);
            }
            return $this->showProfile($userId, ['error' => 'New passwords do not match']);
        }
        
        // Business logic for password change
        $result = $this->authController->changePassword($userId, $currentPassword, $newPassword);
        
        if (!$result['success']) {
            if ($this->isApiRequest()) {
                return Response::json(['error' => $result['message'] ?? 'Current password is incorrect'], 400);
            }
            return $this->showProfile($userId, ['error' => $result['message'] ?? 'Current password is incorrect']);
        }
        
        if ($this->isApiRequest()) {
            return Response::json(['message' => 'Password changed successfully']);
        }
        // Success
        return $this->showProfile($userId, ['message' => 'Password changed successfully']);
    }

    /**
     * API endpoint for user profile data
     */
    public function apiProfile($userId) {
        $userData = $this->userRepository->getById($userId);
        if (!$userData) {
            return new Response(['error' => 'User not found'], 404); // Already JSON
        }
        // Ensure it's a JSON response
        return Response::json($userData);
    }

    /**
     * API endpoint for user profile data (detailed)
     */
    public function apiShowProfile($userId) {
        $userData = $this->userRepository->getById($userId);

        return new Response([
            'user_id' => $userId,
            'user_data' => $userData,
            'is_admin' => isset($userData['role']) && $userData['role'] === 'admin'
        ], 200);
    }

    private function isApiRequest() {
        // Helper function to check if the request is an API request
        if ($this->request && method_exists($this->request, 'getUri')) {
            return strpos($this->request->getUri(), '/api/') !== false;
        }
        // Fallback for when $this->request is not set or doesn't have getUri
        if (isset($_SERVER['REQUEST_URI'])) {
             return strpos($_SERVER['REQUEST_URI'], '/api/') !== false;
        }
        return false;
    }
}