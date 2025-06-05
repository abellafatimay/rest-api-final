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
            return $this->showProfile($userId, ['error' => 'Username cannot be empty']);
        }
        
        // Get current user data to check for changes
        $userData = $this->userRepository->getById($userId);
        
        // Compare with existing data
        if ($userData['name'] === $data['name']) {
            return $this->showProfile($userId, ['message' => 'No changes were made']);
        }
        
        // Update user data
        $this->userRepository->update($userId, ['name' => $data['name']]);
        
        // Show profile with success message
        return $this->showProfile($userId, ['message' => 'Profile updated successfully']);
    }
    
    public function changePassword($userId) {
        $currentPassword = $this->request->getParam('current_password');
        $newPassword = $this->request->getParam('new_password');
        $confirmPassword = $this->request->getParam('confirm_password');
        
        // Validation
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            return $this->showProfile($userId, ['error' => 'All password fields are required']);
        }
        
        if ($newPassword !== $confirmPassword) {
            return $this->showProfile($userId, ['error' => 'New passwords do not match']);
        }
        
        // Business logic for password change
        $result = $this->authController->changePassword($userId, $currentPassword, $newPassword);
        
        if (!$result['success']) {
            return $this->showProfile($userId, ['error' => $result['message'] ?? 'Current password is incorrect']);
        }
        
        // Success
        return $this->showProfile($userId, ['message' => 'Password changed successfully']);
    }
}