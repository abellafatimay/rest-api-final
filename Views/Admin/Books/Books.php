<?php
include __DIR__ . '/../../layout/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Book Management</h1>
            <a href="/admin" class="btn btn-secondary me-2">
                <i class="bi bi-arrow-left"></i> Back to Admin Dashboard
            </a>
        </div>
        <a href="/admin/books/create" class="btn btn-success">Add New Book</a>
    </div>
    
    <?php if (isset($_GET['created'])): ?>
    <div class="alert alert-success">Book created successfully.</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['updated'])): ?>
    <div class="alert alert-success">Book updated successfully.</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">Book deleted successfully.</div>
    <?php endif; ?>
    
    <?php if (empty($books)): ?>
        <div class="alert alert-info">No books found. Add your first book to get started.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Genre</th>
                        <th>Publication Year</th>
                        <th>Available</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['id']); ?></td>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo htmlspecialchars($book['genre'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($book['publication_year'] ?? 'N/A'); ?></td>
                        <td>
                            <?php if ($book['available']): ?>
                                <span class="badge bg-success">Yes</span>
                            <?php else: ?>
                                <span class="badge bg-danger">No</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/admin/books/<?php echo $book['id']; ?>" class="btn btn-sm btn-info">View</a>
                                <a href="/admin/books/<?php echo $book['id']; ?>/edit" class="btn btn-sm btn-primary">Edit</a>
                                <form method="POST" action="/admin/books/<?php echo $book['id']; ?>/delete" style="display: inline;">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination controls -->
        <?php if (isset($pagination) && $pagination['totalPages'] > 1): ?>
        <nav aria-label="Book pagination">
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
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>