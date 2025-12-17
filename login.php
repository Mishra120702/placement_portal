<?php
// dashboard.php
session_start();
require_once 'db_config.php'; // Database configuration file

// Function to create admin user if it doesn't exist
function createAdminUser($pdo) {
    $username = 'Admin';
    $email = 'admin@asdacademy.com';
    $password = 'Admin1234';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if admin already exists
    $check_sql = "SELECT user_id FROM users WHERE username = ? OR email = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$username, $email]);
    
    if ($check_stmt->rowCount() == 0) {
        // Create admin user
        $sql = "INSERT INTO users (username, email, password_hash, user_type, first_name, last_name, is_active) 
                VALUES (?, ?, ?, 'admin', 'System', 'Administrator', 1)";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $email, $hashed_password]);
            return true;
        } catch (PDOException $e) {
            error_log("Error creating admin user: " . $e->getMessage());
            return false;
        }
    }
    return true; // Admin already exists
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        // Create admin user first (if needed)
        createAdminUser($pdo);
        
        // Check credentials
        $sql = "SELECT user_id, username, password_hash, user_type, first_name, last_name, email 
                FROM users 
                WHERE (username = ? OR email = ?) AND user_type = 'admin' AND is_active = 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['email'] = $user['email'];
            
            // Update last login
            $update_sql = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([$user['user_id']]);
            
            // Redirect to admin dashboard
            header('Location: admin_dashboard.php');
            exit();
        } else {
            $error = "Invalid username or password!";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Check if user is already logged in
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'admin') {
    header('Location: admin_dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASD Academy - Admin Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .login-header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .login-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .login-form {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .error-message {
            background: #fee;
            border: 1px solid #f00;
            color: #c00;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .admin-credentials {
            background: #f8f9fa;
            border: 1px dashed #ddd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 12px;
        }
        
        .admin-credentials h4 {
            margin-bottom: 8px;
            color: #4f46e5;
        }
        
        .admin-credentials p {
            margin-bottom: 5px;
            color: #666;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #eee;
        }
        
        @media (max-width: 480px) {
            .login-container {
                max-width: 100%;
            }
            
            .login-header, .login-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>ASD Academy</h1>
            <p>Placement Portal - Admin Dashboard</p>
        </div>
        
        <form class="login-form" method="POST" action="">
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       class="form-control" 
                       required
                       placeholder="Enter username or email"
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="form-control" 
                       required
                       placeholder="Enter password">
            </div>
            
            <button type="submit" name="login" class="btn-login">
                Login to Dashboard
            </button>
            
            <div class="admin-credentials">
                <h4>Default Admin Credentials</h4>
                <p><strong>Username:</strong> Admin</p>
                <p><strong>Email:</strong> admin@asdacademy.com</p>
                <p><strong>Password:</strong> Admin1234</p>
                <p style="color: #ff6b6b; margin-top: 8px;">
                    <small>⚠️ Change password after first login!</small>
                </p>
            </div>
        </form>
        
        <div class="footer">
            <p>© <?php echo date('Y'); ?> ASD Academy Placement Portal. All rights reserved.</p>
        </div>
    </div>
    
    <script>
        // Focus on username field on page load
        document.getElementById('username').focus();
        
        // Show/hide password functionality
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const loginForm = document.querySelector('.login-form');
            
            // Add a show password toggle (optional enhancement)
            const showPassword = document.createElement('button');
            showPassword.type = 'button';
            showPassword.innerHTML = '👁️';
            showPassword.style.cssText = `
                position: absolute;
                right: 15px;
                top: 50%;
                transform: translateY(-50%);
                background: none;
                border: none;
                cursor: pointer;
                font-size: 18px;
                opacity: 0.6;
            `;
            
            const passwordGroup = passwordInput.parentNode;
            passwordGroup.style.position = 'relative';
            passwordGroup.appendChild(showPassword);
            
            showPassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.innerHTML = type === 'password' ? '👁️' : '👁️‍🗨️';
            });
        });
    </script>
</body>
</html>