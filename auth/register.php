<?php require_once '../config/db.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <h2>Register</h2>
        <form class="auth-form" action="register.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register</button>
        </form>
        <div class="auth-links">
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verify_csrf_token($_POST['csrf_token'])) {
                die("CSRF token validation failed");
            }

            $username = trim($_POST['username']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            // Validation
            if (empty($username) || empty($password) || empty($confirm_password)) {
                echo "<p class='error'>Please fill all fields</p>";
                exit;
            }

            if ($password !== $confirm_password) {
                echo "<p class='error'>Passwords don't match</p>";
                exit;
            }

            if (strlen($password) < 8) {
                echo "<p class='error'>Password must be at least 8 characters</p>";
                exit;
            }

            $password = password_hash($password, PASSWORD_DEFAULT);

            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt->execute([$username, $password]);
                echo "<p class='success'>Registered successfully! Please login.</p>";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    echo "<p class='error'>Username already exists!</p>";
                } else {
                    echo "<p class='error'>Registration failed: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
            }
        }
        ?>
    </div>
</body>
</html>

<link rel="stylesheet" href="../assets/css/style.css?v=2">
