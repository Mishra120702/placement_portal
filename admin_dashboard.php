<?php
// admin_dashboard.php
session_start();
require_once 'db_config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: dashboard.php');
    exit();
}

// Get dashboard statistics
try {
    // Count total users
    $user_count = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
    
    // Count students
    $student_count = $pdo->query("SELECT COUNT(*) as count FROM students")->fetch()['count'];
    
    // Count companies
    $company_count = $pdo->query("SELECT COUNT(*) as count FROM companies")->fetch()['count'];
    
    // Count internships
    $internship_count = $pdo->query("SELECT COUNT(*) as count FROM internships")->fetch()['count'];
    
    // Count placed students
    $placed_count = $pdo->query("SELECT COUNT(*) as count FROM students WHERE is_placed = 1")->fetch()['count'];
    
} catch (PDOException $e) {
    $error = "Error fetching dashboard data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ASD Academy</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }
        
        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo h1 {
            font-size: 24px;
            font-weight: 600;
        }
        
        .logo p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4f46e5;
            font-weight: bold;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .dashboard-content {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .welcome-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .welcome-section h2 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .welcome-section p {
            color: #666;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 16px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 10px;
        }
        
        .quick-actions {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .quick-actions h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .action-btn {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s, transform 0.2s;
        }
        
        .action-btn:hover {
            background: #4338ca;
            transform: translateY(-2px);
        }
        
        .action-btn.secondary {
            background: #f1f5f9;
            color: #64748b;
        }
        
        .action-btn.secondary:hover {
            background: #e2e8f0;
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .user-info {
                justify-content: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <h1>ASD Academy Placement</h1>
                <p>Admin Dashboard</p>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
                <div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                    <div style="font-size: 12px; opacity: 0.9;"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
                </div>
                <a href="?logout=true" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>
    
    <div class="dashboard-content">
        <div class="welcome-section">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>! 👋</h2>
            <p>Manage the placement portal, users, companies, and monitor all activities from this dashboard.</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div style="background: #fee; border: 1px solid #f00; color: #c00; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="stat-number"><?php echo htmlspecialchars($user_count); ?></div>
                <p>All registered users</p>
            </div>
            
            <div class="stat-card">
                <h3>Students</h3>
                <div class="stat-number"><?php echo htmlspecialchars($student_count); ?></div>
                <p>Registered students</p>
            </div>
            
            <div class="stat-card">
                <h3>Companies</h3>
                <div class="stat-number"><?php echo htmlspecialchars($company_count); ?></div>
                <p>Partner companies</p>
            </div>
            
            <div class="stat-card">
                <h3>Internships</h3>
                <div class="stat-number"><?php echo htmlspecialchars($internship_count); ?></div>
                <p>Active opportunities</p>
            </div>
            
            <div class="stat-card">
                <h3>Placed Students</h3>
                <div class="stat-number"><?php echo htmlspecialchars($placed_count); ?></div>
                <p>Successfully placed</p>
            </div>
        </div>
        
        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <div class="action-buttons">
                <button class="action-btn" onclick="alert('Feature coming soon!')">Manage Users</button>
                <button class="action-btn" onclick="alert('Feature coming soon!')">View Reports</button>
                <button class="action-btn" onclick="alert('Feature coming soon!')">Manage Companies</button>
                <button class="action-btn" onclick="alert('Feature coming soon!')">Create Announcement</button>
                <button class="action-btn secondary" onclick="alert('Feature coming soon!')">System Settings</button>
            </div>
        </div>
    </div>
    
    <script>
        // Add any interactive features here
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Admin dashboard loaded');
        });
    </script>
</body>
</html>