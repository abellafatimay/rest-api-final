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

    public function updateToken($userId, $token) {
        return $this->orm
            ->table('users')
            ->where('id', '=', $userId)
            ->update(['token' => $token]);
    }
}