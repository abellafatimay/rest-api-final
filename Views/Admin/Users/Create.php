<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="container mt-4">
    <h1>Create New User</h1>
    
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
    
    <form action="/admin/users" method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" 
                   value="<?php echo htmlspecialchars($data['name'] ?? ''); ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" 
                   value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        
        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select class="form-control" id="role" name="role">
                <option value="user" <?php echo (isset($data['role']) && $data['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                <option value="admin" <?php echo (isset($data['role']) && $data['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Create User</button>
        <a href="/admin/users" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>