<?php

namespace Controllers\HomeController; // Or your actual namespace for HomeController

use Responses\Response;
use Views\Core\View; // Make sure you use your View class

class HomeController
{
    public function index()
    {
        try {
            // Data to pass to the view
            $data = [
                'title' => 'Welcome to MVC Framework', // This will be used in <title>
                'heading' => 'MVC Framework Homepage' // This will be used in <h1>
            ];
            
            // Render the view file (Views/Home/index.php)
            $html = View::render('Home/index.php', $data);
            
            // Return a new Response object with the HTML content
            return new Response($html, 200, ['Content-Type' => 'text/html']);
        } catch (\Exception $e) {
            error_log('Error in HomeController->index: ' . $e->getMessage());
            return new Response(['error' => 'Internal server error'], 500);
        }
    }
}   