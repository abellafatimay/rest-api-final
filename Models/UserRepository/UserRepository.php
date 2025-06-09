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
     * Get paginated list of users using SQL LIMIT/OFFSET
     */
    public function getPaginated(int $limit = 10, int $offset = 0): array
    {
        // Use ORM's limit/offset for efficient SQL pagination
        return $this->orm->table('users')
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    /**
     * Get total number of users
     */
    public function getTotalCount(): int
    {
        // Use ORM's count method for efficiency
        return $this->orm->table('users')->count('id');
    }

    /**
     * Count all users in the repository
     */
    public function countAll() {
        return $this->orm->table('users')->count();
    }
}