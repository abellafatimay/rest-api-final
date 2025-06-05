<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="container mt-4">
    <h1>User Details</h1>
    
    <div class="card mb-4">
        <div class="card-header">
            User ID: <?php echo htmlspecialchars($user['id']); ?>
        </div>
        <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($user['name']); ?></h5>
            <p class="card-text">
                <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?><br>
                <strong>Role:</strong> 
                <?php if ($user['role'] === 'admin'): ?>
                    <span class="badge bg-danger">Admin</span>
                <?php else: ?>
                    <span class="badge bg-primary">User</span>
                <?php endif; ?>
                <br>
                <strong>Created:</strong> <?php echo htmlspecialchars($user['created_at'] ?? ''); ?>
            </p>
            
            <div class="mt-3">
                <a href="/admin/users/<?php echo $user['id']; ?>/edit" class="btn btn-primary">Edit</a>
                <a href="/admin/users" class="btn btn-secondary">Back to List</a>
                
                <form method="POST" action="/admin/users/<?php echo $user['id']; ?>/delete" style="display: inline;">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>