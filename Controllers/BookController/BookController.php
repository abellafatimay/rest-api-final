<?php
namespace Controllers\BookController;

use Models\BookRepository\BookRepository;
use Models\BookCategoryRepository\BookCategoryRepository;
use Responses\Response;
use Views\Core\View;
use Requests\Request\Request;

class BookController {
    protected $bookRepository;
    protected $bookCategoryRepository;
    protected $categoryRepository;
    protected $request;

    public function __construct($bookRepository, $bookCategoryRepository, Request $request, $categoryRepository = null) {
        $this->bookRepository = $bookRepository;
        $this->bookCategoryRepository = $bookCategoryRepository;
        $this->categoryRepository = $categoryRepository;
        $this->request = $request;
    }
    
    public function index() {
        $page = $this->request->get('page', 1);
        $perPage = 10;
        
        $books = $this->bookRepository->getPaginated($perPage, ($page - 1) * $perPage);
        $totalBooks = $this->bookRepository->getTotalCount();
        
        if ($this->isApiRequest()) {
            return Response::json([
                'books' => $books,
                'pagination' => [
                    'current' => (int)$page,
                    'perPage' => $perPage,
                    'total' => $totalBooks,
                    'totalPages' => ceil($totalBooks / $perPage)
                ]
            ]);
        }

        return Response::view('Admin/Books/Books', [
            'books' => $books,
            'pagination' => [
                'current' => (int)$page,
                'perPage' => $perPage,
                'total' => $totalBooks,
                'totalPages' => ceil($totalBooks / $perPage)
            ]
        ]);
    }
    
    public function create() {
        // Only render the form, do NOT create a book here!
        $categories = $this->categoryRepository->getAll();
        
        if ($this->isApiRequest()) {
            return Response::json([
                'title' => 'Add New Book',
                'categories' => $categories,
                'book' => [],
                'error' => null
            ]);
        }
        // Remove session dependency
        return Response::view('Admin/Books/Create', [
            'title' => 'Add New Book',
            'categories' => $categories,
            'book' => [],
            // Use empty error by default, not session
            'error' => null
        ]);
    }

    public function store() {
        // Centralized validation
        $postData = $this->request->post();
        $required = ['title', 'author', 'publication_year'];
        $validation = \Services\Validation::requireFields($postData, $required);
        if (!$validation[0]) {
            $errorMsg = implode(' ', $validation[1]);
            if ($this->isApiRequest()) {
                return Response::json(['error' => $errorMsg, 'book' => $postData], 422);
            }
            return Response::view('Admin/Books/Create', [
                'error' => $errorMsg,
                'book' => $postData,
                'categories' => $this->categoryRepository->getAll()
            ], 422);
        }
        if (!\Services\Validation::length($postData['title'], 2, 255)) {
            if ($this->isApiRequest()) {
                return Response::json(['error' => 'Title must be between 2 and 255 characters.', 'book' => $postData], 422);
            }
            return Response::view('Admin/Books/Create', [
                'error' => 'Title must be between 2 and 255 characters.',
                'book' => $postData,
                'categories' => $this->categoryRepository->getAll()
            ], 422);
        }
        if (!\Services\Validation::integer($postData['publication_year'])) {
            if ($this->isApiRequest()) {
                return Response::json(['error' => 'Publication year must be an integer.', 'book' => $postData], 422);
            }
            return Response::view('Admin/Books/Create', [
                'error' => 'Publication year must be an integer.',
                'book' => $postData,
                'categories' => $this->categoryRepository->getAll()
            ], 422);
        }
        
        // Process uploaded image if present
        $coverImage = null;
        if (!empty($this->request->file('cover_image')['tmp_name'])) {
            $coverImage = $this->processUploadedImage();
            if (is_string($coverImage) && strpos($coverImage, 'Error:') === 0) {
                if ($this->isApiRequest()) {
                    return Response::json(['error' => $coverImage, 'book' => $this->request->post()], 422);
                }
                return Response::view('Admin/Books/Create', [
                    'error' => $coverImage,
                    'book' => $this->request->post(),
                    'categories' => $this->categoryRepository->getAll()
                ], 422);
            }
        }
        
        // Create book data
        $data = [
            'title' => $this->request->post('title'),
            'author' => $this->request->post('author'),
            'description' => $this->request->post('description'),
            'publication_year' => (int)$this->request->post('publication_year', 0),
            'isbn' => $this->request->post('isbn'),
            'available' => $this->request->post('available') ? 1 : 0,
            'cover_image' => $coverImage
        ];
        
        $bookId = $this->bookRepository->create($data);
        
        if ($bookId === 'DUPLICATE_TITLE') {
            if ($this->isApiRequest()) {
                return Response::json(['error' => 'A book with this title already exists', 'book' => $this->request->post()], 422);
            }
            return Response::view('Admin/Books/Create', [
                'error' => 'A book with this title already exists',
                'book' => $this->request->post(),
                'categories' => $this->categoryRepository->getAll()
            ], 422);
        }
        
        if ($bookId && $this->request->post('category')) {
            $this->bookRepository->updateCategories($bookId, [$this->request->post('category')]);
        }
        
        if ($this->isApiRequest()) {
            return Response::json(['message' => 'Book created successfully.', 'book_id' => $bookId], 201);
        }
        return Response::redirect('/admin/books')
            ->withFlash('success', 'Book created successfully.');
    }
    
    public function show($id) {
        $book = $this->bookRepository->getById($id);
        if (!$book) {
            if ($this->isApiRequest()) {
                return Response::json(['error' => 'Book not found'], 404);
            }
            return Response::error('Book not found', 404);
        }
        if ($this->isApiRequest()) {
            return Response::json(['book' => $book]);
        }
        return Response::view('Admin/Books/Show', ['book' => $book]);
    }

    public function edit($id) {
        $book = $this->bookRepository->getById($id);
        $categories = $this->categoryRepository->getAll();

        if (!$book) {
            if ($this->isApiRequest()) {
                return Response::json(['error' => 'Book not found'], 404);
            }
            return Response::error('Book not found', 404);
        }

        if ($this->isApiRequest()) {
            return Response::json([
                'title' => 'Edit Book',
                'book' => $book,
                'categories' => $categories
            ]);
        }
        return Response::view('Admin/Books/Edit', [
            'title' => 'Edit Book',
            'book' => $book,
            'categories' => $categories
        ]);
    }

    public function update($id) {
        $bookData = [
            'title' => $this->request->post('title'),
            'author' => $this->request->post('author'),
            'description' => $this->request->post('description'),
            'publication_year' => (int)$this->request->post('publication_year', 0),
            'isbn' => $this->request->post('isbn'),
            'available' => $this->request->post('available') ? 1 : 0
        ];

        // Check for duplicate title (for a different book)
        if (!empty($bookData['title'])) {
            $existingBookByTitle = $this->bookRepository->findByTitle($bookData['title']);
            if ($existingBookByTitle && $existingBookByTitle['id'] != $id) {
                $categories = $this->categoryRepository->getAll();
                $book = $this->bookRepository->getById($id);
                if ($this->isApiRequest()) {
                    return Response::json(['error' => 'Another book with this title already exists.', 'book' => array_merge($book, $bookData)], 422);
                }
                return Response::view('Admin/Books/Edit', [
                    'title' => 'Edit Book',
                    'book' => array_merge($book, $bookData),
                    'categories' => $categories,
                    'error' => 'Another book with this title already exists.'
                ], 422);
            }
        }

        // Check for duplicate ISBN (for a different book)
        if (!empty($bookData['isbn'])) {
            $existingBookByIsbn = $this->bookRepository->findByIsbn($bookData['isbn']);
            if ($existingBookByIsbn && $existingBookByIsbn['id'] != $id) {
                $categories = $this->categoryRepository->getAll();
                $book = $this->bookRepository->getById($id);
                if ($this->isApiRequest()) {
                    return Response::json(['error' => 'Another book with this ISBN already exists.', 'book' => array_merge($book, $bookData)], 422);
                }
                return Response::view('Admin/Books/Edit', [
                    'title' => 'Edit Book',
                    'book' => array_merge($book, $bookData),
                    'categories' => $categories,
                    'error' => 'Another book with this ISBN already exists.'
                ], 422);
            }
        }

        $success = $this->bookRepository->update($id, $bookData);

        if ($success && $this->request->post('category_id') && !empty($this->request->post('category_id'))) {
            $this->bookRepository->updateCategories($id, [$this->request->post('category_id')]);
        }

        if ($success) {
            if ($this->isApiRequest()) {
                return Response::json(['message' => 'Book updated successfully.']);
            }
            return Response::redirect('/admin/books/' . $id)
                ->withFlash('success', 'Book updated successfully.');
        }

        $book = $this->bookRepository->getById($id);
        if ($this->isApiRequest()) {
            return Response::json(['error' => 'Failed to update book.', 'book' => array_merge($book, $bookData)], 422);
        }
        return Response::view('Admin/Books/Edit', [
            'title' => 'Edit Book',
            'book' => array_merge($book, $bookData),
            'categories' => $this->categoryRepository->getAll(),
            'error' => 'Failed to update book.'
        ], 422);
    }
    
    public function delete($id) {
        $book = $this->bookRepository->getById($id);

        if (!$book) {
            if ($this->isApiRequest()) {
                return Response::json(['error' => 'Book not found.'], 404);
            }
            return Response::redirect('/admin/books')
                ->withFlash('error', 'Book not found.');
        }

        if (!empty($book['cover_image'])) {
            $imagePath = __DIR__ . '/../../uploads/covers/' . basename($book['cover_image']);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $success = $this->bookRepository->delete($id);

        if ($success) {
            if ($this->isApiRequest()) {
                return Response::json(['message' => 'Book deleted successfully.'], 200);
            }
            return Response::redirect('/admin/books')
                ->withFlash('success', 'Book deleted successfully.');
        }

        if ($this->isApiRequest()) {
            return Response::json(['error' => 'Failed to delete the book.'], 500);
        }
        return Response::redirect('/admin/books/' . $id)
            ->withFlash('error', 'Failed to delete the book.');
    }
    
    public function manageCategories() {
        $books = $this->bookRepository->getAll();
        $categories = $this->categoryRepository->getAll();
        
        if ($this->isApiRequest()) {
            return Response::json([
                'title' => 'Manage Book Categories',
                'books' => $books,
                'categories' => $categories
            ]);
        }
        $html = View::render('Admin/Categories/Categories.php', [
            'title' => 'Manage Book Categories',
            'books' => $books,
            'categories' => $categories,
            'bookRepository' => $this->bookRepository
        ]);
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    public function updateCategories($bookId) {
        $categoryIds = $this->request->post('categories', []);
        $success = $this->bookRepository->updateCategories($bookId, $categoryIds);

        if ($success) {
            if ($this->isApiRequest()) {
                return Response::json(['message' => 'Categories updated successfully.']);
            }
            return Response::redirect('/admin/books/' . $bookId)
                ->withFlash('success', 'Categories updated successfully.');
        }

        return Response::json(['error' => 'Failed to update categories'], 422);
    }

    public function getCategories($bookId) {
        $categories = $this->bookRepository->getCategories($bookId);
        return Response::json($categories);
    }

    /**
     * Process uploaded book cover image
     * 
     * @return string Path to saved image or error message
     */
    protected function processUploadedImage() {
        $file = $this->request->file('cover_image');
        // Check if upload directory exists, create if not
        $uploadDir = __DIR__ . '/../../uploads/covers/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Basic validation
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'Error: Failed to upload file. Error code: ' . $file['error'];
        }

        if ($file['size'] > 5000000) { // 5MB limit
            return 'Error: File too large (max 5MB)';
        }

        // Check file type
        $fileType = mime_content_type($file['tmp_name']);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($fileType, $allowedTypes)) {
            return 'Error: Invalid file type. Allowed types: JPG, PNG, GIF, WEBP';
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = uniqid() . '_' . time() . '.' . $extension;
        $targetPath = $uploadDir . $newFilename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Return relative path for storage in database
            return '/uploads/covers/' . $newFilename;
        } else {
            return 'Error: Failed to save uploaded file';
        }
    }

    protected function isApiRequest() {
        // Helper function to check if the request is an API request
        // This can be based on a header, a URL prefix, or other criteria
        return strpos($this->request->getUri(), '/api/') !== false;
    }

    /**
     * API endpoint for all books (already present for /api/books)
     */
    public function apiIndex() {
        $page = $this->request->get('page', 1);
        $perPage = 10;

        $books = $this->bookRepository->getPaginated($perPage, ($page - 1) * $perPage);
        $totalBooks = $this->bookRepository->getTotalCount();

        return new Response([
            'books' => $books,
            'pagination' => [
                'current' => (int)$page,
                'perPage' => $perPage,
                'total' => $totalBooks,
                'totalPages' => ceil($totalBooks / $perPage)
            ]
        ], 200);
    }
}