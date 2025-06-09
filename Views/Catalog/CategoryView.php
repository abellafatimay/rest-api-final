<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/catalog">Book Catalog</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($category['name']); ?></li>
        </ol>
    </nav>

    <h1>Books in <?php echo htmlspecialchars($category['name']); ?></h1>
    
    <?php if (!empty($category['description'])): ?>
        <p class="lead"><?php echo htmlspecialchars($category['description']); ?></p>
    <?php endif; ?>
    
    <?php if (empty($books)): ?>
        <div class="alert alert-info">No books found in this category.</div>
    <?php else: ?>
        <div class="row mt-4">
            <?php foreach ($books as $book): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100">
                        <div class="card-img-top p-3 text-center bg-light" style="height: 200px;">
                            <?php if (!empty($book['cover_image'])): ?>
                                <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($book['title']); ?>" 
                                     class="img-fluid h-100" style="object-fit: contain;">
                            <?php else: ?>
                                <i class="bi bi-book" style="font-size: 5rem;"></i>
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                            <p class="card-text">By <?php echo htmlspecialchars($book['author']); ?></p>
                            <div class="mt-auto">
                                <span class="badge <?php echo $book['available'] ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $book['available'] ? 'Available' : 'Not Available'; ?>
                                </span>
                                <a href="/catalog/<?php echo $book['id']; ?>" class="btn btn-sm btn-primary mt-2">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination controls -->
        <?php include __DIR__ . '/../partials/pagination.php'; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>