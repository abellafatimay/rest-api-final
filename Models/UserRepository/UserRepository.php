<?php

namespace Models\UserRepository;

use Models\DataRepositoryInterface\DataRepositoryInterface;
use Models\ORM\ORM;

class UserRepository implements DataRepositoryInterface {
    private $orm;

    public function __construct(ORM $orm) {
        $this->orm = $orm;
    }

    public function getAll() {
        return $this->orm->table('users')->select()->get();
    }

    public function getById($id) {
        return $this->orm->table('users')->select()->where('id', '=', $id)->first();
    }

    public function create($data) {
        return $this->orm->table('users')->insert($data);
    }

    public function update($id, $data) {
        return $this->orm->table('users')->where('id', '=', $id)->update($data);
    }

    public function delete($id) {
        return $this->orm->table('users')->where('id', '=', $id)->delete();
    }

    public function getByEmail($email) {
        return $this->orm->table('users')->select()->where('email', '=', $email)->first();
    }

    // If you want to keep findByEmail as an alias, have it call the implementation directly
    public function findByEmail($email) {
        return $this->getByEmail($email);
    }

    public function updateToken($userId, $token) {
        try {
            // Use ORM instead of direct PDO
            $result = $this->orm->table('users')
                ->where('id', '=', $userId)
                ->update(['token' => $token]);
            
            error_log("Token update for user $userId: " . ($result ? 'success' : 'failed'));
            return $result;
        } catch (\Exception $e) {
            error_log('Token update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get paginated list of users
     */
    public function getPaginated(int $limit = 10, int $offset = 0): array
    {
        // Get all users and manually implement pagination
        // This is less efficient but will work with your ORM's public API
        $allUsers = $this->orm->table('users')->select()->get();
        
        // Sort by ID descending (newest first)
        usort($allUsers, function($a, $b) {
            return $b['id'] <=> $a['id']; 
        });
        
        // Apply pagination
        return array_slice($allUsers, $offset, $limit);
    }

    /**
     * Get total number of users
     */
    public function getTotalCount(): int
    {
        // Count all users
        $allUsers = $this->orm->table('users')->select()->get();
        return count($allUsers);
    }

    /**
     * Count all users in the repository
     */
    public function countAll() {
        return $this->orm->table('users')->count();
    }
}