<?php

namespace Controllers\CategoryController;

use Models\CategoryRepository\CategoryRepository;
use Models\BookRepository\BookRepository;
use Responses\Response;
use Views\Core\View;
use Services\Validation;

// Ensure Validation class is loaded if not using Composer autoload
if (!class_exists('Services\\Validation')) {
    require_once __DIR__ . '/../../Services/Validation.php';
}

class CategoryController {
    private $categoryRepository;
    private $bookRepository;
    private $request;
    
    public function __construct(CategoryRepository $categoryRepository, BookRepository $bookRepository, $request = null) {
        $this->categoryRepository = $categoryRepository;
        $this->bookRepository = $bookRepository;
        $this->request = $request;
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
        
        if ($this->isApiRequest()) {
            return Response::json([
                'title' => 'Category Management',
                'categories' => $categories,
                'pagination' => [
                    'current' => $page,
                    'perPage' => $perPage,
                    'total' => $totalCategories,
                    'totalPages' => $totalPages
                ]
            ]);
        }
        return Response::view('Admin/Categories/Categories', [
            'title' => 'Category Management',
            'categories' => $categories,
            'pagination' => [
                'current' => $page,
                'perPage' => $perPage,
                'total' => $totalCategories,
                'totalPages' => $totalPages
            ]
        ]);
    }
    
    public function create() {
        if ($this->isApiRequest()) {
            return Response::json([
                'title' => 'Create Category',
                'category' => [], // Empty category for form
                'error' => null  // Default empty error
            ]);
        }
        return Response::view('Admin/Categories/Create', [
            'title' => 'Create Category',
            'category' => [], // Empty category for form
            'error' => null  // Default empty error
        ]);
    }
    
    public function store() {
        $postData = $this->request->post();
        $required = ['name', 'slug'];
        $validation = Validation::requireFields($postData, $required);
        if (!$validation[0]) {
            $errorMsg = implode(' ', $validation[1]);
            if ($this->isApiRequest()) {
                return Response::json(['error' => $errorMsg, 'category' => $postData], 422);
            }
            return Response::view('Admin/Categories/Create', [
                'error' => $errorMsg,
                'category' => $postData
            ], 422);
        }
        if (!Validation::length($postData['name'], 2, 100)) {
            if ($this->isApiRequest()) {
                return Response::json(['error' => 'Category name must be between 2 and 100 characters.', 'category' => $postData], 422);
            }
            return Response::view('Admin/Categories/Create', [
                'error' => 'Category name must be between 2 and 100 characters.',
                'category' => $postData
            ], 422);
        }
        if (!Validation::length($postData['slug'], 2, 100)) {
            if ($this->isApiRequest()) {
                return Response::json(['error' => 'Slug must be between 2 and 100 characters.', 'category' => $postData], 422);
            }
            return Response::view('Admin/Categories/Create', [
                'error' => 'Slug must be between 2 and 100 characters.',
                'category' => $postData
            ], 422);
        }

        $data = [
            'name' => $postData['name'],
            'slug' => $postData['slug'],
            'description' => $postData['description']
        ];

        $result = $this->categoryRepository->create($data);

        if ($result === 'DUPLICATE_SLUG') {
            if ($this->isApiRequest()) {
                return Response::json(['error' => 'A category with this slug already exists', 'category' => $postData], 422);
            }
            return Response::view('Admin/Categories/Create', [
                'error' => 'A category with this slug already exists',
                'category' => $postData
            ], 422);
        }

        if ($this->isApiRequest()) {
            return Response::json(['message' => 'Category created successfully.', 'category_id' => $result], 201);
        }
        return Response::redirect('/admin/categories')
            ->withFlash('success', 'Category created successfully.');
    }
    
    public function edit($id) {
        $category = $this->categoryRepository->getById($id);
        
        if (!$category) {
            if ($this->isApiRequest()) {
                return Response::json(['error' => 'Category not found'], 404);
            }
            return Response::redirect('/admin/categories')
                ->withFlash('error', 'Category not found');
        }
        
        if ($this->isApiRequest()) {
            return Response::json([
                'title' => 'Edit Category',
                'category' => $category,
                'error' => null
            ]);
        }
        return Response::view('Admin/Categories/Edit', [
            'title' => 'Edit Category',
            'category' => $category,
            'error' => null
        ]);
    }
    
    public function update($id) {
        $postData = $this->request->post();
        $required = ['name', 'slug'];
        $validation = Validation::requireFields($postData, $required);
        if (!$validation[0]) {
            $errorMsg = implode(' ', $validation[1]);
            if ($this->isApiRequest()) {
                return Response::json(['error' => $errorMsg, 'category' => array_merge(['id' => $id], $postData)], 422);
            }
            return Response::view('Admin/Categories/Edit', [
                'error' => $errorMsg,
                'category' => array_merge(['id' => $id], $postData)
            ], 422);
        }
        if (!Validation::length($postData['name'], 2, 100)) {
            if ($this->isApiRequest()) {
                return Response::json(['error' => 'Category name must be between 2 and 100 characters.', 'category' => array_merge(['id' => $id], $postData)], 422);
            }
            return Response::view('Admin/Categories/Edit', [
                'error' => 'Category name must be between 2 and 100 characters.',
                'category' => array_merge(['id' => $id], $postData)
            ], 422);
        }
        if (!Validation::length($postData['slug'], 2, 100)) {
            if ($this->isApiRequest()) {
                return Response::json(['error' => 'Slug must be between 2 and 100 characters.', 'category' => array_merge(['id' => $id], $postData)], 422);
            }
            return Response::view('Admin/Categories/Edit', [
                'error' => 'Slug must be between 2 and 100 characters.',
                'category' => array_merge(['id' => $id], $postData)
            ], 422);
        }
        $data = [
            'name' => $postData['name'],
            'slug' => $postData['slug'],
            'description' => $postData['description']
        ];
        $result = $this->categoryRepository->update($id, $data);
        if ($result === 'DUPLICATE_SLUG') {
            if ($this->isApiRequest()) {
                return Response::json(['error' => 'A category with this slug already exists', 'category' => array_merge(['id' => $id], $data)], 422);
            }
            return Response::view('Admin/Categories/Edit', [
                'error' => 'A category with this slug already exists',
                'category' => array_merge(['id' => $id], $data)
            ], 422);
        }
        if ($this->isApiRequest()) {
            return Response::json(['message' => 'Category updated successfully.']);
        }
        return Response::redirect('/admin/categories')
            ->withFlash('success', 'Category updated successfully.');
    }
    
    public function delete($id) {
        $category = $this->categoryRepository->getById($id);
        
        if (!$category) {
            if ($this->isApiRequest()) {
                return Response::json(['error' => 'Category not found'], 404);
            }
            return Response::redirect('/admin/categories')
                ->withFlash('error', 'Category not found');
        }
        
        $success = $this->categoryRepository->delete($id);
        
        if ($success) {
            if ($this->isApiRequest()) {
                return Response::json(['message' => 'Category deleted successfully']);
            }
            return Response::redirect('/admin/categories')
                ->withFlash('success', 'Category deleted successfully');
        }
        
        if ($this->isApiRequest()) {
            return Response::json(['error' => 'Failed to delete category'], 500);
        }
        return Response::redirect('/admin/categories')
            ->withFlash('error', 'Failed to delete category');
    }
    
    // Public methods
    public function list() {
        // Get all categories
        $categories = $this->categoryRepository->getAll();
        
        // For each category, get the book count
        foreach ($categories as &$category) {
            $category['book_count'] = $this->bookRepository->getCategoryBookCount($category['id']);
        }
        
        if ($this->isApiRequest()) {
            return Response::json([
                'title' => 'Book Categories',
                'categories' => $categories
            ]);
        }
        return Response::view('Categories/List', [
            'title' => 'Book Categories',
            'categories' => $categories
        ]);
    }
    
    public function view($id) {
        $category = $this->categoryRepository->getById($id);
        
        if (!$category) {
            if ($this->isApiRequest()) {
                return Response::json(['error' => 'Category not found'], 404);
            }
            return Response::redirect('/categories')
                ->withFlash('error', 'Category not found');
        }
        
        // Get all books in this category
        $books = $this->bookRepository->getByCategory($id); // Assuming this method exists and fetches all books for a category
        
        if ($this->isApiRequest()) {
            return Response::json([
                'title' => 'Books in ' . $category['name'],
                'category' => $category,
                'books' => $books
            ]);
        }
        
        $html = View::render('Catalog/CategoryView.php', [
            'category' => $category,
            'books' => $books
        ]);
        
        return Response::view('Categories/View', [
            'title' => $category['name'],
            'category' => $category,
            'books' => $books,
            'html' => $html
        ]);
    }
    
    /**
     * API endpoint for all categories
     */
    public function apiList() {
        $categories = $this->categoryRepository->getAll();
        return new Response($categories, 200);
    }

    /**
     * API endpoint for paginated categories
     */
    public function apiIndex() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 10;

        $offset = ($page - 1) * $perPage;

        $categories = $this->categoryRepository->getPaginated($perPage, $offset);
        $totalCategories = $this->categoryRepository->getTotalCount();

        return new Response([
            'categories' => $categories,
            'pagination' => [
                'current' => $page,
                'perPage' => $perPage,
                'total' => $totalCategories,
                'totalPages' => ceil($totalCategories / $perPage)
            ]
        ], 200);
    }

    private function isApiRequest() {
        // Helper function to check if the request is an API request
        // This can be based on a header, a URL prefix, or other criteria
        // Assuming request object has getUri method
        if ($this->request && method_exists($this->request, 'getUri')) {
            return strpos($this->request->getUri(), '/api/') !== false;
        }
        // Fallback for when $this->request is not set or doesn't have getUri
        if (isset($_SERVER['REQUEST_URI'])) {
             return strpos($_SERVER['REQUEST_URI'], '/api/') !== false;
        }
        return false;
    }
}