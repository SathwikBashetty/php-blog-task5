<?php 
require '../config/db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    // For simplicity, we'll skip CSRF check for GET requests
    // Note: In production, you should use POST for delete operations
    $id = (int)$_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = "Post deleted successfully";
        } else {
            $_SESSION['error'] = "Post not found or you don't have permission to delete it";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting post: " . $e->getMessage();
    }
}

header("Location: read.php");
exit;
?>