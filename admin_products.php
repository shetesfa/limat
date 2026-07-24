<?php
session_start();
require_once 'config.php';
date_default_timezone_set('Africa/Addis_Ababa');

// Set MySQL timezone to match PHP (East Africa Time)
mysqli_query($conn, "SET time_zone = '+03:00'");

// Check login
if (!isLoggedIn() || !isAdmin()) {
    redirect('index.php');
}

$user_id = $_SESSION['user_id'] ?? 0;
$user_name = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Admin';
$user_role = $_SESSION['role'] ?? 'admin';
$branch_id = $_SESSION['branch_id'] ?? 1;
$branch_name = getCurrentBranchName($conn, $branch_id);

// ========== ETHIOPIAN DATE FUNCTIONS ==========
function gregorian_to_ethiopian($year, $month, $day) {
    $ethiopian_months = [
        1 => "መስከረም", 2 => "ጥቅምት", 3 => "ህዳር", 4 => "ታህሳስ", 
        5 => "ጥር", 6 => "የካቲት", 7 => "መጋቢት", 8 => "ሚያዝያ", 
        9 => "ግንቦት", 10 => "ሰኔ", 11 => "ሐምሌ", 12 => "ነሐሴ", 13 => "ጳጉሜ"
    ];
    
    $ethiopian_year = $year - 8;
    $is_gregorian_leap = ($year % 4 == 0 && $year % 100 != 0) || ($year % 400 == 0);
    $new_year_day = $is_gregorian_leap ? 12 : 11;
    
    if ($month > 9 || ($month == 9 && $day >= $new_year_day)) {
        $ethiopian_year = $year - 7;
    }
    
    $new_year_gregorian_year = ($month < 9 || ($month == 9 && $day < $new_year_day)) ? $year - 1 : $year;
    $is_new_year_leap = ($new_year_gregorian_year % 4 == 0 && $new_year_gregorian_year % 100 != 0) || ($new_year_gregorian_year % 400 == 0);
    $new_year_day_final = $is_new_year_leap ? 12 : 11;
    
    $jd_current = gregoriantojd($month, $day, $year);
    $jd_new_year = gregoriantojd(9, $new_year_day_final, $new_year_gregorian_year);
    $days_since_new_year = $jd_current - $jd_new_year;
    
    $ethiopian_month = floor($days_since_new_year / 30) + 1;
    $ethiopian_day = ($days_since_new_year % 30) + 1;
    
    if ($ethiopian_month == 13) {
        $is_ethiopian_leap = ($ethiopian_year % 4 == 3);
        $max_pagume_days = $is_ethiopian_leap ? 6 : 5;
        if ($ethiopian_day > $max_pagume_days) {
            $ethiopian_month = 1;
            $ethiopian_day -= $max_pagume_days;
            $ethiopian_year++;
        }
    }
    
    $ethiopian_month = max(1, min(13, $ethiopian_month));
    
    return [
        'year' => $ethiopian_year,
        'month' => $ethiopian_month,
        'month_name' => $ethiopian_months[$ethiopian_month] ?? "መስከረም",
        'day' => $ethiopian_day,
        'full_date' => sprintf("%04d-%02d-%02d", $ethiopian_year, $ethiopian_month, $ethiopian_day),
        'display_date' => $ethiopian_day . ' ' . ($ethiopian_months[$ethiopian_month] ?? "መስከረም") . ' ' . $ethiopian_year
    ];
}

function get_ethiopian_today() {
    return gregorian_to_ethiopian(date('Y'), date('n'), date('j'));
}

function format_ethiopian_date_from_db($db_datetime) {
    if (empty($db_datetime)) return ['display' => ''];
    $timestamp = strtotime($db_datetime);
    $year = (int)date('Y', $timestamp);
    $month = (int)date('n', $timestamp);
    $day = (int)date('j', $timestamp);
    $eth = gregorian_to_ethiopian($year, $month, $day);
    return ['display' => $eth['display_date'], 'year' => $eth['year'], 'month' => $eth['month'], 'month_name' => $eth['month_name'], 'day' => $eth['day']];
}

function format_gregorian_time_12hr($datetime) {
    if (empty($datetime)) return '';
    $date = new DateTime($datetime, new DateTimeZone('Africa/Addis_Ababa'));
    return $date->format('h:i:s A');
}

$current_ethiopian = get_ethiopian_today();
$current_gregorian_time = date('h:i:s A');
$today_display = $current_ethiopian['display_date'];

$message = '';
$message_type = '';

// Handle Add Stock Receive - FIXED: No duplicate check
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_stock'])) {
    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $quantity = floatval($_POST['quantity']);
    $unit = mysqli_real_escape_string($conn, $_POST['unit']);
    $buy_price = floatval($_POST['buy_price']);
    $sell_price = floatval($_POST['sell_price']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');
    
    $insert_query = "INSERT INTO stock_receive (item_name, quantity, unit, buy_price, sell_price, notes, received_by, received_by_name, branch_id, date_received) 
                     VALUES ('$item_name', $quantity, '$unit', $buy_price, $sell_price, '$notes', $user_id, '$user_name', $branch_id, NOW())";
    
    if (mysqli_query($conn, $insert_query)) {
        // Always add/update product in products table
        $check_product = mysqli_query($conn, "SELECT id FROM products WHERE name = '$item_name'");
        if (mysqli_num_rows($check_product) > 0) {
            mysqli_query($conn, "UPDATE products SET unit_price=$sell_price, is_active=1 WHERE name='$item_name'");
        } else {
            mysqli_query($conn, "INSERT INTO products (name, unit_price, last_edited_by, last_edited_at) VALUES ('$item_name', $sell_price, '$user_name', NOW())");
        }
        
        // Update inventory for ALL sellers in this branch
        $sellers = mysqli_query($conn, "SELECT id FROM users WHERE role='seller' AND branch_id=$branch_id");
        while ($s = mysqli_fetch_assoc($sellers)) {
            $inv_check = mysqli_query($conn, "SELECT id FROM seller_inventory WHERE seller_id={$s['id']} AND item_name='$item_name'");
            if (mysqli_num_rows($inv_check) > 0) {
                mysqli_query($conn, "UPDATE seller_inventory SET current_stock = current_stock + $quantity, price=$sell_price, unit='$unit' WHERE seller_id={$s['id']} AND item_name='$item_name'");
            } else {
                mysqli_query($conn, "INSERT INTO seller_inventory (seller_id, item_name, current_stock, unit, price, branch_id) VALUES ({$s['id']}, '$item_name', $quantity, '$unit', $sell_price, $branch_id)");
            }
        }
        
        $_SESSION['message'] = "✅ ምርት በተሳካ ሁኔታ ተቀብሏል!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "❌ ስህተት: " . mysqli_error($conn);
        $_SESSION['message_type'] = "danger";
    }
    
    header("Location: admin_products.php");
    exit();
}

// Get products for dropdown
$products_list = [];
$products_result = mysqli_query($conn, "SELECT name, unit_price FROM products WHERE is_active = 1 ORDER BY name");
if ($products_result) {
    while($product = mysqli_fetch_assoc($products_result)) {
        $products_list[] = $product;
    }
}

// ========== GET STOCK RECEIVE HISTORY ==========
$today_stock = $yesterday_stock = $all_stock = [];

$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'stock_receive'");
$table_exists = $check_table && mysqli_num_rows($check_table) > 0;

if ($table_exists) {
    $today_date = date('Y-m-d');
    $yesterday_date = date('Y-m-d', strtotime('-1 day'));
    
    $stock_query = "SELECT * FROM stock_receive WHERE branch_id = $branch_id ORDER BY date_received DESC LIMIT 500";
    $stock_result = mysqli_query($conn, $stock_query);
    
    if ($stock_result) {
        while($stock = mysqli_fetch_assoc($stock_result)) {
            $eth_date = format_ethiopian_date_from_db($stock['date_received']);
            $stock['ethiopian_date'] = $eth_date['display'];
            $stock['gregorian_time'] = format_gregorian_time_12hr($stock['date_received']);
            
            $stock_date = date('Y-m-d', strtotime($stock['date_received']));
            if ($stock_date == $today_date) {
                $today_stock[] = $stock;
            } elseif ($stock_date == $yesterday_date) {
                $yesterday_stock[] = $stock;
            }
            $all_stock[] = $stock;
        }
    }
    
    $today_stock = array_slice($today_stock, 0, 50);
    $yesterday_stock = array_slice($yesterday_stock, 0, 50);
    $all_stock = array_slice($all_stock, 0, 100);
}

$total_today_quantity = array_sum(array_column($today_stock, 'quantity'));
$total_all_quantity = array_sum(array_column($all_stock, 'quantity'));
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpg" href="image/photo_2026-01-12_07-44-10.jpg">
    <title>ምርት መቀበያ - አጸደ ትጉሃን ሰንበት ትምህርት ቤት</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #7209b7;
            --success: #4cc9f0;
            --success-dark: #00b894;
            --warning: #f8961e;
            --danger: #f72585;
            --info: #3a86ff;
            --light: #f8f9fa;
            --dark: #212529;
            --gold: #DAA520;
            --gray-200: #e9ecef;
            --gray-600: #6c757d;
            --border-radius: 15px;
            --border-radius-sm: 10px;
            --shadow: 0 8px 30px rgba(0,0,0,0.12);
            --transition: all 0.3s ease;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', 'Nyala', 'Abyssinica SIL', sans-serif; }
        
        body { 
            background: radial-gradient(circle at center, #F6E27A 0%, #F4A640 35%, #D96B2B 65%, #7A1E1E 100%);
            min-height: 100vh; 
            padding: 20px; 
            color: var(--dark); 
        }
        
        .dashboard-container { 
            max-width: 1400px; 
            margin: 0 auto; 
            background: white; 
            border-radius: var(--border-radius); 
            box-shadow: 0 15px 50px rgba(0,0,0,0.2); 
            overflow: hidden; 
        }
        
        .dashboard-header { 
            background: linear-gradient(135deg, #f39c12, #e67e22); 
            color: white; 
            padding: 25px 30px; 
        }
        
        .header-content { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            flex-wrap: wrap; 
            gap: 20px; 
        }
        
        .header-title h1 { 
            font-size: 1.8rem; 
            font-weight: 800; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        
        .role-badge {
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 30px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-left: 10px;
        }
        
        .ethiopian-date-badge, .branch-badge { 
            background: rgba(255,255,255,0.2); 
            padding: 8px 15px; 
            border-radius: 20px; 
            font-size: 0.9rem; 
            backdrop-filter: blur(10px); 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            margin-left: 10px; 
        }
        
        .gregorian-time-badge {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: monospace;
        }
        
        .user-info { 
            background: rgba(255,255,255,0.2); 
            padding: 12px 18px; 
            border-radius: var(--border-radius-sm); 
            display: flex; 
            align-items: center; 
            gap: 12px; 
        }
        
        .avatar { 
            width: 45px; 
            height: 45px; 
            background: linear-gradient(45deg, var(--primary), var(--secondary)); 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: bold; 
            color: white; 
            font-size: 1.2rem; 
        }
        
        .header-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        
        .btn { 
            padding: 12px 20px; 
            border-radius: var(--border-radius-sm); 
            font-weight: 600; 
            text-decoration: none; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            border: none; 
            cursor: pointer; 
            transition: var(--transition); 
        }
        
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .btn-back { background: rgba(255,255,255,0.9); color: var(--primary); }
        
        .refresh-btn { 
            background: var(--info); 
            color: white; 
            border: none; 
            padding: 8px 15px; 
            border-radius: 20px; 
            cursor: pointer; 
            display: flex; 
            align-items: center; 
            gap: 5px; 
        }
        
        .alert { 
            margin: 0 30px 20px; 
            padding: 15px 20px; 
            border-radius: var(--border-radius-sm); 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            animation: slideDown 0.3s ease; 
        }
        .alert-success { background: #d4edda; border: 2px solid #28a745; color: #155724; }
        .alert-danger { background: #f8d7da; border: 2px solid #dc3545; color: #721c24; }
        .alert-warning { background: #fff3cd; border: 2px solid #ffc107; color: #856404; }
        
        @keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        
        .stats-bar {
            display: flex;
            gap: 15px;
            margin: 0 30px 30px;
        }
        
        .stat-card {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: var(--border-radius-sm);
            box-shadow: var(--shadow);
            text-align: center;
            border: 1px solid var(--gray-200);
        }
        
        .stat-number { font-size: 2.2rem; font-weight: 800; margin: 5px 0; color: var(--dark); }
        .stat-label { font-size: 0.85rem; color: var(--gray-600); text-transform: uppercase; }
        
        .dashboard-content { 
            padding: 30px; 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 30px; 
        }
        
        @media (max-width:1200px){ 
            .dashboard-content { grid-template-columns: 1fr; } 
            .stats-bar { flex-direction: column; }
        }
        
        .form-panel, .history-panel { 
            background: white; 
            border-radius: var(--border-radius); 
            padding: 30px; 
            box-shadow: var(--shadow); 
            border: 1px solid var(--gray-200); 
            animation: fadeIn 0.5s ease; 
        }
        
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        
        .panel-title { 
            font-size: 1.5rem; 
            font-weight: 700; 
            margin-bottom: 25px; 
            padding-bottom: 15px; 
            border-bottom: 2px solid var(--gray-200); 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        
        .form-group { margin-bottom: 20px; }
        
        .form-group label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 600; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
        }
        
        .form-control { 
            width: 100%; 
            padding: 14px; 
            border: 2px solid var(--gray-200); 
            border-radius: var(--border-radius-sm); 
            font-size: 1rem; 
            transition: var(--transition); 
            background: white;
        }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
        
        select.form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%236c757d' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: calc(100% - 15px) center;
            padding-right: 40px;
        }
        
        textarea.form-control { resize: vertical; min-height: 80px; }
        
        .price-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .quantity-unit-group { display: grid; grid-template-columns: 2fr 1fr; gap: 12px; }
        
        .submit-btn { 
            width: 100%; 
            padding: 16px; 
            background: linear-gradient(to right, var(--primary), var(--secondary)); 
            color: white; 
            border: none; 
            border-radius: var(--border-radius-sm); 
            font-size: 1.1rem; 
            font-weight: 700; 
            cursor: pointer; 
            transition: var(--transition); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            gap: 10px; 
        }
        .submit-btn:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(67,97,238,0.3); }
        
        .history-panel { 
            max-height: 800px; 
            overflow: hidden; 
            display: flex; 
            flex-direction: column; 
        }
        
        .tabs { 
            display: flex; 
            border-bottom: 2px solid var(--gray-200); 
            margin-bottom: 20px; 
        }
        
        .tab-btn { 
            flex: 1; 
            padding: 15px; 
            border: none; 
            background: var(--gray-200); 
            font-weight: 600; 
            cursor: pointer; 
            transition: var(--transition); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            gap: 8px; 
        }
        .tab-btn:hover { background: #d0d0d0; }
        .tab-btn.active { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; }
        
        .tab-content { display: none; flex: 1; overflow-y: auto; padding-right: 10px; }
        .tab-content.active { display: block; animation: fadeIn 0.3s ease; }
        
        .stock-table-container { 
            overflow-y: auto; 
            max-height: 600px; 
            border-radius: var(--border-radius-sm); 
            border: 1px solid var(--gray-200); 
        }
        
        .stock-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        
        .stock-table th { 
            background: linear-gradient(135deg, var(--primary), var(--secondary)); 
            color: white; 
            padding: 15px; 
            text-align: left; 
            position: sticky; 
            top: 0; 
        }
        
        .stock-table td { padding: 15px; border-bottom: 1px solid var(--gray-200); }
        .stock-table tr:hover { background: rgba(67,97,238,0.05); }
        
        .ethiopian-date-cell { 
            font-family: 'Nyala', 'Segoe UI', sans-serif;
            font-weight: 600; 
            white-space: nowrap; 
            color: var(--dark);
        }
        
        .gregorian-time-cell { 
            font-family: monospace; 
            font-weight: 600; 
            white-space: nowrap; 
            color: var(--primary);
        }
        
        .date-title { 
            font-size: 1.2rem; 
            font-weight: 700; 
            margin-bottom: 15px; 
            padding-bottom: 10px; 
            border-bottom: 2px solid var(--gray-200); 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        
        .today-badge { background: linear-gradient(135deg, var(--success), var(--success-dark)); color: white; padding: 5px 15px; border-radius: 20px; }
        .yesterday-badge { background: linear-gradient(135deg, var(--warning), #ff9500); color: white; padding: 5px 15px; border-radius: 20px; }
        
        .empty-state { text-align: center; padding: 60px 20px; color: var(--gray-600); }
        .empty-state i { font-size: 3rem; margin-bottom: 15px; color: #ccc; }
        
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: var(--gray-200); border-radius: 4px; }
        ::-webkit-scrollbar-thumb { background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 4px; }
        
        @media (max-width:768px){ 
            .price-row, .quantity-unit-group { grid-template-columns: 1fr; } 
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="header-content">
                <div class="header-title">
                    <h1>
                        <img src="icon.png" style="width:40px;height:40px;border-radius:10px;">
                        ምርት መቀበያ
                        <span class="role-badge"><i class="fas fa-user-shield"></i> አድሚን</span>
                    </h1>
                    <span class="ethiopian-date-badge"><i class="fas fa-calendar-alt"></i> <?php echo $today_display; ?> ዓ.ም</span>
                    <span class="gregorian-time-badge" id="liveGregorianTime"><i class="fas fa-clock"></i> <?php echo $current_gregorian_time; ?></span>
                    <span class="branch-badge"><i class="fas fa-store"></i> <?php echo htmlspecialchars($branch_name); ?></span>
                </div>
                
                <div class="user-info">
                    <div class="avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                    <div>
                        <div style="font-weight:800;"><?php echo htmlspecialchars($user_name); ?></div>
                        <div style="font-size:0.85rem;">የምርት መቀበያ</div>
                    </div>
                </div>
                
                <div class="header-actions">
                    <button class="refresh-btn" onclick="refreshStockData()"><i class="fas fa-sync-alt"></i> አድስ</button>
                    <a href="admin_dashboard.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> ወደ ዳሽቦርድ</a>
                </div>
            </div>
        </div>
        
        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type'] ?? 'success'; ?>">
                <i class="fas fa-<?php echo ($_SESSION['message_type'] ?? 'success') == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $_SESSION['message']; unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
            </div>
        <?php endif; ?>
        
        <div class="stats-bar">
            <div class="stat-card">
                <i class="fas fa-calendar-day" style="font-size:2rem; color:var(--primary);"></i>
                <div class="stat-number"><?php echo count($today_stock); ?></div>
                <div class="stat-label">የዛሬ ምርቶች</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-balance-scale" style="font-size:2rem; color:var(--success-dark);"></i>
                <div class="stat-number"><?php echo number_format($total_today_quantity, 1); ?></div>
                <div class="stat-label">የዛሬ ጠቅላላ ብዛት</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-history" style="font-size:2rem; color:var(--warning);"></i>
                <div class="stat-number"><?php echo count($all_stock); ?></div>
                <div class="stat-label">ጠቅላላ የተቀበሉ</div>
            </div>
        </div>
        
        <div class="dashboard-content">
            <div class="form-panel">
                <h2 class="panel-title"><i class="fas fa-plus-circle" style="color:var(--gold);"></i> አዲስ ምርት መቀበያ</h2>
                <form method="POST" action="" id="stockForm">
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> የእቃው ስም</label>
                        <input type="text" name="item_name" id="item_name" class="form-control" required placeholder="የእቃውን ስም ያስገቡ" list="productList" autocomplete="off" autofocus>
                        <datalist id="productList">
                            <?php foreach($products_list as $p): ?>
                                <option value="<?php echo htmlspecialchars($p['name']); ?>" data-price="<?php echo $p['unit_price']; ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    
                    <div class="price-row">
                        <div class="form-group">
                            <label><i class="fas fa-shopping-cart"></i> የግዢ ዋጋ (ብር)</label>
                            <input type="number" name="buy_price" id="buy_price" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-tag"></i> የሽያጭ ዋጋ (ብር)</label>
                            <input type="number" name="sell_price" id="sell_price" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-balance-scale"></i> ብዛት እና መለኪያ</label>
                        <div class="quantity-unit-group">
                            <input type="number" name="quantity" id="quantity" class="form-control" step="0.1" min="0.1" required placeholder="ብዛት">
                            <select name="unit" id="unit" class="form-control" required>
                                <option value="">መለኪያ</option>
                                <option value="pcs">በፍሬ</option>
                                <option value="kg">በኪሎ</option>
                                <option value="g">ግራም</option>
                                <option value="l">ሊትር</option>
                                <option value="pack">ፓኬጅ</option>
                                <option value="box">ካርቶን</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-sticky-note"></i> ማስታወሻ</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="ማስታወሻ ካለ ያስገቡ (አማራጭ)"></textarea>
                    </div>
                    
                    <button type="submit" name="add_stock" class="submit-btn">
                        <i class="fas fa-check-circle"></i> መዝግብ
                    </button>
                </form>
            </div>
            
            <div class="history-panel">
                <h2 class="panel-title"><i class="fas fa-history"></i> የተመዘገቡ ምርቶች</h2>
                
                <?php if($table_exists): ?>
                <div class="tabs">
                    <button class="tab-btn active" onclick="switchTab('today')">
                        <i class="fas fa-calendar-day"></i> ዛሬ (<?php echo count($today_stock); ?>)
                    </button>
                    <button class="tab-btn" onclick="switchTab('yesterday')">
                        <i class="fas fa-calendar-minus"></i> ትናንት (<?php echo count($yesterday_stock); ?>)
                    </button>
                    <button class="tab-btn" onclick="switchTab('all')">
                        <i class="fas fa-calendar-alt"></i> ሁሉም (<?php echo count($all_stock); ?>)
                    </button>
                </div>
                
                <div id="todayTab" class="tab-content active">
                    <div class="date-title">
                        <i class="fas fa-calendar-day"></i> የዛሬ ምርቶች 
                        <span class="today-badge"><?php echo $today_display; ?></span>
                    </div>
                    
                    <?php if(!empty($today_stock)): ?>
                        <div class="stock-table-container">
                            <table class="stock-table">
                                <thead>
                                    <tr>
                                        <th>እቃ</th>
                                        <th>ብዛት</th>
                                        <th>መለኪያ</th>
                                        <th>የግዢ ዋጋ</th>
                                        <th>የሽያጭ ዋጋ</th>
                                        <th>ማስታወሻ</th>
                                        <th>የኢትዮጵያ ቀን</th>
                                        <th>ሰዓት (12-ሰዓት)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($today_stock as $s): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($s['item_name']); ?></strong></td>
                                        <td><span style="font-weight:700;color:var(--success-dark);"><?php echo number_format($s['quantity'], 1); ?></span></td>
                                        <td><?php echo $s['unit']; ?></td>
                                        <td><?php echo number_format($s['buy_price'], 2); ?> ብር</td>
                                        <td><?php echo number_format($s['sell_price'], 2); ?> ብር</td>
                                        <td><small><?php echo htmlspecialchars(substr($s['notes'] ?? '', 0, 30)); ?></small></td>
                                        <td class="ethiopian-date-cell"><?php echo $s['ethiopian_date']; ?></td>
                                        <td class="gregorian-time-cell"><?php echo $s['gregorian_time']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <h3>ዛሬ ምንም አልተመዘገበም</h3>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div id="yesterdayTab" class="tab-content">
                    <div class="date-title">
                        <i class="fas fa-calendar-minus"></i> የትናንት ምርቶች
                        <span class="yesterday-badge"><?php echo date('Y-m-d', strtotime('-1 day')); ?></span>
                    </div>
                    
                    <?php if(!empty($yesterday_stock)): ?>
                        <div class="stock-table-container">
                            <table class="stock-table">
                                <thead>
                                    <tr>
                                        <th>እቃ</th>
                                        <th>ብዛት</th>
                                        <th>መለኪያ</th>
                                        <th>የግዢ ዋጋ</th>
                                        <th>የሽያጭ ዋጋ</th>
                                        <th>ማስታወሻ</th>
                                        <th>የኢትዮጵያ ቀን</th>
                                        <th>ሰዓት (12-ሰዓት)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($yesterday_stock as $s): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($s['item_name']); ?></strong></td>
                                        <td><span style="font-weight:700;color:var(--success-dark);"><?php echo number_format($s['quantity'], 1); ?></span></td>
                                        <td><?php echo $s['unit']; ?></td>
                                        <td><?php echo number_format($s['buy_price'], 2); ?> ብር</td>
                                        <td><?php echo number_format($s['sell_price'], 2); ?> ብር</td>
                                        <td><small><?php echo htmlspecialchars(substr($s['notes'] ?? '', 0, 30)); ?></small></td>
                                        <td class="ethiopian-date-cell"><?php echo $s['ethiopian_date']; ?></td>
                                        <td class="gregorian-time-cell"><?php echo $s['gregorian_time']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <h3>ትናንት ምንም አልተመዘገበም</h3>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div id="allTab" class="tab-content">
                    <div class="date-title">
                        <i class="fas fa-calendar-alt"></i> ሁሉም ምርቶች
                    </div>
                    
                    <?php if(!empty($all_stock)): ?>
                        <div class="stock-table-container">
                            <table class="stock-table">
                                <thead>
                                    <tr>
                                        <th>እቃ</th>
                                        <th>ብዛት</th>
                                        <th>መለኪያ</th>
                                        <th>የግዢ ዋጋ</th>
                                        <th>የሽያጭ ዋጋ</th>
                                        <th>ማስታወሻ</th>
                                        <th>የኢትዮጵያ ቀን</th>
                                        <th>ሰዓት (12-ሰዓት)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($all_stock as $s): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($s['item_name']); ?></strong></td>
                                        <td><span style="font-weight:700;color:var(--success-dark);"><?php echo number_format($s['quantity'], 1); ?></span></td>
                                        <td><?php echo $s['unit']; ?></td>
                                        <td><?php echo number_format($s['buy_price'], 2); ?> ብር</td>
                                        <td><?php echo number_format($s['sell_price'], 2); ?> ብር</td>
                                        <td><small><?php echo htmlspecialchars(substr($s['notes'] ?? '', 0, 30)); ?></small></td>
                                        <td class="ethiopian-date-cell"><?php echo $s['ethiopian_date']; ?></td>
                                        <td class="gregorian-time-cell"><?php echo $s['gregorian_time']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-history"></i>
                            <h3>ምንም ታሪክ የለም</h3>
                        </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-database"></i>
                        <h3>stock_receive ሰንጠረዥ አልተገኘም</h3>
                        <p>እባክዎ ከላይ ያለውን SQL ያስኪዱ</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(tab + 'Tab').classList.add('active');
            
            const buttons = document.querySelectorAll('.tab-btn');
            for(let i = 0; i < buttons.length; i++) {
                if(buttons[i].textContent.includes(tab === 'today' ? 'ዛሬ' : tab === 'yesterday' ? 'ትናንት' : 'ሁሉም')) {
                    buttons[i].classList.add('active');
                    break;
                }
            }
        }
        
        document.getElementById('item_name').addEventListener('input', function() {
            const inputVal = this.value;
            const options = document.querySelectorAll('#productList option');
            options.forEach(option => {
                if (option.value === inputVal && option.getAttribute('data-price')) {
                    document.getElementById('sell_price').value = option.getAttribute('data-price');
                }
            });
        });
        
        document.getElementById('stockForm').addEventListener('submit', function(e) {
            const quantity = parseFloat(document.getElementById('quantity').value);
            if (quantity <= 0) {
                e.preventDefault();
                alert('እባክዎ ትክክለኛ ቁጥር ያስገቡ');
            }
        });
        
        function updateGregorianTime() {
            const now = new Date();
            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            const timeElement = document.querySelector('#liveGregorianTime');
            if (timeElement) {
                timeElement.innerHTML = `<i class="fas fa-clock"></i> ${hours}:${minutes}:${seconds} ${ampm}`;
            }
        }
        setInterval(updateGregorianTime, 1000);
        
        function refreshStockData() {
            const refreshBtn = document.querySelector('.refresh-btn');
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> በማደስ ላይ...';
            refreshBtn.disabled = true;
            setTimeout(() => { location.reload(); }, 500);
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('item_name').focus();
        });
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>