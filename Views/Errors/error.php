<?php
include __DIR__ . '/../layout/header.php';
?>
<div class="container mt-5">
    <div class="alert alert-danger" role="alert">
        <h2>Error</h2>
        <p><?= isset($message) ? htmlspecialchars($message) : 'An unexpected error occurred.' ?></p>
        <?php if (isset($code)): ?>
            <hr>
            <small>Error code: <?= htmlspecialchars($code) ?></small>
        <?php endif; ?>
    </div>
    <a href="/" class="btn btn-primary">Back to Home</a>
</div>
<?php
include __DIR__ . '/../layout/footer.php';
