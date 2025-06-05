<?php

namespace Models\CategoryRepository;

use Models\ORM\ORM;

class CategoryRepository {
    private $orm;

    public function __construct(ORM $orm) {
        $this->orm = $orm;
    }

    public function getAll() {
        return $this->orm->table('categories')
            ->select(['id', 'name', 'slug', 'description'])
            ->orderBy('name', 'ASC')
            ->get();
    }

    public function getById($id) {
        return $this->orm->table('categories')
            ->select()
            ->where('id', '=', $id)
            ->first();
    }

    public function create(array $data) {
        return $this->orm->table('categories')->insert([
            'name' => $data['name'],
            'slug' => $this->createSlug($data['name']),
            'description' => $data['description'] ?? null
        ]);
    }

    private function createSlug($name, $excludeId = null) {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    }
}