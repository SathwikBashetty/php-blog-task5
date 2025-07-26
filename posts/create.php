<?php 
require '../config/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Post</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Create New Post</h2>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="text" name="title" placeholder="Title" required>
            <textarea name="content" placeholder="Content" required></textarea>
            <button type="submit">Submit</button>
            <a href="read.php" class="button">Cancel</a>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'])) {
                echo "<p class='error'>Invalid security token. Please try again.</p>";
                exit;
            }

            $title = trim($_POST['title']);
            $content = trim($_POST['content']);

            if (empty($title) || empty($content)) {
                echo "<p class='error'>Please fill all fields</p>";
                exit;
            }

            try {
                $stmt = $pdo->prepare("INSERT INTO posts (title, content, user_id) VALUES (?, ?, ?)");
                $stmt->execute([$title, $content, $_SESSION['user_id']]);
                echo "<p class='success'>Post created! <a href='read.php'>View all</a></p>";
            } catch (PDOException $e) {
                echo "<p class='error'>Error creating post: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
        ?>
    </div>
</body>
</html>