<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="container mt-4">
    <h1>Category Management</h1>
    
    <div class="mb-3 d-flex justify-content-between">
        <a href="/admin/" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
        
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
    
    <?php include __DIR__ . '/../../partials/pagination.php'; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>