<?php

namespace Models\BookRepository;

use Models\ORM\ORM;
use Models\DataRepositoryInterface;

class BookRepository {
    protected $orm;
    
    public function __construct(ORM $orm) {
        $this->orm = $orm;
    }
    
    /**
     * Get all books
     */
    public function getAll() {
        return $this->orm->table('books')->select()->get();
    }
    
    /**
     * Get book by ID
     */
    public function getById($id) {
        return $this->orm->table('books')->where('id', '=', $id)->first();
    }
    
    /**
     * Create a new book
     */
    public function create(array $data) {
        return $this->orm->table('books')->insert($data);
    }
    
    /**
     * Update book
     */
    public function update($id, array $data) {
        return $this->orm->table('books')->where('id', '=', $id)->update($data);
    }
    
    /**
     * Delete book
     */
    public function delete($id) {
        return $this->orm->table('books')->where('id', '=', $id)->delete();
    }
    
    /**
     * Get paginated list of books
     */
    public function getPaginated(int $limit = 10, int $offset = 0): array {
        // Get all books and manually implement pagination
        $allBooks = $this->orm->table('books')->select()->get();
        
        // Sort by ID descending (newest first)
        usort($allBooks, function($a, $b) {
            return $b['id'] <=> $a['id']; 
        });
        
        // Apply pagination
        return array_slice($allBooks, $offset, $limit);
    }
    
    /**
     * Get total number of books
     */
    public function getTotalCount(): int {
        $allBooks = $this->orm->table('books')->select()->get();
        return count($allBooks);
    }
    
    /**
     * Get categories for a book
     */
    public function getCategories($bookId)
    {
        return $this->orm->table('categories')
            ->select('categories.*')
            ->join('book_categories', 'categories.id', '=', 'book_categories.category_id')
            ->where('book_categories.book_id', '=', $bookId)
            ->get();
    }

    /**
     * Update categories for a book
     */
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
    
    /**
     * Get books by category
     */
    public function getByCategory($categoryId) {
        return $this->orm->table('books')
            ->select('books.*')
            ->join('book_categories', 'books.id', '=', 'book_categories.book_id')
            ->where('book_categories.category_id', '=', $categoryId)
            ->get();
    }
}