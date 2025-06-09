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

        if ($this->isApiRequest()) {
            return Response::json([
                'totalUsers' => $totalUsers,
                'systemInfo' => $systemInfo
            ]);
        }
        // Render the dashboard view as HTML
        $html = View::render('Admin/Dashboard/Dashboard.php', [
            'title' => 'Admin Dashboard',
            'totalUsers' => $totalUsers,
            'systemInfo' => $systemInfo
        ]);
        return new Response($html, 200, ['Content-Type' => 'text/html']);
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
        
        $dataToPass = [
            'title' => 'Admin Dashboard',
            'totalUsers' => $totalUsers,
            'totalBooks' => $totalBooks,
            'totalCategories' => $totalCategories,
            'phpVersion' => phpversion(),
            'serverTime' => date('Y-m-d H:i:s'),
            'memoryUsage' => round(memory_get_usage() / 1024 / 1024, 2)
        ];

        if ($this->isApiRequest()) {
            return Response::json($dataToPass);
        }
        
        $html = View::render('Admin/Dashboard/Dashboard.php', $dataToPass);
        
        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }
    
    /**
     * API endpoint for admin dashboard data
     */
    public function apiDashboard() {
        $totalUsers = $this->userRepository->countAll();
        $systemInfo = [
            'phpVersion' => phpversion(),
            'serverTime' => date('Y-m-d H:i:s'),
            'memoryUsage' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB'
        ];

        return Response::json([ // Changed from new Response to Response::json
            'totalUsers' => $totalUsers,
            'systemInfo' => $systemInfo
        ]);
    }

    private function isApiRequest() {
        // Helper function to check if the request is an API request
        // This can be based on a header, a URL prefix, or other criteria
        // Assuming request object has getUri method, or fallback to $_SERVER['REQUEST_URI']
        // A more robust solution might involve checking Accept headers or a dedicated request property
        if (isset($_SERVER['REQUEST_URI'])) {
            return strpos($_SERVER['REQUEST_URI'], '/api/') !== false;
        }
        return false;
    }
}