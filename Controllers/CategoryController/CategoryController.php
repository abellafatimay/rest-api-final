<?php

namespace Controllers\CategoryController;

use Models\CategoryRepository\CategoryRepository;
use Models\BookRepository\BookRepository; // Add this import
use Responses\Response;
use Views\Core\View;

class CategoryController {
    private $categoryRepository;
    private $bookRepository; // Add this property
    
    public function __construct(CategoryRepository $categoryRepository, BookRepository $bookRepository) {
        $this->categoryRepository = $categoryRepository;
        $this->bookRepository = $bookRepository; // Initialize bookRepository
    }
    
    // Handles base category CRUD
    public function index() {
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 10;
        
        // Calculate offset
        $offset = ($page - 1) * $perPage;
        
        // Get categories with pagination
        $categories = $this->categoryRepository->getPaginated($perPage, $offset);
        $totalCategories = $this->categoryRepository->getTotalCount();
        $totalPages = ceil($totalCategories / $perPage);
        
        $html = View::render('Admin/Categories/Categories.php', [
            'title' => 'Category Management',
            'categories' => $categories,
            'pagination' => [
                'current' => $page,
                'perPage' => $perPage,
                'total' => $totalCategories,
                'totalPages' => $totalPages
            ]
        ]);
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }
    
    public function create() {
        $html = View::render('Admin/Categories/Create.php', [
            'title' => 'Create Category'
        ]);
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }
    
    public function store() {
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'created_at' => date('Y-m-d H:i:s') // Add this line to include the current timestamp
        ];
        
        $success = $this->categoryRepository->create($data);
        
        if ($success) {
            return new Response(
                ['redirect' => '/admin/categories?created=true'],
                303,
                ['Location' => '/admin/categories?created=true']
            );
        }
        
        $html = View::render('Admin/Categories/Create.php', [
            'title' => 'Create Category',
            'category' => $data,
            'error' => 'Failed to create category.'
        ]);
        return new Response($html, 422, ['Content-Type' => 'text/html']);
    }
    
    public function edit($id) {
        $category = $this->categoryRepository->getById($id);
        
        if (!$category) {
            return new Response(
                ['redirect' => '/admin/categories?error=not-found'],
                303,
                ['Location' => '/admin/categories?error=not-found']
            );
        }
        
        $html = View::render('Admin/Categories/Edit.php', [
            'title' => 'Edit Category',
            'category' => $category
        ]);
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }
    
    public function update($id) {
        $category = $this->categoryRepository->getById($id);
        
        if (!$category) {
            return new Response(
                ['redirect' => '/admin/categories?error=not-found'],
                303,
                ['Location' => '/admin/categories?error=not-found']
            );
        }
        
        $data = [
            'name' => $_POST['name'] ?? $category['name'],
            'description' => $_POST['description'] ?? $category['description']
        ];
        
        $success = $this->categoryRepository->update($id, $data);
        
        if ($success) {
            return new Response(
                ['redirect' => '/admin/categories?updated=true'],
                303,
                ['Location' => '/admin/categories?updated=true']
            );
        }
        
        $html = View::render('Admin/Categories/Edit.php', [
            'title' => 'Edit Category',
            'category' => array_merge($category, $data),
            'error' => 'Failed to update category.'
        ]);
        return new Response($html, 422, ['Content-Type' => 'text/html']);
    }
    
    public function delete($id) {
        $category = $this->categoryRepository->getById($id);
        
        if (!$category) {
            return new Response(
                ['redirect' => '/admin/categories?error=not-found'],
                303,
                ['Location' => '/admin/categories?error=not-found']
            );
        }
        
        $success = $this->categoryRepository->delete($id);
        
        if ($success) {
            return new Response(
                ['redirect' => '/admin/categories?deleted=true'],
                303,
                ['Location' => '/admin/categories?deleted=true']
            );
        }
        
        return new Response(
            ['redirect' => '/admin/categories?error=delete-failed'],
            303,
            ['Location' => '/admin/categories?error=delete-failed']
        );
    }
    
    // Public methods
    public function list() {
        // Get all categories
        $categories = $this->categoryRepository->getAll();
        
        // For each category, get the book count
        foreach ($categories as &$category) {
            $category['book_count'] = $this->bookRepository->getCategoryBookCount($category['id']);
        }
        
        $html = View::render('Categories/List.php', [
            'title' => 'Book Categories',
            'categories' => $categories
        ]);
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }
    
    public function view($id) {
        $category = $this->categoryRepository->getById($id);
        
        if (!$category) {
            return new Response(
                ['redirect' => '/categories?error=not-found'],
                303,
                ['Location' => '/categories?error=not-found']
            );
        }
        
        // Get all books in this category
        $books = $this->bookRepository->getByCategory($id);
        
        $html = View::render('Categories/View.php', [
            'title' => $category['name'],
            'category' => $category,
            'books' => $books
        ]);
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }
}