<?php
require 'init.php';

$perPage = 5;
$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));

$where = '';
$params = [];
if ($search !== '') {
    $where = "WHERE title LIKE ? OR content LIKE ?";
    $like = '%' . $search . '%';
    $params = [$like, $like];
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM posts $where");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare(
    "SELECT id, title, content, created_at FROM posts $where
     ORDER BY created_at DESC, id DESC LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$posts = $stmt->fetchAll();

function page_url($p, $search) {
    return 'index.php?' . http_build_query(['q' => $search, 'page' => $p]);
}

include 'header.php';
?>
<h1 class="h3 mb-3">All Posts</h1>

<form method="get" action="index.php" class="input-group mb-3">
    <input type="text" name="q" class="form-control" placeholder="Search by title or content"
           value="<?= h($search) ?>">
    <button class="btn btn-primary">Search</button>
    <?php if ($search !== ''): ?>
        <a href="index.php" class="btn btn-outline-secondary">Clear</a>
    <?php endif; ?>
</form>

<?php if ($search !== ''): ?>
    <p class="text-muted"><?= $total ?> result(s) for "<?= h($search) ?>"</p>
<?php endif; ?>

<?php if (!$posts): ?>
    <div class="alert alert-info">No posts found.</div>
<?php endif; ?>

<?php foreach ($posts as $p): ?>
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <h2 class="h4 card-title"><?= h($p['title']) ?></h2>
            <p class="text-muted small mb-2">Posted on <?= h($p['created_at']) ?></p>
            <p class="post-content"><?= h($p['content']) ?></p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a class="btn btn-sm btn-outline-primary" href="edit.php?id=<?= (int)$p['id'] ?>">Edit</a>
                <form method="post" action="delete.php" class="d-inline"
                      onsubmit="return confirm('Delete this post?');">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

<?php if ($totalPages > 1): ?>
    <nav>
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= h(page_url($page - 1, $search)) ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= h(page_url($i, $search)) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= h(page_url($page + 1, $search)) ?>">Next</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<?php include 'footer.php'; ?>