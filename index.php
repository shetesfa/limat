<?php
session_start();
require_once 'config.php';

// If already logged in, redirect to appropriate page
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin_dashboard.php');
    } else {
        redirect('seller_pos.php');
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['branch_id'] = $user['branch_id'] ?? 1;
            
            // Log activity
            mysqli_query($conn, "INSERT INTO activity_log (user_id, action) VALUES ({$user['id']}, 'ወደ ስርዓቱ ገብቷል')");
            
            if ($user['role'] == 'admin') {
                redirect('admin_dashboard.php');
            } else {
                redirect('seller_pos.php');
            }
        } else {
            $error = 'የይለፍ ቃል ትክክል አይደለም!';
        }
    } else {
        $error = 'ተጠቃሚው አልተገኘም!';
    }
}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>አጸደ ትጉሃን ሰንበት ትምህርት ቤት - መግቢያ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', 'Nyala', sans-serif; }
        
        body {
            background: radial-gradient(circle at center, #F6E27A 0%, #F4A640 35%, #D96B2B 65%, #7A1E1E 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideUp 0.5s ease;
        }
        
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo img {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            margin-bottom: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .logo h1 {
            color: #8B4513;
            font-size: 1.5rem;
            font-weight: 800;
        }
        
        .logo p {
            color: #888;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
        }
        
        .alert-danger {
            background: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
            outline: none;
        }
        
        .form-control:focus {
            border-color: #8B4513;
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }
        
        .password-wrapper {
            position: relative;
        }
        
        .password-wrapper .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            background: none;
            border: none;
            font-size: 1.1rem;
        }
        
        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #8B4513, #a0522d);
            color: #DAA520;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(139, 69, 19, 0.3);
        }
        
        .footer-text {
            text-align: center;
            margin-top: 20px;
            color: #999;
            font-size: 0.85rem;
        }
        
        .test-accounts {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            font-size: 0.8rem;
            color: #666;
        }
        
        .test-accounts strong {
            color: #8B4513;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <img src="icon.png" alt="Icon">
                <h1>አጸደ ትጉሃን</h1>
                <p>ሰንበት ትምህርት ቤት - የሽያጭ አስተዳደር ስርዓት</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> ተጠቃሚ ስም</label>
                    <input type="text" name="username" class="form-control" placeholder="የተጠቃሚ ስም ያስገቡ" required autofocus>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> የይለፍ ቃል</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" class="form-control" placeholder="የይለፍ ቃል ያስገቡ" required>
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> ግባ
                </button>
            </form>
            
            <div class="test-accounts">
                <strong>መሞከሪያ አካውንቶች:</strong><br>
                <strong>አድሚን:</strong> admin / 123456<br>
                <strong>ሻጭ:</strong> selam / 123456
            </div>
            
            <div class="footer-text">
                <i class="fas fa-shield-alt"></i> ደህንነቱ የተጠበቀ ግንኙነት
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (password.type === 'password') {
                password.type = 'text';
                eyeIcon.className = 'fas fa-eye-slash';
            } else {
                password.type = 'password';
                eyeIcon.className = 'fas fa-eye';
            }
        }
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>