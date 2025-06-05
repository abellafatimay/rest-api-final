<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Admin Dashboard</h1>
            <a href="/dashboard" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <p>Welcome to the administration area. Manage your site from here.</p>
    
    <!-- Rest of your dashboard content -->
    <div class="row">
        <!-- User Management Card -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    User Management
                </div>
                <div class="card-body">
                    <p>Manage system users, roles and permissions.</p>
                    <p>Total users: <?= $totalUsers ?? 0 ?></p>
                    <a href="/admin/users" class="btn btn-primary">Manage Users</a>
                </div>
            </div>
        </div>
        
        <!-- System Information Card -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    System Information
                </div>
                <div class="card-body">
                    <p>View system statistics and information.</p>
                    <p>PHP Version: <?= phpversion() ?><br>
                    Server Time: <?= date('Y-m-d H:i:s') ?><br>
                    Memory Usage: <?= round(memory_get_usage() / 1024 / 1024, 2) ?> MB</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Book Management Card -->
    <div class="row mt-4">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    Book Management
                </div>
                <div class="card-body">
                    <p>Manage your book catalog and inventory.</p>
                    <p>Total books: <?= $totalBooks ?? 0 ?></p>
                    <a href="/admin/books" class="btn btn-warning">Manage Books</a>
                </div>
            </div>
        </div>
        
        <!-- Optional: Another management card -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    Book Categories
                </div>
                <div class="card-body">
                    <p>Manage book categories and genres.</p>
                    <p>Total categories: <?= $totalCategories ?? 0 ?></p>
                    <a href="/admin/book-categories" class="btn btn-secondary">Manage Categories</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity Section -->
    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            Recent Activity
        </div>
        <div class="card-body">
            <p>This is where you'd display recent system activity logs.</p>
            <p class="text-muted">Feature can be implemented in future updates.</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>