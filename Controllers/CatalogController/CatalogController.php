<?php

namespace Controllers\CatalogController;

use Models\BookRepository\BookRepository;
use Models\CategoryRepository\CategoryRepository;
use Responses\Response;
use Views\Core\View;

class CatalogController {
    private $bookRepository;
    private $categoryRepository;
    
    public function __construct(BookRepository $bookRepository, CategoryRepository $categoryRepository) {
        $this->bookRepository = $bookRepository;
        $this->categoryRepository = $categoryRepository;
    }
    
    // Main catalog display with search, filtering and pagination
    public function index() {
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 12; // Or your preferred number of books per page
        
        // Get search and filter parameters
        $searchQuery = $_GET['search'] ?? ''; // Renamed to avoid conflict with $search variable later
        $categoryId = isset($_GET['category']) && !empty($_GET['category']) ? (int)$_GET['category'] : null;
        
        // Calculate offset
        $offset = ($page - 1) * $perPage;
        
        // Get filtered books and their total count
        // The BookRepository's search and getSearchCount methods now handle all scenarios
        // Pass both searchQuery and categoryId to the search methods
        $books = $this->bookRepository->search($searchQuery, $categoryId, $perPage, $offset);
        $totalBooks = $this->bookRepository->getSearchCount($searchQuery, $categoryId);
        
        $totalPages = ($perPage > 0 && $totalBooks > 0) ? ceil($totalBooks / $perPage) : 1; // Ensure totalPages is at least 1 if there are books
        if ($totalBooks == 0) $totalPages = 0; // No books, no pages
        
        // Get categories for filter dropdown
        $allCategories = $this->categoryRepository->getAll(); 
        
        $html = View::render('Catalog/Browse.php', [
            'title' => 'Book Catalog',
            'books' => $books,
            'categories' => $allCategories, // Ensure view uses this variable name
            'currentCategory' => $categoryId,
            'search' => $searchQuery, // Pass the original search query to the view
            'pagination' => [
                'current' => $page,
                'perPage' => $perPage,
                'total' => $totalBooks,
                'totalPages' => $totalPages
            ]
        ]);
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }
    
    // Show details of a specific book
    public function show($id) {
        $book = $this->bookRepository->getById($id);
        
        if (!$book) {
            return new Response('Book not found', 404);
        }
        
        // Get book categories
        $bookCategories = $this->bookRepository->getCategories($id);
        
        $html = View::render('Catalog/Detail.php', [
            'title' => $book['title'],
            'book' => $book,
            'categories' => $bookCategories
        ]);
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }
    
    // Show books in a specific category
    public function category($id) {
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 12;
        
        // Calculate offset
        $offset = ($page - 1) * $perPage;
        
        // Get category info
        $category = $this->categoryRepository->getById($id);
        
        if (!$category) {
            return new Response('Category not found', 404);
        }
        
        // Get books in this category
        $books = $this->bookRepository->getByCategory($id, $perPage, $offset);
        $totalBooks = $this->bookRepository->getCategoryBookCount($id);
        $totalPages = ceil($totalBooks / $perPage);
        
        $html = View::render('Catalog/CategoryView.php', [
            'title' => 'Books in ' . $category['name'],
            'category' => $category,
            'books' => $books,
            'pagination' => [
                'current' => $page,
                'perPage' => $perPage,
                'total' => $totalBooks,
                'totalPages' => $totalPages
            ]
        ]);
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }
}
