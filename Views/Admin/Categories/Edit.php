<?php include __DIR__ . '/../../layout/header.php'; ?>

<title><?php echo htmlspecialchars($title ?? 'Edit Category'); ?></title>

<div class="container mt-4">
    <h1><?= htmlspecialchars($title ?? 'Edit Category') ?></h1>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($message) && $message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form action="/admin/categories/<?= $category['id'] ?>" method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required
                   value="<?= htmlspecialchars($category['name']) ?>">
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3"
                      ><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
            <label for="slug" class="form-label">Slug</label>
            <input type="text" class="form-control" id="slug" name="slug" required
                   value="<?= htmlspecialchars($category['slug'] ?? '') ?>">
        </div>

        <button type="submit" class="btn btn-primary">Update Category</button>
        <a href="/admin/categories" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>