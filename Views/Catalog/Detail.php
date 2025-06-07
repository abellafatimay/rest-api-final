<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/catalog">Book Catalog</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($book['title']); ?></li>
        </ol>
    </nav>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 text-center">
                    <?php if (!empty($book['cover_image'])): ?>
                        <img src="<?php echo htmlspecialchars($book['cover_image']); ?>"
                            alt="<?php echo htmlspecialchars($book['title']); ?>"
                            class="img-fluid rounded" style="max-height: 400px;">
                    <?php else: ?>
                        <div class="bg-light p-5 rounded">
                            <i class="bi bi-book" style="font-size: 8rem;"></i>
                            <p class="mt-3">No cover available</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="col-md-8">
                    <h1><?php echo htmlspecialchars($book['title']); ?></h1>
                    <p class="lead">by <?php echo htmlspecialchars($book['author']); ?></p>
                    
                    <div class="mb-3">
                        <span class="badge <?php echo $book['available'] ? 'bg-success' : 'bg-danger'; ?> p-2">
                            <?php echo $book['available'] ? 'Available' : 'Not Available'; ?>
                        </span>
                        
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <a href="/catalog/category/<?php echo $category['id']; ?>" class="badge bg-info text-decoration-none p-2">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($book['description'])): ?>
                        <h5>Description</h5>
                        <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                    <?php endif; ?>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5>Book Details</h5>
                            <ul class="list-unstyled">
                                <?php if (!empty($book['publication_year'])): ?>
                                    <li><strong>Publication Year:</strong> <?php echo htmlspecialchars($book['publication_year']); ?></li>
                                <?php endif; ?>
                                
                                <?php if (!empty($book['isbn'])): ?>
                                    <li><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?></li>
                                <?php endif; ?>
                                
                                <?php if (!empty($book['genre'])): ?>
                                    <li><strong>Genre:</strong> <?php echo htmlspecialchars($book['genre']); ?></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>