<?php include __DIR__ . '/../layout/header.php'; ?>

<div class="container mt-4">
    <h1>Book Catalog</h1>
    
    <!-- Search and Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="/catalog" method="GET" class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">Search Books</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?php echo htmlspecialchars($search ?? ''); ?>" 
                           placeholder="Search by title, author, or ISBN...">
                </div>
                <div class="col-md-4">
                    <label for="category" class="form-label">Filter by Category</label>
                    <select class="form-control" id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                <?php echo (isset($currentCategory) && $currentCategory == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </form>
        </div>
    </div>
    
    <?php if (empty($books)): ?>
        <div class="alert alert-info">No books found matching your criteria.</div>
    <?php else: ?>
        <!-- Book Grid -->
        <div class="row">
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