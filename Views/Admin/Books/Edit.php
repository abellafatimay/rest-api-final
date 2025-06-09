<?php 
include __DIR__ . '/../../layout/header.php';
?>

<div class="container mt-4">
    <h1><?php echo htmlspecialchars($title ?? 'Edit Book'); ?></h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="/admin/books/<?php echo htmlspecialchars($book['id']); ?>" enctype="multipart/form-data">

        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($book['title'] ?? ''); ?>" required>
        </div>

        <div class="mb-3">
            <label for="author" class="form-label">Author</label>
            <input type="text" class="form-control" id="author" name="author" value="<?php echo htmlspecialchars($book['author'] ?? ''); ?>" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($book['description'] ?? ''); ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="publication_year" class="form-label">Publication Year</label>
                    <input type="number" class="form-control" id="publication_year" name="publication_year" min="1800" max="<?php echo date('Y'); ?>" value="<?php echo htmlspecialchars($book['publication_year'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="category" class="form-label">Book Category</label>
                    <select class="form-control" id="category" name="category">
                        <option value="">Select a category...</option>
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category['id']) ?>" 
                                    <?= ((isset($book['category_id_alias']) && $book['category_id_alias'] == $category['id']) ? 'selected' : '') ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">No categories available</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="isbn" class="form-label">ISBN</label>
            <input type="text" class="form-control" id="isbn" name="isbn" value="<?php echo htmlspecialchars($book['isbn'] ?? ''); ?>">
        </div>
        
        <div class="mb-3">
            <label for="cover_image" class="form-label">Cover Image</label>
            <input type="file" class="form-control" id="cover_image" name="cover_image">
            <?php if (!empty($book['cover_image'])): ?>
                <div class="mt-2">
                    <small>Current image:</small><br>
                    <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" 
                         alt="<?php echo htmlspecialchars($book['title'] ?? 'Cover'); ?>" 
                         style="max-width: 100px; max-height: 150px;">
                </div>
            <?php endif; ?>
        </div>


        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="available" name="available" value="1" <?php echo (isset($book['available']) && $book['available'] == 1) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="available">
                Available
            </label>
        </div>

        <button type="submit" class="btn btn-primary">Update Book</button>
        <a href="/admin/books/<?php echo htmlspecialchars($book['id'] ?? ''); ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
