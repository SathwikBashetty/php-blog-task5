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
    <title>All Posts</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>All Posts</h2>
        <div class="action-buttons">
            <a href="create.php" class="button">+ Create Post</a>
            <a href="../dashboard.php" class="button">Dashboard</a>
            <a href="../auth/logout.php" class="button logout">Logout</a>
        </div>

        <!-- Search & Filter Section -->
        <div class="search-filter-container">
            <form method="GET" action="" class="search-form">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="search-input-group">
                    <input type="text" name="search" placeholder="Search posts..." 
                           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button type="submit" class="search-button">Search</button>
                </div>
                
                <div class="pagination-settings">
                    <span>Show:</span>
                    <select name="per_page" onchange="this.form.submit()">
                        <?php
                        $options = [5, 10, 20, 50];
                        $selectedPerPage = $_GET['per_page'] ?? 5;
                        foreach ($options as $option) {
                            $selected = $selectedPerPage == $option ? 'selected' : '';
                            echo "<option value=\"$option\" $selected>$option per page</option>";
                        }
                        ?>
                    </select>
                </div>
            </form>
        </div>

        <?php
        // Search functionality
        $search = $_GET['search'] ?? '';
        $where = "WHERE user_id = :user_id";
        $params = ['user_id' => $_SESSION['user_id']];
        
        if (!empty($search)) {
            $where .= " AND (title LIKE :search OR content LIKE :search)";
            $params['search'] = "%$search%";
        }

        // Pagination setup
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 5;
        $currentPage = max(1, $_GET['page'] ?? 1);
        $offset = ($currentPage - 1) * $perPage;

        // Count total posts
        $countSql = "SELECT COUNT(*) FROM posts $where";
        $totalPostsStmt = $pdo->prepare($countSql);
        $totalPostsStmt->execute($params);
        $totalPosts = $totalPostsStmt->fetchColumn();
        $totalPages = max(1, ceil($totalPosts / $perPage));

        // Fetch posts with pagination
        $sql = "SELECT * FROM posts $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0): ?>
            <div class="posts-list">
                <?php while ($post = $stmt->fetch()): ?>
                    <div class="post-card">
                        <div class="post-header">
                            <h3><?= htmlspecialchars($post['title']) ?></h3>
                            <small><?= date('M j, Y \a\t h:i a', strtotime($post['created_at'])) ?></small>
                        </div>
                        <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                        <div class="post-actions">
                            <a href="update.php?id=<?= $post['id'] ?>" class="edit-btn">Edit</a>
                            <a href="delete.php?id=<?= $post['id'] ?>" class="delete-btn" onclick="return confirm('Delete this post?')">Delete</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <div class="pagination-container">
                <div class="pagination-info">
                    Showing <?= $offset + 1 ?>-<?= min($offset + $perPage, $totalPosts) ?> of <?= $totalPosts ?> posts
                </div>
                
                <div class="pagination">
                    <?php
                    $queryString = http_build_query([
                        'search' => $search,
                        'per_page' => $perPage,
                        'csrf_token' => $_SESSION['csrf_token']
                    ]);
                    ?>
                    
                    <?php if ($currentPage > 1): ?>
                        <a href="?<?= $queryString ?>&page=1" class="pagination-link first-page">« First</a>
                        <a href="?<?= $queryString ?>&page=<?= $currentPage - 1 ?>" class="pagination-link prev-page">‹ Previous</a>
                    <?php endif; ?>

                    <?php 
                    // Show limited page numbers (max 5 visible)
                    $start = max(1, $currentPage - 2);
                    $end = min($totalPages, $start + 4);
                    
                    for ($i = $start; $i <= $end; $i++): ?>
                        <a href="?<?= $queryString ?>&page=<?= $i ?>" 
                           class="pagination-link <?= $i == $currentPage ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?<?= $queryString ?>&page=<?= $currentPage + 1 ?>" class="pagination-link next-page">Next ›</a>
                        <a href="?<?= $queryString ?>&page=<?= $totalPages ?>" class="pagination-link last-page">Last »</a>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <div class="empty-state">
                <img src="../assets/images/empty.svg" alt="No posts">
                <h3>No posts found<?= !empty($search) ? ' matching your search' : '' ?></h3>
                <?php if (empty($search)): ?>
                    <a href="create.php" class="button">Create Your First Post</a>
                <?php else: ?>
                    <a href="read.php" class="button">Clear Search</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Add loading state during navigation
        document.querySelectorAll('.pagination-link').forEach(link => {
            link.addEventListener('click', () => {
                document.body.innerHTML += '<div class="loading-overlay"><div class="loading-spinner"></div></div>';
            });
        });
    </script>
</body>
</html>