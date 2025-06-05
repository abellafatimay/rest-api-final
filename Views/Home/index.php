<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; }
        nav { margin-bottom: 2rem; }
        nav ul { list-style: none; display: flex; padding: 0; }
        nav li { margin-right: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <nav>
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/login">Login</a></li>
                <li><a href="/register">Register</a></li>
            </ul>
        </nav>
        
        <h1><?php echo htmlspecialchars($heading); ?></h1>
        
        <div>
            <p>Welcome to the MVC Framework. The following endpoints are available:</p>
            <ul>
                <li><code>GET /</code> - Home page</li>
                <li><code>GET /login</code> - Login page</li>
                <li><code>POST /login</code> - Login submission</li>
                <li><code>GET /register</code> - Registration page</li>
                <li><code>POST /register</code> - Registration submission</li>
                <li><code>GET /users</code> - List all users (authenticated)</li>
                <li><code>GET /users/{id}</code> - Get user by ID (authenticated)</li>
            </ul>
        </div>
    </div>
</body>
</html>