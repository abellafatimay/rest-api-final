<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Dashboard'); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            color: #333;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 5px;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .welcome-message {
            font-size: 24px;
            color: #4CAF50;
        }
        .dashboard-content {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            grid-gap: 20px;
        }
        .dashboard-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .card-title {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        .nav-menu {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 0 0 20px 0;
        }
        .nav-menu li {
            margin-right: 15px;
        }
        .nav-menu a {
            text-decoration: none;
            color: #333;
            padding: 5px;
        }
        .nav-menu a:hover {
            color: #4CAF50;
        }
        .logout-button {
            background: #f44336;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .nav-tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .nav-tabs .nav-item {
            margin-right: 10px;
        }
        .nav-tabs .nav-link {
            padding: 10px 15px;
            border: 1px solid transparent;
            border-radius: 4px 4px 0 0;
            background: #f8f8f8;
            color: #333;
            text-decoration: none;
        }
        .nav-tabs .nav-link.active {
            background: white;
            border-color: #ddd #ddd #fff;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="welcome-message">Welcome to Your Dashboard</div>
            <a href="/logout" class="logout-button">Logout</a>
        </div>
        
        <!-- Navigation tabs -->
        <div class="nav nav-tabs mb-4">
            <a class="nav-item nav-link active" href="/dashboard">Dashboard</a>
            <a class="nav-item nav-link" href="/profile">My Profile</a>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a class="nav-item nav-link" href="/admin">Admin Panel</a>
            <?php endif; ?>
        </div>
        
        <div class="dashboard-content">
            <div class="dashboard-card">
                <h3 class="card-title">Account Information</h3>
                <p>User ID: <?php echo htmlspecialchars($user_id ?? 'Unknown'); ?></p>
                <?php if (isset($user_data)): ?>
                    <p>Name: <?php echo htmlspecialchars($user_data['name'] ?? 'Unknown'); ?></p>
                    <p>Email: <?php echo htmlspecialchars($user_data['email'] ?? 'Unknown'); ?></p>
                    <p>Member Since: <?php echo htmlspecialchars($user_data['created_at'] ?? 'Unknown'); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="dashboard-card">
                <h3 class="card-title">Recent Activity</h3>
                <p>Your recent activity will be displayed here.</p>
                <!-- This would typically be populated with data from your database -->
                <ul>
                    <li>You logged in on <?php echo date('Y-m-d H:i:s'); ?></li>

                </ul>
            </div>
            
            <div class="dashboard-card">
                <h3 class="card-title">Quick Actions</h3>
                <p>Common actions you can take:</p>
                <ul>
                    <li><a href="/profile">Edit Profile</a></li>
                    <li><a href="/profile">Change Password</a></li>
                    
                </ul>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/../layout/footer.php'; ?>
</body>
</html>