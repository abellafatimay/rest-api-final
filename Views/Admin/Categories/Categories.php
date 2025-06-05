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
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>