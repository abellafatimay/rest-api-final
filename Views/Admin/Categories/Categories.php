<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="container mt-4">
    <h1>Category Management</h1>
    
    <?php if (isset($_GET['created'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Category created successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="mb-3">
        <a href="/admin/categories/create" class="btn btn-primary">Add New Category</a>
    </div>
    
    <?php if (empty($categories)): ?>
        <div class="alert alert-info">No categories found. Create your first category!</div>
    <?php else: ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
            <tr>
                <td><?= htmlspecialchars($category['id']) ?></td>
                <td><?= htmlspecialchars($category['name']) ?></td>
                <td><?= htmlspecialchars($category['description']) ?></td>
                <td>
                    <a href="/admin/categories/<?= $category['id'] ?>/edit" class="btn btn-sm btn-primary">Edit</a>
                    <form method="POST" action="/admin/categories/<?= $category['id'] ?>/delete" class="d-inline">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- Pagination controls -->
    <?php if (isset($pagination) && $pagination['totalPages'] > 1): ?>
    <nav aria-label="Category pagination">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo $pagination['current'] <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $pagination['current'] - 1; ?>">Previous</a>
            </li>
            
            <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
            <li class="page-item <?php echo $i === $pagination['current'] ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
            <?php endfor; ?>
            
            <li class="page-item <?php echo $pagination['current'] >= $pagination['totalPages'] ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $pagination['current'] + 1; ?>">Next</a>
            </li>
        </ul>
    </nav>
    
    <div class="text-center text-muted mb-4">
        Showing <?php echo ($pagination['current'] - 1) * $pagination['perPage'] + 1; ?> - 
        <?php echo min($pagination['current'] * $pagination['perPage'], $pagination['total']); ?> 
        of <?php echo $pagination['total']; ?> categories
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>