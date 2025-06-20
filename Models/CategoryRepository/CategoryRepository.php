<?php

namespace Models\CategoryRepository;

use Models\ORM\ORM;

class CategoryRepository {
    private $orm;

    public function __construct(ORM $orm) {
        if ($orm === null) {
            throw new \InvalidArgumentException('ORM instance cannot be null');
        }
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
        // Generate slug if not provided
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = $this->createSlug($data['name']);
        }
        if (empty($data['slug'])) {
            throw new \Exception('Category slug is required.');
        }
        // Check if slug exists
        $existing = $this->orm->table('categories')->where('slug', '=', $data['slug'])->first();
        if ($existing) {
            throw new \Exception('Category slug already exists.');
        }
        return $this->orm->table('categories')->insert($data);
    }

    private function createSlug($name) {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    }

    public function getTotalCount() {
        // Use the ORM's count() method
        return $this->orm->table('categories')->count();
    }

    public function update($id, array $data) {
        // Ensure data is a simple key-value array, not nested arrays
        $updateData = [
            'name' => $data['name'] ?? null,
            'slug' => $data['slug'] ?? null,
            'description' => $data['description'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->orm->table('categories')
            ->where('id', '=', $id)
            ->update($updateData);
    }

    public function delete($id) {
        return $this->orm->table('categories')
            ->where('id', '=', $id)
            ->delete();
    }

    public function getPaginated($limit, $offset) {
        return $this->orm->table('categories')
            ->select(['id', 'name', 'slug', 'description'])
            ->orderBy('name', 'ASC')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    /**
     * Find a category by name
     * 
     * @param string $name The name to search for
     * @return array|null The category data if found, null otherwise
     */
    public function findByName($name) {
        return $this->orm->table('categories')
            ->where('name', '=', $name)
            ->first();
    }
}