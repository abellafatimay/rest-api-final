<?php
include __DIR__ . '/../../layout/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Book Details</h1>
        <div>
            <a href="/admin/books" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left"></i> Back to Books
            </a>
            <a href="/admin/books/<?php echo $book['id']; ?>/edit" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
        </div>
    </div>

    <?php if (isset($_GET['updated']) && $_GET['updated'] === 'true'): ?>
        <div class="alert alert-success">Book updated successfully.</div>
    <?php elseif (isset($_GET['nochanges']) && $_GET['nochanges'] === 'true'): ?>
        <div class="alert alert-info">No changes were made to the book.</div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <?php if (!empty($book['cover_image'])): ?>
                <div class="col-md-3">
                    <img src="<?php echo htmlspecialchars('/uploads/covers/' . basename($book['cover_image'])); ?>" 
                         alt="<?php echo htmlspecialchars($book['title']); ?>" 
                         style="max-width: 200px; height: auto;" class="img-fluid rounded">
                </div>
                <?php else: ?>
                <div class="col-md-3">
                    <p>No cover image available.</p>
                </div>
                <?php endif; ?>
                
                <div class="<?php echo !empty($book['cover_image']) ? 'col-md-9' : 'col-md-12'; ?>">
                    <h2><?php echo htmlspecialchars($book['title']); ?></h2>
                    <p class="text-muted">by <?php echo htmlspecialchars($book['author']); ?></p>
                    
                    <div class="mb-3">
                        <span class="badge <?php echo $book['available'] ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo $book['available'] ? 'Available' : 'Not Available'; ?>
                        </span>
                        <?php if (!empty($book['genre'])): ?>
                        <span class="badge bg-info"><?php echo htmlspecialchars($book['genre']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <?php if (!empty($book['publication_year'])): ?>
                        <p><strong>Publication Year:</strong> <?php echo htmlspecialchars($book['publication_year']); ?></p>
                        <?php endif; ?>
                        
                        <?php if (!empty($book['isbn'])): ?>
                        <p><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Category:</strong> 
                        <?php if (!empty($book['category_name'])): ?>
                            <?= htmlspecialchars($book['category_name']) ?>
                        <?php else: ?>
                            <em>No category assigned</em>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($book['description'])): ?>
                    <h5>Description</h5>
                    <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card bg-light">
        <div class="card-body">
            <h5>Administrative Details</h5>
            <p>
                <strong>Added on:</strong> <?php echo date('F j, Y, g:i a', strtotime($book['created_at'])); ?><br>
                <strong>Last updated:</strong> <?php echo date('F j, Y, g:i a', strtotime($book['updated_at'])); ?>
            </p>
            
            <form action="/admin/books/<?php echo $book['id']; ?>/delete" method="POST" 
                  onsubmit="return confirm('Are you sure you want to delete this book?');">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Delete Book
                </button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>