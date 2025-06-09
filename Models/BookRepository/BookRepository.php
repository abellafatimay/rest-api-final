<?php

namespace Models\BookRepository;

use Models\ORM\ORM;

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
     * Get all books with their category names
     */
    public function getAllWithCategoryNames() {
        return $this->orm->table('books')
            ->leftJoin('book_categories', 'books.id', '=', 'book_categories.book_id')
            ->leftJoin('categories', 'book_categories.category_id', '=', 'categories.id')
            ->select([
                'books.*',
                'GROUP_CONCAT(categories.name SEPARATOR ", ") AS category_name'
            ])
            ->groupBy('books.id')
            ->orderBy('books.title', 'ASC')
            ->get();
    }
    
    /**
     * Get book by ID
     */
    public function getById($id) {
        // Use LEFT JOINs to get the book with its category (if any)
        return $this->orm->table('books')
            ->leftJoin('book_categories', 'books.id', '=', 'book_categories.book_id')
            ->leftJoin('categories', 'book_categories.category_id', '=', 'categories.id')
            ->select([
                'books.*',
                'categories.id AS category_id_alias', // Use alias to avoid potential name collision
                'categories.name AS category_name'
            ])
            ->where('books.id', '=', $id)
            ->first();
    }
    
    /**
     * Create a new book
     *
     * @param array $data Associative array of book data.
     * @return int|string|false Returns the last inserted ID on success, 
     *                          'DUPLICATE_TITLE' if title already exists,
     *                          'DUPLICATE_ISBN' if ISBN already exists,
     *                          or false on other errors.
     */
    public function create(array $data) {
        // Check for duplicate title if title is provided and not empty
        if (!empty($data['title'])) {
            $existingByTitle = $this->findByTitle($data['title']);
            if ($existingByTitle) {
                return 'DUPLICATE_TITLE'; // Signal duplicate title
            }
        }

        // Check for duplicate ISBN if ISBN is provided and not empty
        if (!empty($data['isbn'])) {
            $existingByIsbn = $this->findByIsbn($data['isbn']);
            if ($existingByIsbn) {
                return 'DUPLICATE_ISBN'; // Signal duplicate ISBN
            }
        }

        // Ensure all expected fields are present, defaulting if necessary
        $bookData = [
            'title' => $data['title'] ?? null,
            'author' => $data['author'] ?? null,
            'description' => $data['description'] ?? null,
            'publication_year' => isset($data['publication_year']) ? (int)$data['publication_year'] : null,
            'isbn' => $data['isbn'] ?? null,
            'available' => isset($data['available']) ? (int)$data['available'] : 0,
            'cover_image' => $data['cover_image'] ?? null // This path comes from the BookController
        ]; // Changed from }; to ];

        // Use the ORM's insert method
        // The specific method and return value might vary based on your ORM implementation
        // This assumes $this->orm->insert returns the last inserted ID or throws an exception on failure
        try {
            // If your ORM uses table() then insert()
            $lastInsertId = $this->orm->table('books')->insert($bookData);
            return $lastInsertId;
        } catch (\Exception $e) {
            error_log("Error creating book: " . $e->getMessage());
            return false;
        }
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
        return $this->orm->table('books')
            ->leftJoin('book_categories', 'books.id', '=', 'book_categories.book_id')
            ->leftJoin('categories', 'book_categories.category_id', '=', 'categories.id')
            ->select([
                'books.*',
                'GROUP_CONCAT(categories.name SEPARATOR ", ") AS category_name'
            ])
            ->groupBy('books.id')
            ->orderBy('books.title', 'ASC')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }
    
    /**
     * Get total number of books
     */
    public function getTotalCount(): int {
        $this->orm->table('books');
        return $this->orm->count('books.id'); // Or just 'id' if table context is clear
    }

    /**
     * Find a book by its title.
     *
     * @param string $title
     * @return mixed Returns the book record if found, otherwise null or false depending on ORM.
     */
    public function findByTitle(string $title)
    {
        return $this->orm->table('books')->where('title', '=', $title)->first();
    }

    /**
     * Find a book by its ISBN.
     *
     * @param string $isbn
     * @return mixed Returns the book record if found, otherwise null or false depending on ORM.
     */
    public function findByIsbn(string $isbn)
    {
        return $this->orm->table('books')->where('isbn', '=', $isbn)->first();
    }
    
    /**z
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
     * Get books by category with pagination
     */
    public function getByCategory($categoryId, $limit = 10, $offset = 0) {
        return $this->orm->table('books')
            ->select('books.*')
            ->join('book_categories', 'books.id', '=', 'book_categories.book_id')
            ->where('book_categories.category_id', '=', $categoryId)
            ->orderBy('books.title', 'ASC')
            ->limit((int)$limit)
            ->offset((int)$offset)
            ->get();
    }

    /**
     * Get count of books in a category
     */
    public function getCategoryBookCount($categoryId) {
        $this->orm->table('books')
            ->join('book_categories', 'books.id', '=', 'book_categories.book_id')
            ->where('book_categories.category_id', '=', $categoryId);
        // Use a distinct count on the books.id when joins are involved
        return $this->orm->count("DISTINCT books.id");
    }
    
    /**
     * Search books by title, author, or ISBN, optionally filtered by category.
     */
    public function search($query, $categoryId = null, $limit = 10, $offset = 0) {
        $this->orm->table('books')->select('books.*'); // Start building the query

        if ($categoryId !== null) {
            $this->orm->join('book_categories', 'books.id', '=', 'book_categories.book_id')
                      ->where('book_categories.category_id', '=', $categoryId);
        }

        if (!empty($query) && $categoryId !== null) {
            // Scenario 1: Text search AND Category filter (A AND (B OR C OR D))
            $this->orm->andWhereNested(function($q_nested) use ($query) {
                $q_nested->where('books.title', 'LIKE', "%{$query}%")
                         ->orWhere('books.author', 'LIKE', "%{$query}%")
                         ->orWhere('books.isbn', 'LIKE', "%{$query}%");
            });
        } elseif (!empty($query)) {
            // Scenario 2: Text search only (B OR C OR D)
            $this->orm->where('books.title', 'LIKE', "%{$query}%")
                      ->orWhere('books.author', 'LIKE', "%{$query}%")
                      ->orWhere('books.isbn', 'LIKE', "%{$query}%");
        }
        // If only categoryId is provided, the join and where for category are already added.
        // If neither query nor categoryId, it fetches all (respecting pagination).

        return $this->orm->orderBy('books.title', 'ASC')
                         ->limit((int)$limit)
                         ->offset((int)$offset)
                         ->get();
    }

    /**
     * Get count of search results, optionally filtered by category.
     */
    public function getSearchCount($query, $categoryId = null) {
        $this->orm->table('books'); // Start building the query for count

        if ($categoryId !== null) {
            $this->orm->join('book_categories', 'books.id', '=', 'book_categories.book_id')
                      ->where('book_categories.category_id', '=', $categoryId);
        }

        if (!empty($query) && $categoryId !== null) {
            // Scenario 1: Text search AND Category filter (A AND (B OR C OR D))
            $this->orm->andWhereNested(function($q_nested) use ($query) {
                $q_nested->where('books.title', 'LIKE', "%{$query}%")
                         ->orWhere('books.author', 'LIKE', "%{$query}%")
                         ->orWhere('books.isbn', 'LIKE', "%{$query}%");
            });
        } elseif (!empty($query)) {
            // Scenario 2: Text search only (B OR C OR D)
            $this->orm->where('books.title', 'LIKE', "%{$query}%")
                      ->orWhere('books.author', 'LIKE', "%{$query}%")
                      ->orWhere('books.isbn', 'LIKE', "%{$query}%");
        }
        // If only categoryId, the join and where are set.
        // If neither, it counts all.

        // When joins are involved, it's safer to count distinct IDs of the main table.
        $columnToCount = "DISTINCT books.id";
        if (empty($this->orm->getJoinClauses())) { // Check if joins were actually added
             $columnToCount = "books.id"; // or just 'id' if table context is clear in ORM count
        }
        return $this->orm->count($columnToCount);
    }
    
    // Helper in BookRepository to access joinClauses from ORM if needed (requires public getter in ORM)
    // For the getSearchCount logic above, you might need a way to check if joins were added to the ORM instance.
    // Add this to ORM.php:
    // public function getJoinClauses() { return $this->joinClauses; }
    // Then you can use it in BookRepository if necessary.
    // For now, the logic in getSearchCount assumes if $categoryId is set, joins are active.

    // ... other methods like getById, create, update, delete, updateCategory ...
}
