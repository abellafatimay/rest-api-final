<?php

namespace Models\BookCategoryRepository;

use Models\ORM\ORM;

class BookCategoryRepository {
    private $orm;

    public function __construct(ORM $orm) {
        $this->orm = $orm;
    }

    public function getAll() {
        return $this->orm->table('categories')
            ->select(['id', 'name', 'description'])
            ->orderBy('name', 'ASC')
            ->get();
    }

    public function getCategoriesForBook($bookId) {
        return $this->orm->table('book_categories') // This sets the FROM clause
            ->select(['categories.*'])
            ->join('categories', 'categories.id', '=', 'book_categories.category_id')
            ->where('book_categories.book_id', '=', $bookId)
            ->get();
    }

    public function updateCategories($bookId, array $categoryIds) {
        // First delete existing relationships
        $this->orm->table('book_categories')
            ->where('book_id', '=', $bookId)
            ->delete();
        
        // Then insert new ones
        foreach ($categoryIds as $categoryId) {
            $this->orm->table('book_categories')->insert([
                'book_id' => $bookId,
                'category_id' => $categoryId
            ]);
        }
        
        return true;
    }
}