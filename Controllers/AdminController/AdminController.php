<?php

namespace Controllers\AdminController;

use Models\UserRepository\UserRepository;
use Responses\Response;
use Views\Core\View;

class AdminController {
    protected $userRepository;
    protected $bookRepository;
    protected $bookCategoryRepository;

    public function __construct(
        UserRepository $userRepository, 
        $bookRepository = null,
        $bookCategoryRepository = null
    ) {
        $this->userRepository = $userRepository;
        $this->bookRepository = $bookRepository;
        $this->bookCategoryRepository = $bookCategoryRepository;
    }

    public function dashboard() {
        // Get total user count
        $totalUsers = $this->userRepository->countAll();
        
        // Get system info
        $systemInfo = [
            'phpVersion' => phpversion(),
            'serverTime' => date('Y-m-d H:i:s'),
            'memoryUsage' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB'
        ];

        // Return a Response object instead of an array
        return new Response([
            'view' => 'Admin/Dashboard/Dashboard',
            'data' => [
                'title' => 'Admin Dashboard',
                'totalUsers' => $totalUsers,
                'systemInfo' => $systemInfo
            ]
        ]);
    }
    
    public function index() {
        // Get user count
        $totalUsers = $this->userRepository->getTotalCount();
        
        // Get book count if repository exists and has the method
        $totalBooks = ($this->bookRepository && method_exists($this->bookRepository, 'getTotalCount')) 
            ? $this->bookRepository->getTotalCount() 
            : 0;
        
        // Get category count if repository exists and has the method
        $totalCategories = ($this->bookCategoryRepository && method_exists($this->bookCategoryRepository, 'getTotalCount'))
            ? $this->bookCategoryRepository->getTotalCount()
            : 0;
        
        $html = View::render('Admin/Dashboard/Dashboard.php', [
            'title' => 'Admin Dashboard',
            'totalUsers' => $totalUsers,
            'totalBooks' => $totalBooks,
            'totalCategories' => $totalCategories,
            'phpVersion' => phpversion(),
            'serverTime' => date('Y-m-d H:i:s'),
            'memoryUsage' => round(memory_get_usage() / 1024 / 1024, 2)
        ]);
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }
    
}