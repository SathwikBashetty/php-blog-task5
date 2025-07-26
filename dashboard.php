<?php 
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - My Blog</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="user-greeting">
                <div class="avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div>
                    <h1>Welcome, <?= htmlspecialchars($_SESSION['user']) ?>!</h1>
                    <p class="welcome-message">Manage your content and settings</p>
                </div>
            </div>
            <nav class="dashboard-nav">
                <a href="posts/read.php" class="nav-button">
                    <i class="fas fa-list"></i> Manage Posts
                </a>
                <a href="posts/create.php" class="nav-button">
                    <i class="fas fa-plus"></i> Create Post
                </a>
                <a href="auth/logout.php" class="nav-button logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </header>

        <main class="dashboard-content">
            <section class="quick-actions">
                <div class="section-header">
                    <h2>Quick Actions</h2>
                    <p>Get started quickly with these options</p>
                </div>
                <div class="action-grid">
                    <a href="posts/create.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                        <h3>New Post</h3>
                        <p>Create a fresh blog post</p>
                    </a>
                    <a href="posts/read.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <h3>View All</h3>
                        <p>See all your posts</p>
                    </a>
                    <a href="#" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <h3>Profile</h3>
                        <p>Edit your account</p>
                    </a>
                </div>
            </section>

            <section class="recent-posts">
                <div class="section-header">
                    <h2>Your Recent Posts</h2>
                    <p>Latest content you've created</p>
                </div>
                
                <?php
                require_once 'config/db.php';
                $stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
                $stmt->execute([$_SESSION['user_id']]);
                
                if ($stmt->rowCount() > 0):
                    while ($post = $stmt->fetch()):
                ?>
                    <div class="post-card">
                        <div class="post-content">
                            <h3><?= htmlspecialchars($post['title']) ?></h3>
                            <p><?= substr(htmlspecialchars($post['content']), 0, 100) ?>...</p>
                        </div>
                        <div class="post-meta">
                            <div class="post-date">
                                <i class="far fa-calendar-alt"></i>
                                <?= date('M j, Y', strtotime($post['created_at'])) ?>
                            </div>
                            <a href="posts/update.php?id=<?= $post['id'] ?>" class="edit-link">
                                <i class="fas fa-pencil-alt"></i> Edit
                            </a>
                        </div>
                    </div>
                <?php
                    endwhile;
                else:
                    echo '<div class="empty-state">
                            <i class="fas fa-pen-fancy empty-icon"></i>
                            <h3>No posts yet</h3>
                            <p>You haven\'t created any posts yet. Get started now!</p>
                            <a href="posts/create.php" class="button">Create First Post</a>
                          </div>';
                endif;
                ?>
                <a href="posts/read.php" class="view-all">
                    View All Posts <i class="fas fa-arrow-right"></i>
                </a>
            </section>
        </main>

        <footer class="dashboard-footer">
            <p>© <?= date('Y') ?> My Blog. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Contact</a>
            </div>
        </footer>
    </div>
</body>
</html>

<link rel="stylesheet" href="assets/css/style.css?v=2">
