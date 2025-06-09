<ul class="navbar-nav mr-auto">
    <li class="nav-item">
        <a class="nav-link" href="/catalog">Book Catalog</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="/categories">Categories</a>
    </li>
    <?php if (isset($_SESSION['user_id'])): ?>
        <li class="nav-item">
            <a class="nav-link" href="/dashboard">Dashboard</a>
        </li>
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link" href="/admin">Admin</a>
            </li>
        <?php endif; ?>
        <li class="nav-item">
            <a class="nav-link" href="/logout">Logout</a>
        </li>
    <?php else: ?>
        <li class="nav-item">
            <a class="nav-link" href="/login">Login</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/register">Register</a>
        </li>
    <?php endif; ?>
</ul>
