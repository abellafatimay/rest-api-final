<?php

namespace Controllers\HomeController; // Or your actual namespace for HomeController

use Responses\Response;
use Views\Core\View; // Make sure you use your View class

class HomeController
{
    public function index()
    {
        // Data to pass to the view
        $data = [
            'title' => 'My Awesome Homepage', // This will be used in <title>
            'heading' => 'Welcome to My MVC Site!', // This will be used in <h1>
            'name' => 'Valued Visitor' // This will be used in the <p>Hello, ...</p>
        ];

        // Render the view file (Views/Home/index.php)
        $htmlContent = View::render('Home/index.php', $data);

        // Return a new Response object with the HTML content
        return new Response($htmlContent, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}