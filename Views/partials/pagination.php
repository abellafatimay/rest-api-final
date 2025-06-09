<?php
if (!isset($pagination) || $pagination['totalPages'] <= 1) return;
?>
<nav aria-label="Pagination">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= $pagination['current'] <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $pagination['current'] - 1 ?>">Previous</a>
        </li>
        <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
            <li class="page-item <?= $i === $pagination['current'] ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $pagination['current'] >= $pagination['totalPages'] ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $pagination['current'] + 1 ?>">Next</a>
        </li>
    </ul>
</nav>
