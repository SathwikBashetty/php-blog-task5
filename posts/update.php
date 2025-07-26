<?php 
require '../config/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

$id = $_GET['id'] ?? 0;

// Verify post exists and belongs to user
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$post = $stmt->fetch();

if (!$post) {
    $_SESSION['error'] = "Post not found or you don't have permission to edit it";
    header("Location: read.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error'] = "Invalid security token. Please try again.";
        header("Location: update.php?id=$id");
        exit();
    }

    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (empty($title) || empty($content)) {
        $_SESSION['error'] = "Please fill all fields";
        header("Location: update.php?id=$id");
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $content, $id, $_SESSION['user_id']]);
        $_SESSION['success'] = "Post updated successfully!";
        header("Location: read.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating post: " . $e->getMessage();
        header("Location: update.php?id=$id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="edit-container">
        <div class="edit-header">
            <h1>Edit Post</h1>
            <a href="read.php" class="button cancel-btn">← Back to Posts</a>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <form method="POST" class="edit-form">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="content">Content</label>
                <textarea id="content" name="content" rows="10" required><?= htmlspecialchars($post['content']) ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="button primary-btn">Update Post</button>
                <a href="read.php" class="button cancel-btn">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>