<?php
require_once 'config.php';
if (!isLoggedIn() || !isAdmin()) redirect('index.php');

$message = '';
$branch_id = $_SESSION['branch_id'] ?? 1;

if (isset($_POST['add_user'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    $check = mysqli_query($conn, "SELECT id FROM users WHERE username='$username'");
    if (mysqli_num_rows($check) > 0) {
        $message = '<div style="background:#f8d7da;color:#721c24;padding:12px;border-radius:10px;margin-bottom:15px;">ይህ የተጠቃሚ ስም አስቀድሞ አለ!</div>';
    } else {
        mysqli_query($conn, "INSERT INTO users (name, username, password, role, branch_id) VALUES ('$name', '$username', '$password', '$role', $branch_id)");
        
        if ($role == 'seller') {
            $new_id = mysqli_insert_id($conn);
            $products = mysqli_query($conn, "SELECT name, unit_price FROM products WHERE is_active=1");
            while ($p = mysqli_fetch_assoc($products)) {
                mysqli_query($conn, "INSERT INTO seller_inventory (seller_id, item_name, current_stock, unit, price, branch_id) VALUES ($new_id, '{$p['name']}', 0, 'pcs', {$p['unit_price']}, $branch_id)");
            }
        }
        
        $message = '<div style="background:#d4edda;color:#155724;padding:12px;border-radius:10px;margin-bottom:15px;">✅ ተጠቃሚው ተመዝግቧል!</div>';
    }
}

if (isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    if ($id != $_SESSION['user_id']) {
        mysqli_query($conn, "DELETE FROM users WHERE id=$id");
        $message = '<div style="background:#FFF3CD;color:#856404;padding:12px;border-radius:10px;margin-bottom:15px;">ተጠቃሚው ተሰርዟል!</div>';
    }
}

$users = mysqli_query($conn, "SELECT * FROM users ORDER BY role, name");
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ተጠቃሚዎች - አጽደተጉሃን</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: radial-gradient(circle at center, #F6E27A 0%, #F4A640 35%, #D96B2B 65%, #7A1E1E 100%);
            padding: 15px; 
        }
        .container { max-width: 1000px; margin: 0 auto; }
        .header {
            background: linear-gradient(135deg, #8B4513, #DAA520); color: white; padding: 20px 25px;
            border-radius: 15px; margin-bottom: 20px; display: flex; justify-content: space-between;
            align-items: center; flex-wrap: wrap; gap: 15px; box-shadow: 0 8px 25px rgba(139,69,19,0.3);
        }
        .card {
            background: white; border-radius: 15px; padding: 25px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1); margin-bottom: 20px;
        }
        .form-row { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 12px; }
        .form-row input, .form-row select {
            flex: 1; min-width: 150px; padding: 12px; border: 2px solid #e0d5c1;
            border-radius: 10px; font-size: 0.9rem;
        }
        .form-row input:focus, .form-row select:focus { outline: none; border-color: #DAA520; }
        .btn {
            padding: 12px 20px; border: none; border-radius: 10px; cursor: pointer; font-weight: 600;
            transition: all 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .btn:hover { transform: translateY(-2px); }
        .btn-primary { background: linear-gradient(135deg, #8B4513, #DAA520); color: white; }
        .btn-info { background: #17a2b8; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-sm { padding: 6px 12px; font-size: 0.8rem; }
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th { background: #8B4513; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #f0f0f0; }
        tr:hover { background: #FFF8DC; }
        .badge { padding: 4px 10px; border-radius: 15px; font-size: 0.8rem; font-weight: 600; }
        .badge-admin { background: #f8d7da; color: #721c24; }
        .badge-seller { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><img src="icon.png" style="width:30px;height:30px;border-radius:8px;vertical-align:middle;"> ተጠቃሚዎች አስተዳደር</h1>
            <a href="admin_dashboard.php" class="btn btn-info"><i class="fas fa-arrow-left"></i> ተመለስ</a>
        </div>
        
        <?php echo $message; ?>
        
        <div class="card">
            <h3 style="color:#8B4513; margin-bottom:15px;"><i class="fas fa-user-plus"></i> አዲስ ተጠቃሚ</h3>
            <form method="POST">
                <div class="form-row">
                    <input type="text" name="name" placeholder="ሙሉ ስም" required>
                    <input type="text" name="username" placeholder="የተጠቃሚ ስም" required>
                </div>
                <div class="form-row">
                    <input type="password" name="password" placeholder="የይለፍ ቃል" required minlength="4">
                    <select name="role" required>
                        <option value="seller">ሻጭ</option>
                        <option value="admin">አስተዳዳሪ</option>
                    </select>
                </div>
                <button type="submit" name="add_user" class="btn btn-primary"><i class="fas fa-save"></i> ተጠቃሚውን መዝግብ</button>
            </form>
        </div>
        
        <div class="card">
            <h3 style="color:#8B4513; margin-bottom:15px;"><i class="fas fa-list"></i> ሁሉም ተጠቃሚዎች</h3>
            <div style="overflow-x:auto;">
                <table>
                    <thead><tr><th>ID</th><th>ስም</th><th>የተጠቃሚ ስም</th><th>ሚና</th><th>የተመዘገበበት</th><th>ተግባር</th></tr></thead>
                    <tbody>
                        <?php while ($u = mysqli_fetch_assoc($users)): ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($u['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                            <td><span class="badge <?php echo $u['role']=='admin'?'badge-admin':'badge-seller'; ?>"><?php echo $u['role']=='admin'?'አስተዳዳሪ':'ሻጭ'; ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                            <td>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <a href="?delete_user=<?php echo $u['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('መሰረዝ እርግጠኛ ነዎት?')"><i class="fas fa-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
<?php mysqli_close($conn); ?>