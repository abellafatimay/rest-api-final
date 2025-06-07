<?php

namespace Controllers\BookController;

use Models\BookRepository\BookRepository;
use Models\BookCategoryRepository\BookCategoryRepository;
use Responses\Response;
use Views\Core\View;

class BookController {
    private $bookRepository;
    private $categoryRepository;
    
    public function __construct(BookRepository $bookRepository, BookCategoryRepository $categoryRepository) {
        $this->bookRepository = $bookRepository;
        $this->categoryRepository = $categoryRepository;
    }
    
    public function index() {
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 10;
        
        // Calculate offset
        $offset = ($page - 1) * $perPage;
        
        // Get books
        $books = $this->bookRepository->getPaginated($perPage, $offset);
        $totalBooks = $this->bookRepository->getTotalCount();
        $totalPages = ceil($totalBooks / $perPage);
        
        $html = View::render('Admin/Books/Books.php', [
            'title' => 'Book Management',
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
    
    public function create() {
        $categories = $this->categoryRepository->getAll();
        $html = View::render('Admin/Books/Create.php', [
            'title' => 'Add New Book',
            'categories' => $categories
        ]);
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    public function store() {
        // Get data from form
        $data = [
            'title' => $_POST['title'] ?? '',
            'author' => $_POST['author'] ?? '',
            'description' => $_POST['description'] ?? '',
            'publication_year' => (int)($_POST['publication_year'] ?? 0),
            'genre' => $_POST['genre'] ?? '',
            'isbn' => $_POST['isbn'] ?? '',
            'available' => isset($_POST['available']) ? 1 : 0
        ];
        
        // Handle cover image upload if present
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/covers/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $filename = uniqid() . '_' . basename($_FILES['cover_image']['name']);
            $uploadFile = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadFile)) {
                // Store the web-accessible path without public
                $data['cover_image'] = '/uploads/covers/' . $filename;
                error_log("File uploaded successfully, path stored: " . $data['cover_image']);
            }
        } else if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            error_log("File upload error: " . $_FILES['cover_image']['error']); // DEBUG
        }
        
        // Create book in database
        $bookId = $this->bookRepository->create($data);

        if ($bookId && !empty($_POST['category_id'])) {
            $this->bookRepository->updateCategory($bookId, $_POST['category_id']);
        }
        
        // Handle categories if present
        if (isset($_POST['categories']) && is_array($_POST['categories'])) {
            $this->bookRepository->updateCategories($bookId, $_POST['categories']);
        }
        
        // Redirect to book list
        return new Response(
            ['redirect' => '/admin/books?created=true'],
            303,
            ['Location' => '/admin/books?created=true']
        );
    }
    
    public function show($id) {
        // Get book details
        $book = $this->bookRepository->getById($id);
        
        // If book not found, redirect to books list
        if (!$book) {
            return new Response(
                ['redirect' => '/admin/books?error=not-found'],
                303,
                ['Location' => '/admin/books?error=not-found']
            );
        }
        
        // Render book details view
        $html = View::render('Admin/Books/Show.php', [
            'title' => 'Book Details',
            'book' => $book
        ]);
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    public function edit($id) {
        $book = $this->bookRepository->getById($id);
        $categories = $this->categoryRepository->getAll();

        if (!$book) {
            return new Response('Book not found', 404);
        }
        
        $html = \Views\Core\View::render('Admin/Books/Edit.php', [
            'title' => 'Edit Book',
            'book' => $book,
            'categories' => $categories // Make sure this is being passed
        ]);
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    public function update($id) {
        // Book data
        $bookData = [
            'title' => $_POST['title'],
            'author' => $_POST['author'],
            'description' => $_POST['description'] ?? null,
            'publication_year' => (int)($_POST['publication_year'] ?? 0),
            'genre' => $_POST['genre'] ?? null,
            'isbn' => $_POST['isbn'] ?? null,
            'available' => isset($_POST['available']) ? 1 : 0
        ];

        // Update the book first
        $success = $this->bookRepository->update($id, $bookData);
        
        // Then update the book-category relationship
        if ($success && isset($_POST['category_id']) && !empty($_POST['category_id'])) {
            $this->bookRepository->updateCategory($id, $_POST['category_id']);
        }
        
        if ($success) {
            return new Response(
                ['redirect' => '/admin/books/' . $id . '?updated=true'],
                303,
                ['Location' => '/admin/books/' . $id . '?updated=true']
            );
        }

        // Handle failure
        $html = View::render('Admin/Books/Edit.php', [
            'title' => 'Edit Book',
            'book' => array_merge($book, $data),
            'error' => 'Failed to update book.'
        ]);
        return new Response($html, 422, ['Content-Type' => 'text/html']);
    }
    
    public function delete($id) {
        // Get the book first to check if it exists and to get the image path
        $book = $this->bookRepository->getById($id);
        
        if (!$book) {
            return new Response(
                ['redirect' => '/admin/books?error=not-found'],
                303,
                ['Location' => '/admin/books?error=not-found']
            );
        }

        // Delete the associated image file if it exists
        if (!empty($book['cover_image'])) {
            $imagePath = __DIR__ . '/../../uploads/covers/' . basename($book['cover_image']);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Delete the book record
        $success = $this->bookRepository->delete($id);

        if ($success) {
            return new Response(
                ['redirect' => '/admin/books?deleted=true'],
                303,
                ['Location' => '/admin/books?deleted=true']
            );
        }

        // Handle failure
        return new Response(
            ['redirect' => '/admin/books/' . $id . '?error=delete-failed'],
            303,
            ['Location' => '/admin/books/' . $id . '?error=delete-failed']
        );
    }
    
    public function manageCategories() {
        $books = $this->bookRepository->getAll();
        $categories = $this->categoryRepository->getAll();
        
        $html = View::render('Admin/Categories/Categories.php', [
            'title' => 'Manage Book Categories',
            'books' => $books,
            'categories' => $categories,
            'bookRepository' => $this->bookRepository
        ]);
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }

    public function updateCategories($bookId) {
        $categoryIds = $_POST['categories'] ?? [];
        $success = $this->bookRepository->updateCategories($bookId, $categoryIds);
        
        if ($success) {
            return new Response(
                ['redirect' => '/admin/books/' . $bookId . '?categories_updated=true'],
                303,
                ['Location' => '/admin/books/' . $bookId . '?categories_updated=true']
            );
        }
        
        return new Response(
            ['error' => 'Failed to update categories'],
            422,
            ['Content-Type' => 'application/json']
        );
    }

    public function getCategories($bookId) {
        $categories = $this->bookRepository->getCategories($bookId);
        return new Response($categories, 200, ['Content-Type' => 'application/json']);
    }
}