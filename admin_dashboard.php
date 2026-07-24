<?php
require_once 'config.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: index.php");
    exit();
}

$admin_name = $_SESSION['full_name'] ?? 'Admin';
$branch_id = $_SESSION['branch_id'] ?? 1;
$branch_name = getCurrentBranchName($conn, $branch_id);

// Use the correct function name from config.php
$eth_date = getEthiopianDateDisplay();
$greg_time = date('h:i:s A');

// Get stats - No expenses, only sales and products
$today = date('Y-m-d');
$today_sales = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_amount),0) as total, COUNT(*) as count FROM transactions WHERE DATE(transaction_date)='$today' AND branch_id=$branch_id"));
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE is_active=1"));
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"));
$total_sales_all = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_amount),0) as total FROM transactions WHERE branch_id=$branch_id"));
$total_transactions_all = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM transactions WHERE branch_id=$branch_id"));

$recent = mysqli_query($conn, "SELECT * FROM transactions WHERE branch_id=$branch_id ORDER BY transaction_date DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ዳሽቦርድ - አጸደ ትጉሃን ሰንበት ትምህርት ቤት</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{
            font-family:'Segoe UI',sans-serif;
            background: radial-gradient(circle at center, #F6E27A 0%, #F4A640 35%, #D96B2B 65%, #7A1E1E 100%);
            display:flex;
            min-height:100vh;
        }
        
        .sidebar{
            width:250px;
            background:linear-gradient(180deg,#5C2E0B,#8B4513);
            color:white;
            position:fixed;
            height:100vh;
            padding:20px 0;
            z-index:100;
            box-shadow: 4px 0 20px rgba(0,0,0,0.3);
        }
        .sidebar-header{padding:20px;text-align:center;border-bottom:1px solid rgba(255,255,255,0.2);margin-bottom:20px;}
        .sidebar-header i{font-size:35px;color:#DAA520;}
        .sidebar-header h2{font-size:0.9rem;color:#DAA520;margin-top:8px;}
        .sidebar-header small{color:#ccc;font-size:0.7rem;}
        .nav-menu{list-style:none;padding:0 15px;}
        .nav-menu li{margin-bottom:3px;}
        .nav-menu a{
            display:flex;align-items:center;gap:10px;padding:11px 15px;
            color:white;text-decoration:none;border-radius:8px;font-size:0.85rem;
            transition: all 0.3s;
        }
        .nav-menu a:hover,.nav-menu a.active{background:rgba(218,165,32,0.2);color:#DAA520;}
        .sidebar-footer{position:absolute;bottom:0;width:100%;padding:15px 20px;border-top:1px solid rgba(255,255,255,0.2);font-size:0.75rem;}
        
        .main-content{margin-left:250px;padding:20px;width:calc(100% - 250px);}
        
        .top-bar{
            background:white;padding:15px 20px;border-radius:15px;display:flex;
            justify-content:space-between;align-items:center;margin-bottom:20px;
            box-shadow:0 8px 30px rgba(0,0,0,0.1);flex-wrap:wrap;gap:10px;
        }
        .top-bar h2{color:#8B4513;font-size:1.2rem;}
        .eth-clock{
            background:#8B4513;color:#DAA520;padding:8px 15px;border-radius:20px;
            font-weight:600;font-size:0.85rem;display:flex;align-items:center;gap:8px;
            box-shadow: 0 4px 15px rgba(139,69,19,0.3);
        }
        .branch-badge{
            background:#f0f0f0;padding:8px 15px;border-radius:20px;font-size:0.85rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;margin-bottom:20px;}
        .stat-card{
            background:white;padding:20px;border-radius:15px;
            box-shadow:0 8px 30px rgba(0,0,0,0.1);transition:all 0.3s;
        }
        .stat-card:hover{transform:translateY(-5px);box-shadow:0 15px 40px rgba(0,0,0,0.15);}
        .stat-card .icon{font-size:28px;margin-bottom:8px;}
        .stat-card .value{font-size:1.6rem;font-weight:700;color:#333;}
        .stat-card .label{font-size:0.8rem;color:#888;margin-top:4px;}
        
        .card{
            background:white;border-radius:15px;padding:20px;
            box-shadow:0 8px 30px rgba(0,0,0,0.1);margin-bottom:20px;
        }
        .card h3{color:#8B4513;margin-bottom:15px;display:flex;align-items:center;gap:10px;font-size:1rem;}
        
        .action-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;}
        .action-card{
            background:white;padding:18px;border-radius:15px;text-align:center;
            text-decoration:none;color:#333;box-shadow:0 4px 15px rgba(0,0,0,0.08);
            transition:all 0.3s;border: 2px solid transparent;
        }
        .action-card:hover{
            transform:translateY(-3px);
            box-shadow:0 10px 30px rgba(0,0,0,0.15);
            border-color: #DAA520;
        }
        .action-card i{font-size:28px;color:#DAA520;margin-bottom:8px;}
        .action-card h4{font-size:0.85rem;}
        
        table{width:100%;border-collapse:collapse;font-size:0.85rem;}
        th{background:#8B4513;color:white;padding:10px;text-align:left;}
        td{padding:10px;border-bottom:1px solid #f0f0f0;}
        tr:hover{background:#FFF8DC;}
        
        .amount{font-weight:700;color:#28a745;}
        
        @media(max-width:768px){
            .sidebar{width:100%;height:auto;position:relative;}
            .sidebar-footer{position:relative;}
            .main-content{margin-left:0;width:100%;}
            .nav-menu{display:flex;flex-wrap:wrap;gap:5px;}
            .nav-menu a{font-size:0.75rem;padding:8px 10px;}
        }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-header">
        <img src="icon.png" alt="Icon" style="width:50px;height:50px;border-radius:15px;margin-bottom:10px;">
        <h2>አጸደ ትጉሃን</h2>
        <small>ሰንበት ትምህርት ቤት</small>
    </div>
    <ul class="nav-menu">
        <li><a href="admin_dashboard.php" class="active"><i class="fas fa-th-large"></i> ዳሽቦርድ</a></li>
        <li><a href="seller_pos.php"><i class="fas fa-cash-register"></i> መሸጫ</a></li>
        <li><a href="admin_products.php"><i class="fas fa-box"></i> ምርት መቀበያ</a></li>
        <li><a href="admin_reports.php"><i class="fas fa-chart-bar"></i> ሪፖርቶች</a></li>
        <li><a href="admin_users.php"><i class="fas fa-users"></i> ተጠቃሚዎች</a></li>
        <li><a href="history.php"><i class="fas fa-history"></i> ታሪክ</a></li>
    </ul>
    <div class="sidebar-footer">
        <div><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($admin_name); ?></div>
        <a href="logout.php" style="color:#DAA520;text-decoration:none;font-size:0.8rem;display:block;margin-top:8px;">
            <i class="fas fa-sign-out-alt"></i> ውጣ
        </a>
    </div>
</div>

<div class="main-content">
    <div class="top-bar">
        <h2><i class="fas fa-dashboard" style="color:#DAA520;"></i> ዳሽቦርድ</h2>
        <div style="display:flex;align-items:center;gap:12px;">
            <span class="branch-badge"><i class="fas fa-store"></i> <?php echo htmlspecialchars($branch_name); ?></span>
            <span class="eth-clock" id="ethTime"><i class="fas fa-clock"></i> <?php echo $greg_time; ?></span>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="icon" style="color:#DAA520;"><i class="fas fa-calendar-day"></i></div>
            <div class="value"><?php echo number_format($today_sales['total'],2); ?> ብር</div>
            <div class="label">የዛሬ ሽያጭ (<?php echo $today_sales['count']; ?> ግብይቶች)</div>
        </div>
        <div class="stat-card">
            <div class="icon" style="color:#28a745;"><i class="fas fa-chart-line"></i></div>
            <div class="value"><?php echo number_format($total_sales_all['total'],2); ?> ብር</div>
            <div class="label">ጠቅላላ ሽያጭ (<?php echo $total_transactions_all['count']; ?> ግብይቶች)</div>
        </div>
        <div class="stat-card">
            <div class="icon" style="color:#17a2b8;"><i class="fas fa-boxes"></i></div>
            <div class="value"><?php echo $total_products['count']; ?></div>
            <div class="label">ምርቶች</div>
        </div>
        <div class="stat-card">
            <div class="icon" style="color:#ff9800;"><i class="fas fa-users"></i></div>
            <div class="value"><?php echo $total_users['count']; ?></div>
            <div class="label">ተጠቃሚዎች</div>
        </div>
    </div>
    
    <div class="card">
        <h3><i class="fas fa-bolt" style="color:#DAA520;"></i> ፈጣን እርምጃዎች</h3>
        <div class="action-grid">
            <a href="seller_pos.php" class="action-card"><i class="fas fa-cash-register"></i><h4>መሸጫ</h4></a>
            <a href="admin_products.php" class="action-card"><i class="fas fa-box"></i><h4>ምርት መቀበያ</h4></a>
            <a href="admin_reports.php" class="action-card"><i class="fas fa-chart-bar"></i><h4>ሪፖርት</h4></a>
            <a href="admin_users.php" class="action-card"><i class="fas fa-user-plus"></i><h4>አዲስ ተጠቃሚ</h4></a>
            <a href="history.php" class="action-card"><i class="fas fa-history"></i><h4>ታሪክ</h4></a>
        </div>
    </div>
    
    <div class="card">
        <h3><i class="fas fa-history"></i> የቅርብ ሽያጮች</h3>
        <table>
            <thead><tr><th>ደረሰኝ</th><th>ሻጭ</th><th>ጠቅላላ</th><th>ክፍያ</th><th>ቀን</th></tr></thead>
            <tbody>
                <?php while($r=mysqli_fetch_assoc($recent)): ?>
                <tr>
                    <td>#<?php echo str_pad($r['id'],6,'0',STR_PAD_LEFT); ?></td>
                    <td><?php echo htmlspecialchars($r['seller_name']); ?></td>
                    <td class="amount"><?php echo number_format($r['total_amount'],2); ?> ብር</td>
                    <td><?php echo $r['payment_method']; ?></td>
                    <td><?php echo date('M d, h:i A',strtotime($r['transaction_date'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function updateTime(){
    var n=new Date();
    var h=n.getHours(),m=String(n.getMinutes()).padStart(2,'0'),s=String(n.getSeconds()).padStart(2,'0');
    var ap=h>=12?'PM':'AM';
    h=h%12||12;
    document.getElementById('ethTime').innerHTML='<i class="fas fa-clock"></i> '+h+':'+m+':'+s+' '+ap;
}
setInterval(updateTime,1000);
</script>
</body>
</html>
<?php mysqli_close($conn); ?>