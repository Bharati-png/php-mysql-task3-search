<?php
require 'init.php';
require_login();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
        $error = 'Title and content are required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
        $stmt->execute([$title, $content]);
        header('Location: index.php');
        exit;
    }
}
include 'header.php';
?>
<h1 class="h3 mb-3">New Post</h1>
<?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>

<form method="post" class="card card-body shadow-sm">
    <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" value="<?= h($_POST['title'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Content</label>
        <textarea name="content" rows="7" class="form-control"><?= h($_POST['content'] ?? '') ?></textarea>
    </div>
    <div>
        <button class="btn btn-primary">Publish</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php include 'footer.php'; ?>