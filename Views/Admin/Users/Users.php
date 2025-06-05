<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>User Management</h1>
            <a href="/admin" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
        <a href="/admin/users/create" class="btn btn-success">Add New User</a>
    </div>
    
    <?php if (isset($message)): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if (empty($users)): ?>
        <div class="alert alert-info">No users found.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
                                <span class="badge bg-danger">Admin</span>
                            <?php else: ?>
                                <span class="badge bg-primary">User</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($user['created_at'] ?? ''); ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="/admin/users/<?php echo $user['id']; ?>" class="btn btn-sm btn-info">View</a>
                                <a href="/admin/users/<?php echo $user['id']; ?>/edit" class="btn btn-sm btn-primary">Edit</a>
                                <form method="POST" action="/admin/users/<?php echo $user['id']; ?>/delete" style="display: inline;">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Add pagination controls -->
        <?php if (isset($pagination) && $pagination['totalPages'] > 1): ?>
        <nav aria-label="User pagination">
            <ul class="pagination justify-content-center">
                <!-- Previous page link -->
                <li class="page-item <?php echo $pagination['current'] <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $pagination['current'] - 1; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                
                <!-- Page numbers -->
                <?php for ($i = max(1, $pagination['current'] - 2); $i <= min($pagination['totalPages'], $pagination['current'] + 2); $i++): ?>
                <li class="page-item <?php echo $i === $pagination['current'] ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
                
                <!-- Next page link -->
                <li class="page-item <?php echo $pagination['current'] >= $pagination['totalPages'] ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $pagination['current'] + 1; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <div class="text-center text-muted mb-4">
            Showing <?php echo ($pagination['current'] - 1) * $pagination['perPage'] + 1; ?> - 
            <?php echo min($pagination['current'] * $pagination['perPage'], $pagination['total']); ?> 
            of <?php echo $pagination['total']; ?> users
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>