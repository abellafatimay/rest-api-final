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
        return new Response($this->userRepository->getAll(), 200);
    }

    public function getUserById($id) {
        $user = $this->userRepository->getById($id);
        if (empty($user)) {
            // Fixed parameter order
            return new Response(['error' => 'User not found'], 404);
        }
        
        // Fixed parameter order and added safe array access
        if (isset($user[0])) {
            return new Response($user[0], 200);
        } else {
            // If $user isn't an array with index 0, return the entire user object
            return new Response($user, 200);
        }
    }

    public function createUser() {
        $data = $this->request->getBody();
        $this->userRepository->create($data);
        // Fixed parameter order
        return new Response(['message' => 'User created'], 201);
    }

    public function updateUser($id, $data = null) {
        // If no data provided, get it from the request
        if ($data === null) {
            $data = $this->request->getBody();
        }
        
        // Get current user data
        $currentUser = $this->userRepository->getById($id);
        if (!$currentUser) {
            return new Response(['error' => 'User not found'], 404);
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
        
        return new Response(['message' => 'User updated'], 200);
    }

    public function deleteUser($id) {
        $this->userRepository->delete($id);
        // Fixed parameter order
        return new Response('', 204);
    }

}