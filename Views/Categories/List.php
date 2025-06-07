<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container mt-4">
    <h1>Book Categories</h1>
    
    <?php if (empty($categories)): ?>
        <div class="alert alert-info">No categories found.</div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($categories as $category): ?>
                <div class="col mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($category['name']); ?></h5>
                            <?php if (!empty($category['description'])): ?>
                                <p class="card-text"><?php echo htmlspecialchars($category['description']); ?></p>
                            <?php endif; ?>
                            <a href="/catalog/category/<?php echo $category['id']; ?>" class="btn btn-primary">View Books</a>
                        </div>
                        <div class="card-footer">
                            <small class="text-muted">
                                <?php echo $category['book_count'] ?? 0; ?> books in this category
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>