<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

require_once 'config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['login_success'] = true; // Set flag to trigger success toast on dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please enter both username and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script>
        // Clean up any lingering tab data if arriving at the login page
        sessionStorage.removeItem('admin_tab_active');
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Church Attendance</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=2">
    <style>
        .login-container {
            max-width: 450px;
            margin: auto;
        }
        .error-message {
            background-color: var(--secondary-color);
            color: white;
            padding: 1rem;
            border-radius: var(--border-radius-md);
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.95rem;
            box-shadow: 0 4px 10px rgba(244, 63, 94, 0.3);
        }
    </style>
</head>
<body>
    <div class="background-decor"></div>
    <div class="container login-container">
        
        <header class="form-header">
            <div class="logo-container" style="width: 70px; height: 70px; margin-bottom: 1rem;">
                <i class="fa-solid fa-lock"></i>
            </div>
            <h1>Admin Login</h1>
            <p>Access the attendance dashboard.</p>
        </header>

        <?php if ($error): ?>
            <div class="error-message">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-user-shield"></i>
                    <input type="text" id="username" name="username" placeholder="Enter admin username" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-key"></i>
                    <input type="password" id="password" name="password" placeholder="Enter admin password" required>
                    <button type="button" id="togglePassword" style="position: absolute; right: 1rem; background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.1rem; transition: var(--transition); display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-actions" style="margin-top: 2rem;">
                <button type="submit" class="submit-btn">
                    <span>Login Securely</span>
                    <i class="fa-solid fa-shield-halved"></i>
                </button>
            </div>
            
            <div style="text-align: center; margin-top: 1.5rem;">
                <a href="index.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.5rem; transition: var(--transition);">
                    <i class="fa-solid fa-arrow-left"></i> Go to Public Form
                </a>
            </div>
        </form>
    </div>
    
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {
                // Toggle the type attribute
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle the eye / eye slash icon
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
                
                // Toggle color to indicate active state
                if (type === 'text') {
                    this.style.color = 'var(--primary-color)';
                } else {
                    this.style.color = 'var(--text-muted)';
                }
            });
        }
    </script>
</body>
</html>
