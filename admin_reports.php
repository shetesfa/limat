<?php
session_start();
require_once 'config.php';

mysqli_set_charset($conn, "utf8mb4");

if (!isLoggedIn()) redirect('index.php');

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'seller';
$current_user = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User';

$user_branch = getUserBranch($conn, $user_id);
$branch_id = getCurrentBranchId($conn, $user_id, $user_role);
$branch_name = getCurrentBranchName($conn, $branch_id);

// ========== ETHIOPIAN DATE FUNCTION (ACCURATE) ==========
function get_ethiopian_date_from_gregorian($gregorianDate) {
    if (empty($gregorianDate)) return '';
    
    $timestamp = strtotime($gregorianDate);
    $greg_year = (int)date('Y', $timestamp);
    $greg_month = (int)date('m', $timestamp);
    $greg_day = (int)date('d', $timestamp);
    
    $ethiopian_months = [
        1 => ['start' => '09-11', 'name' => 'መስከረም'],
        2 => ['start' => '10-11', 'name' => 'ጥቅምት'],
        3 => ['start' => '11-10', 'name' => 'ኅዳር'],
        4 => ['start' => '12-10', 'name' => 'ታኅሣሥ'],
        5 => ['start' => '01-09', 'name' => 'ጥር'],
        6 => ['start' => '02-08', 'name' => 'የካቲት'],
        7 => ['start' => '03-10', 'name' => 'መጋቢት'],
        8 => ['start' => '04-09', 'name' => 'ሚያዝያ'],
        9 => ['start' => '05-09', 'name' => 'ግንቦት'],
        10 => ['start' => '06-08', 'name' => 'ሰኔ'],
        11 => ['start' => '07-08', 'name' => 'ሐምሌ'],
        12 => ['start' => '08-07', 'name' => 'ነሐሴ'],
        13 => ['start' => '09-06', 'name' => 'ጳጉሜ']
    ];
    
    $ethiopian_year = $greg_year - 8;
    if ($greg_month >= 9 || ($greg_month == 9 && $greg_day >= 11)) {
        $ethiopian_year++;
    }
    
    $current_date = $greg_month . '-' . $greg_day;
    $eth_month = 1;
    $eth_day = 1;
    
    for ($i = 1; $i <= 13; $i++) {
        $month_start = $ethiopian_months[$i]['start'];
        if ($current_date >= $month_start) {
            if ($i == 13) {
                $next_year_first_month = $ethiopian_months[1]['start'];
                if ($current_date < $next_year_first_month) {
                    $eth_month = $i;
                    list($next_month, $next_day) = explode('-', $next_year_first_month);
                    $greg_next_date = strtotime($greg_year . '-' . $next_month . '-' . $next_day);
                    $greg_current = strtotime($greg_year . '-' . $greg_month . '-' . $greg_day);
                    $eth_day = (int)(($greg_next_date - $greg_current) / (60 * 60 * 24));
                    break;
                }
            } else {
                $next_month_start = $ethiopian_months[$i + 1]['start'];
                if ($current_date < $next_month_start) {
                    $eth_month = $i;
                    list($start_month, $start_day) = explode('-', $month_start);
                    $greg_start = strtotime($greg_year . '-' . $start_month . '-' . $start_day);
                    $greg_current = strtotime($greg_year . '-' . $greg_month . '-' . $greg_day);
                    $eth_day = (int)(($greg_current - $greg_start) / (60 * 60 * 24)) + 1;
                    break;
                }
            }
        }
    }
    
    if ($eth_month == 0) $eth_month = 1;
    if ($eth_day == 0) $eth_day = 1;
    
    $monthName = $ethiopian_months[$eth_month]['name'] ?? '';
    return $eth_day . ' ' . $monthName . ' ' . $ethiopian_year;
}

function formatTime12HourEthiopian($datetime) {
    $timestamp = strtotime($datetime);
    $ethiopian_timestamp = $timestamp + (3 * 3600);
    return date('h:i A', $ethiopian_timestamp);
}

function safe_html($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function format_currency($amount) {
    return number_format($amount, 2) . ' ብር';
}

function format_with_unit($quantity, $unit) {
    if (empty($unit)) return number_format($quantity, 2) . ' ክፍል';
    
    $unit_map = [
        'kg' => 'ኪግ',
        'l' => 'ሊትር',
        'pcs' => 'ክፍል',
        'piece' => 'ክፍል',
        'm' => 'ሜትር',
        'cm' => 'ሴሜ',
        'g' => 'ግራም'
    ];
    
    $amharic_unit = $unit_map[$unit] ?? $unit;
    return number_format($quantity, 2) . ' ' . $amharic_unit;
}

// ========== FILTER PERIOD ==========
$period = $_GET['period'] ?? '1month';
$view = $_GET['view'] ?? 'overview';
$custom_start = $_GET['start_date'] ?? '';
$custom_end = $_GET['end_date'] ?? '';

$today = date('Y-m-d');

$period_options = [
    '3days' => ['days' => 3, 'text' => 'ባለፉት 3 ቀናት'],
    '1week' => ['days' => 7, 'text' => 'ባለፉት 1 ሳምንት'],
    '2weeks' => ['days' => 14, 'text' => 'ባለፉት 2 ሳምንታት'],
    '3weeks' => ['days' => 21, 'text' => 'ባለፉት 3 ሳምንታት'],
    '1month' => ['days' => 30, 'text' => 'ባለፉት 1 ወር'],
    '2months' => ['days' => 60, 'text' => 'ባለፉት 2 ወራት'],
    '3months' => ['days' => 90, 'text' => 'ባለፉት 3 ወራት'],
    '6months' => ['days' => 180, 'text' => 'ባለፉት 6 ወራት'],
    '9months' => ['days' => 270, 'text' => 'ባለፉት 9 ወራት'],
    '1year' => ['days' => 365, 'text' => 'ባለፉት 1 አመት'],
    'custom' => ['days' => 0, 'text' => 'ብጁ ቀን']
];

if ($period == 'custom' && !empty($custom_start) && !empty($custom_end)) {
    $date_from = $custom_start;
    $date_to = $custom_end;
    $period_text = "ከ $custom_start እስከ $custom_end";
    $prev_date_from = date('Y-m-d', strtotime($custom_start . ' -' . (strtotime($custom_end) - strtotime($custom_start)) . ' days'));
    $prev_date_to = $custom_start;
} elseif (isset($period_options[$period])) {
    $days = $period_options[$period]['days'];
    $date_from = date('Y-m-d', strtotime("-{$days} days"));
    $date_to = $today;
    $period_text = $period_options[$period]['text'];
    $prev_date_from = date('Y-m-d', strtotime("-" . ($days * 2) . " days"));
    $prev_date_to = date('Y-m-d', strtotime("-" . ($days + 1) . " days"));
} else {
    $period = '1month';
    $date_from = date('Y-m-d', strtotime('-30 days'));
    $date_to = $today;
    $period_text = 'ባለፉት 1 ወር';
    $prev_date_from = date('Y-m-d', strtotime('-60 days'));
    $prev_date_to = date('Y-m-d', strtotime('-31 days'));
}

$date_from_esc = mysqli_real_escape_string($conn, $date_from);
$date_to_esc = mysqli_real_escape_string($conn, $date_to);
$prev_date_from_esc = mysqli_real_escape_string($conn, $prev_date_from);
$prev_date_to_esc = mysqli_real_escape_string($conn, $prev_date_to);

// ========== 1. SUMMARY STATS ==========
$sales_query = "SELECT COUNT(DISTINCT id) as total_transactions, COALESCE(SUM(total_amount), 0) as total_revenue, COALESCE(AVG(total_amount), 0) as avg_transaction FROM transactions WHERE DATE(transaction_date) BETWEEN '$date_from_esc' AND '$date_to_esc' AND branch_id = $branch_id";
$sales_data = mysqli_fetch_assoc(mysqli_query($conn, $sales_query));

$total_sales = $sales_data['total_revenue'];

$prev_sales_query = "SELECT COALESCE(SUM(total_amount), 0) as prev_revenue FROM transactions WHERE DATE(transaction_date) BETWEEN '$prev_date_from_esc' AND '$prev_date_to_esc' AND branch_id = $branch_id";
$prev_revenue = mysqli_fetch_assoc(mysqli_query($conn, $prev_sales_query))['prev_revenue'] ?? 0;

$current_revenue = $total_sales;
if ($prev_revenue > 0) {
    $growth = (($current_revenue - $prev_revenue) / $prev_revenue) * 100;
} else {
    $growth = $current_revenue > 0 ? 100 : 0;
}

$today_query = "SELECT COALESCE(SUM(total_amount), 0) as today_revenue FROM transactions WHERE DATE(transaction_date) = CURDATE() AND branch_id = $branch_id";
$today_data = mysqli_fetch_assoc(mysqli_query($conn, $today_query));

// ========== 2. DAILY TRENDS FOR CHART ==========
$daily_query = "SELECT DATE(transaction_date) as date, COUNT(DISTINCT id) as transactions, COALESCE(SUM(total_amount), 0) as revenue FROM transactions WHERE DATE(transaction_date) BETWEEN '$date_from_esc' AND '$date_to_esc' AND branch_id = $branch_id GROUP BY DATE(transaction_date) ORDER BY date";
$daily_result = mysqli_query($conn, $daily_query);

$chart_dates = [];
$chart_revenue = [];
$chart_transactions = [];

while ($row = mysqli_fetch_assoc($daily_result)) {
    $chart_dates[] = $row['date'];
    $chart_revenue[] = $row['revenue'];
    $chart_transactions[] = $row['transactions'];
}

// ========== 3. SELLER PERFORMANCE ==========
$seller_perf_query = "SELECT seller_name, seller_id, COALESCE(SUM(total_amount), 0) as revenue, COUNT(*) as txns FROM transactions WHERE DATE(transaction_date) BETWEEN '$date_from_esc' AND '$date_to_esc' AND branch_id = $branch_id GROUP BY seller_id, seller_name ORDER BY revenue DESC";
$seller_perf_result = mysqli_query($conn, $seller_perf_query);

// ========== 4. TOP SELLING PRODUCTS ==========
$top_products_query = "SELECT ti.product_name, COUNT(DISTINCT t.id) as times_sold, COALESCE(SUM(ti.quantity), 0) as total_quantity, COALESCE(SUM(ti.subtotal), 0) as total_revenue FROM transaction_items ti JOIN transactions t ON ti.transaction_id = t.id WHERE DATE(t.transaction_date) BETWEEN '$date_from_esc' AND '$date_to_esc' AND t.branch_id = $branch_id GROUP BY ti.product_name ORDER BY total_revenue DESC LIMIT 10";
$top_products_result = mysqli_query($conn, $top_products_query);

// ========== 5. PRODUCT PERFORMANCE (FIXED - NO DUPLICATE PRODUCTS) ==========
$product_performance_query = "SELECT 
    ti.product_name,
    COALESCE(SUM(ti.quantity), 0) as total_sold_quantity,
    COUNT(DISTINCT t.id) as times_sold,
    COALESCE(SUM(ti.subtotal), 0) as total_revenue,
    COALESCE(AVG(ti.unit_price), 0) as avg_selling_price,
    COALESCE(MIN(ti.unit_price), 0) as min_selling_price,
    COALESCE(MAX(ti.unit_price), 0) as max_selling_price
    FROM transaction_items ti
    INNER JOIN transactions t ON ti.transaction_id = t.id 
        AND DATE(t.transaction_date) BETWEEN '$date_from_esc' AND '$date_to_esc'
        AND t.branch_id = $branch_id
    GROUP BY ti.product_name
    HAVING total_sold_quantity > 0
    ORDER BY total_revenue DESC";

$product_perf_result = mysqli_query($conn, $product_performance_query);

// Get inventory data for units (separate query to avoid duplication)
$inventory_data = [];
$inv_query = "SELECT item_name, unit, price FROM seller_inventory WHERE branch_id = $branch_id";
$inv_result = mysqli_query($conn, $inv_query);
if ($inv_result) {
    while ($inv = mysqli_fetch_assoc($inv_result)) {
        $inventory_data[$inv['item_name']] = $inv;
    }
}

// ========== 6. RECENT ACTIVITY (SALES ONLY) ==========
$recent_activity = [];

$recent_sales_query = "SELECT 
    'sale' as type,
    id,
    seller_name,
    CAST(total_amount AS DECIMAL(10,2)) as amount,
    transaction_date as date
    FROM transactions 
    WHERE DATE(transaction_date) BETWEEN '$date_from_esc' AND '$date_to_esc'
    AND branch_id = $branch_id
    ORDER BY transaction_date DESC
    LIMIT 20";
$recent_sales_result = mysqli_query($conn, $recent_sales_query);
while ($row = mysqli_fetch_assoc($recent_sales_result)) {
    $row['ethiopian_date'] = get_ethiopian_date_from_gregorian($row['date']);
    $row['time_12hr'] = formatTime12HourEthiopian($row['date']);
    $recent_activity[] = $row;
}
?>

<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ሪፖርቶች - አጸደ ትጉሃን ሰንበት ትምህርት ቤት</title>
    <link rel="icon" type="image/png" href="icon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', 'Nyala', sans-serif; 
            background: radial-gradient(circle at center, #F6E27A 0%, #F4A640 35%, #D96B2B 65%, #7A1E1E 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 1400px; margin: 0 auto; }
        
        .header {
            background: white; border-radius: 15px; padding: 25px; margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); display: flex; justify-content: space-between;
            align-items: center; flex-wrap: wrap; gap: 15px;
        }
        .header h1 { color: #333; font-size: 24px; display: flex; align-items: center; gap: 10px; }
        .header h1 i { color: #DAA520; }
        .branch-badge {
            background: linear-gradient(135deg, #8B4513, #DAA520); color: white; padding: 8px 20px;
            border-radius: 30px; font-size: 14px; font-weight: 600; display: inline-flex;
            align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(139,69,19,0.3);
        }
        .back-btn {
            background: linear-gradient(135deg, #8B4513, #DAA520); color: white; border: none;
            padding: 12px 25px; border-radius: 10px; cursor: pointer; font-size: 16px; font-weight: 600;
            text-decoration: none; display: flex; align-items: center; gap: 8px; transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(139,69,19,0.3);
        }
        .back-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(139,69,19,0.4); }
        
        .period-selector {
            display: flex; gap: 8px; flex-wrap: wrap; background: white; padding: 15px;
            border-radius: 15px; margin-bottom: 20px; box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }
        .period-btn {
            padding: 10px 16px; border: 2px solid #e0d5c1; background: white; border-radius: 10px;
            cursor: pointer; font-size: 13px; font-weight: 600; color: #555; text-decoration: none;
            transition: all 0.3s; white-space: nowrap;
        }
        .period-btn:hover { border-color: #DAA520; color: #8B4513; transform: translateY(-2px); }
        .period-btn.active {
            background: linear-gradient(135deg, #8B4513, #DAA520); color: white; border-color: #8B4513;
        }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-card {
            background: white; padding: 20px; border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1); transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card .stat-icon {
            width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center;
            justify-content: center; font-size: 22px; margin-bottom: 12px; color: white;
        }
        .stat-card:nth-child(1) .stat-icon { background: linear-gradient(135deg, #8B4513, #DAA520); }
        .stat-card:nth-child(2) .stat-icon { background: linear-gradient(135deg, #28a745, #20c997); }
        .stat-label { color: #718096; font-size: 13px; margin-bottom: 5px; }
        .stat-value { font-size: 24px; font-weight: 700; color: #2d3748; }
        .stat-sub { font-size: 12px; color: #a0aec0; margin-top: 5px; }
        .growth-positive { color: #28a745; font-weight: 600; }
        .growth-negative { color: #dc3545; font-weight: 600; }
        
        .card {
            background: white; border-radius: 15px; padding: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1); margin-bottom: 20px;
        }
        .card h3 { color: #8B4513; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; font-size: 18px; }
        
        .view-tabs {
            display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; background: white;
            padding: 10px; border-radius: 15px; box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }
        .view-tab {
            padding: 12px 20px; border: none; background: transparent; border-radius: 10px;
            cursor: pointer; font-size: 14px; font-weight: 500; color: #4a5568; text-decoration: none;
            transition: all 0.3s;
        }
        .view-tab.active { background: linear-gradient(135deg, #8B4513, #DAA520); color: white; }
        .view-tab i { margin-right: 6px; }
        
        .chart-container { height: 350px; position: relative; }
        
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { background: linear-gradient(135deg, #8B4513, #DAA520); color: white; padding: 12px; text-align: left; font-weight: 600; }
        td { padding: 12px; border-bottom: 1px solid #f0f0f0; color: #555; }
        tr:hover { background: #FFF8DC; }
        
        .rank-badge {
            width: 30px; height: 30px; background: linear-gradient(135deg, #8B4513, #DAA520);
            color: white; border-radius: 50%; display: inline-flex; align-items: center;
            justify-content: center; font-size: 12px; font-weight: 700;
        }
        
        .type-badge {
            padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .type-sale { background: #e6ffe6; color: #2e7d32; }
        
        .summary-row {
            background: #FFF8DC; font-weight: 700; border-top: 3px solid #DAA520;
        }
        
        .unit-badge {
            font-size: 10px; color: #888; margin-left: 3px;
        }
        
        .progress-bar { height: 8px; background: #e0e0e0; border-radius: 4px; margin-top: 5px; overflow: hidden; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #DAA520, #8B4513); border-radius: 4px; }
        
        .action-buttons { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 20px; }
        .btn-action {
            padding: 12px 25px; border: none; border-radius: 10px; cursor: pointer; font-size: 14px;
            font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
            transition: all 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.2); }
        .btn-primary { background: linear-gradient(135deg, #8B4513, #DAA520); color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-success { background: #28a745; color: white; }
        
        .no-data { text-align: center; padding: 40px; color: #999; }
        .no-data i { font-size: 48px; margin-bottom: 15px; }
        
        .table-responsive { overflow-x: auto; }
        
        @media (max-width: 768px) {
            .header { flex-direction: column; text-align: center; }
            .stats-grid { grid-template-columns: 1fr; }
            .chart-container { height: 250px; }
            .period-selector { justify-content: center; }
            .view-tabs { justify-content: center; }
            table { font-size: 12px; }
            th, td { padding: 8px 6px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>
                <img src="icon.png" style="width:35px;height:35px;border-radius:8px;" alt="Logo">
                የሽያጭ ሪፖርት
                <span class="branch-badge"><i class="fas fa-store"></i> <?php echo safe_html($branch_name); ?></span>
            </h1>
            <a href="<?php echo $user_role == 'admin' ? 'admin_dashboard.php' : 'seller_pos.php'; ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> ተመለስ
            </a>
        </div>
        
        <!-- Period Selector -->
        <div class="period-selector">
            <?php foreach ($period_options as $key => $opt): ?>
                <?php if ($key != 'custom'): ?>
                <a href="?period=<?php echo $key; ?>&view=<?php echo $view; ?>" class="period-btn <?php echo $period == $key ? 'active' : ''; ?>">
                    <?php echo $opt['text']; ?>
                </a>
                <?php endif; ?>
            <?php endforeach; ?>
            <a href="?period=custom&view=<?php echo $view; ?>" class="period-btn <?php echo $period == 'custom' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i> ብጁ
            </a>
        </div>
        
        <!-- Custom Date Filter -->
        <?php if ($period == 'custom'): ?>
        <div class="card">
            <form method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                <input type="hidden" name="period" value="custom">
                <input type="hidden" name="view" value="<?php echo $view; ?>">
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #555;">የመጀመሪያ ቀን</label>
                    <input type="date" name="start_date" value="<?php echo $custom_start; ?>" style="width: 100%; padding: 10px; border: 2px solid #e0d5c1; border-radius: 10px;">
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #555;">የመጨረሻ ቀን</label>
                    <input type="date" name="end_date" value="<?php echo $custom_end; ?>" style="width: 100%; padding: 10px; border: 2px solid #e0d5c1; border-radius: 10px;">
                </div>
                <button type="submit" class="btn-action btn-primary"><i class="fas fa-search"></i> አሳይ</button>
            </form>
        </div>
        <?php endif; ?>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="stat-label">ጠቅላላ ሽያጭ</div>
                <div class="stat-value"><?php echo format_currency($total_sales); ?></div>
                <div class="stat-sub"><?php echo $period_text; ?> | <?php echo $sales_data['total_transactions']; ?> ግብይቶች</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-sun"></i></div>
                <div class="stat-label">የዛሬ ሽያጭ</div>
                <div class="stat-value" style="color:#28a745;"><?php echo format_currency($today_data['today_revenue'] ?? 0); ?></div>
                <div class="stat-sub">
                    <span class="<?php echo $growth >= 0 ? 'growth-positive' : 'growth-negative'; ?>">
                        <i class="fas fa-<?php echo $growth >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                        <?php echo ($growth >= 0 ? '+' : '') . round($growth, 1); ?>%
                    </span> ካለፈው ጊዜ
                </div>
            </div>
        </div>
        
        <!-- Chart -->
        <div class="card">
            <h3><i class="fas fa-chart-line"></i> ዕለታዊ የሽያጭ አዝማሚያ - <?php echo $period_text; ?></h3>
            <?php if (!empty($chart_dates)): ?>
            <div class="chart-container"><canvas id="salesChart"></canvas></div>
            <?php else: ?>
            <div class="no-data"><i class="fas fa-chart-line"></i><p>በዚህ ጊዜ ውስጥ ምንም ውሂብ አልተገኘም</p></div>
            <?php endif; ?>
        </div>
        
        <!-- View Tabs -->
        <div class="view-tabs">
            <a href="?period=<?php echo $period; ?>&view=overview<?php echo $period=='custom'?'&start_date='.$custom_start.'&end_date='.$custom_end:''; ?>" class="view-tab <?php echo $view=='overview'?'active':''; ?>">
                <i class="fas fa-eye"></i> አጠቃላይ
            </a>
            <a href="?period=<?php echo $period; ?>&view=sellers<?php echo $period=='custom'?'&start_date='.$custom_start.'&end_date='.$custom_end:''; ?>" class="view-tab <?php echo $view=='sellers'?'active':''; ?>">
                <i class="fas fa-users"></i> ሻጮች
            </a>
            <a href="?period=<?php echo $period; ?>&view=products<?php echo $period=='custom'?'&start_date='.$custom_start.'&end_date='.$custom_end:''; ?>" class="view-tab <?php echo $view=='products'?'active':''; ?>">
                <i class="fas fa-box"></i> ምርጥ ምርቶች
            </a>
            <a href="?period=<?php echo $period; ?>&view=product_performance<?php echo $period=='custom'?'&start_date='.$custom_start.'&end_date='.$custom_end:''; ?>" class="view-tab <?php echo $view=='product_performance'?'active':''; ?>">
                <i class="fas fa-chart-bar"></i> የምርቶች አፈጻጸም
            </a>
        </div>
        
        <!-- ==================== VIEW: OVERVIEW ==================== -->
        <?php if ($view == 'overview'): ?>
        <div class="card" id="export-section">
            <h3><i class="fas fa-calculator"></i> የሽያጭ ማጠቃለያ</h3>
            <table>
                <tr><td><strong>ጠቅላላ ሽያጭ</strong></td><td style="color:#28a745;font-weight:700;"><?php echo format_currency($total_sales); ?></td></tr>
                <tr><td><strong>ጠቅላላ ግብይቶች</strong></td><td><?php echo $sales_data['total_transactions']; ?></td></tr>
                <tr><td><strong>አማካይ ግብይት</strong></td><td><?php echo format_currency($sales_data['avg_transaction']); ?></td></tr>
                <tr><td><strong>የዛሬ ሽያጭ</strong></td><td style="color:#28a745;font-weight:700;"><?php echo format_currency($today_data['today_revenue'] ?? 0); ?></td></tr>
                <tr style="border-top:3px solid #DAA520;font-weight:700;font-size:1.1rem;">
                    <td><strong>የእድገት መጠን</strong></td>
                    <td style="color:<?php echo $growth>=0?'#28a745':'#dc3545'; ?>;">
                        <?php echo ($growth>=0?'+ ':'- ') . abs(round($growth, 1)) . '%'; ?>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Recent Activity -->
        <div class="card">
            <h3><i class="fas fa-history"></i> የቅርብ ጊዜ ሽያጮች</h3>
            <?php if (!empty($recent_activity)): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ሻጭ</th>
                            <th>መጠን</th>
                            <th>የኢትዮጵያ ቀን</th>
                            <th>ሰዓት</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_activity as $activity): ?>
                        <tr>
                            <td>
                                <span class="type-badge type-sale"><i class="fas fa-shopping-cart"></i></span>
                                <?php echo safe_html($activity['seller_name'] ?? 'ሥርዓት'); ?>
                            </td>
                            <td style="color:#28a745;font-weight:700;"><?php echo format_currency($activity['amount']); ?></td>
                            <td><?php echo safe_html($activity['ethiopian_date'] ?? ''); ?></td>
                            <td><?php echo safe_html($activity['time_12hr'] ?? ''); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="no-data"><i class="fas fa-history"></i><p>ምንም ሽያጭ አልተገኘም</p></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- ==================== VIEW: SELLERS ==================== -->
        <?php if ($view == 'sellers'): ?>
        <div class="card" id="export-section">
            <h3><i class="fas fa-medal"></i> የሻጭ አፈጻጸም - <?php echo $period_text; ?></h3>
            <?php if ($seller_perf_result && mysqli_num_rows($seller_perf_result) > 0): 
                $max_revenue = 0;
                $seller_rows = [];
                mysqli_data_seek($seller_perf_result, 0);
                while($sp = mysqli_fetch_assoc($seller_perf_result)) {
                    $seller_rows[] = $sp;
                    if ($sp['revenue'] > $max_revenue) $max_revenue = $sp['revenue'];
                }
            ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ሻጭ</th>
                            <th>ገቢ</th>
                            <th>የሽያጭ ብዛት</th>
                            <th>አፈጻጸም</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rank = 1; foreach ($seller_rows as $sp): 
                            $percentage = $max_revenue > 0 ? ($sp['revenue'] / $max_revenue) * 100 : 0;
                        ?>
                        <tr>
                            <td><span class="rank-badge"><?php echo $rank++; ?></span></td>
                            <td><strong><?php echo safe_html($sp['seller_name']); ?></strong></td>
                            <td style="color:#28a745;font-weight:700;"><?php echo format_currency($sp['revenue']); ?></td>
                            <td><?php echo $sp['txns']; ?> ጊዜ</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <small><?php echo round($percentage, 1); ?>%</small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="no-data"><i class="fas fa-users"></i><p>ምንም ውሂብ አልተገኘም</p></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- ==================== VIEW: TOP PRODUCTS ==================== -->
        <?php if ($view == 'products'): ?>
        <div class="card" id="export-section">
            <h3><i class="fas fa-trophy" style="color:#DAA520;"></i> ከፍተኛ ሽያጭ ያላቸው ምርቶች</h3>
            <?php if ($top_products_result && mysqli_num_rows($top_products_result) > 0): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ምርት</th>
                            <th>የተሸጠ ጊዜ</th>
                            <th>ጠቅላላ ብዛት</th>
                            <th>ገቢ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rank = 1; while($tp = mysqli_fetch_assoc($top_products_result)): ?>
                        <tr>
                            <td><span class="rank-badge"><?php echo $rank++; ?></span></td>
                            <td><strong><?php echo safe_html($tp['product_name']); ?></strong></td>
                            <td><?php echo $tp['times_sold']; ?> ጊዜ</td>
                            <td><?php echo number_format($tp['total_quantity'], 2); ?></td>
                            <td style="color:#28a745;font-weight:700;"><?php echo format_currency($tp['total_revenue']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="no-data"><i class="fas fa-box"></i><p>ምንም ውሂብ አልተገኘም</p></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- ==================== VIEW: PRODUCT PERFORMANCE (FIXED) ==================== -->
        <?php if ($view == 'product_performance'): ?>
        <div class="card" id="export-section">
            <h3><i class="fas fa-chart-pie"></i> የምርቶች አፈጻጸም - <?php echo safe_html($branch_name); ?></h3>
            <span class="period-btn active" style="display:inline-block;margin-bottom:15px;"><?php echo $period_text; ?></span>
            <?php 
            $total_sales_all = 0;
            $product_count = 0;
            if ($product_perf_result && mysqli_num_rows($product_perf_result) > 0): 
            ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ምርት</th>
                            <th>የተሸጠው ብዛት</th>
                            <th>የሽያጭ ጊዜ</th>
                            <th>ጠቅላላ ገቢ</th>
                            <th>አማካይ ዋጋ</th>
                            <th>የዋጋ ልዩነት</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = mysqli_fetch_assoc($product_perf_result)): 
                            $price_variation = $product['max_selling_price'] - $product['min_selling_price'];
                            $total_sales_all += $product['total_revenue'];
                            $product_count++;
                            
                            // Get unit and base price from separate inventory data
                            $inv_info = $inventory_data[$product['product_name']] ?? null;
                            $unit = $inv_info['unit'] ?? '';
                            $base_price = $inv_info['price'] ?? 0;
                        ?>
                        <tr>
                            <td><strong><?php echo safe_html($product['product_name']); ?></strong></td>
                            <td><?php echo format_with_unit($product['total_sold_quantity'], $unit); ?></td>
                            <td><?php echo (int)$product['times_sold']; ?> ጊዜ</td>
                            <td style="color:#28a745;font-weight:700;"><?php echo format_currency($product['total_revenue']); ?></td>
                            <td>
                                <?php echo format_currency($product['avg_selling_price']); ?>
                                <?php if ($base_price > 0): ?>
                                    <br><small class="unit-badge">የተቀመጠ: <?php echo format_currency($base_price); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($price_variation > 0): ?>
                                    <span style="color: #3b82f6; font-weight: 600;">
                                        <?php echo format_currency($product['min_selling_price']); ?> - <?php echo format_currency($product['max_selling_price']); ?>
                                    </span>
                                <?php else: ?>
                                    ቋሚ
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <!-- Summary Row -->
                        <tr class="summary-row">
                            <td><strong>ድምር</strong></td>
                            <td><strong><?php echo $product_count; ?> ምርቶች</strong></td>
                            <td>-</td>
                            <td><strong style="color:#28a745;"><?php echo format_currency($total_sales_all); ?></strong></td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="no-data">
                <i class="fas fa-chart-bar"></i>
                <p>በዚህ ጊዜ ውስጥ ምንም የተሸጡ ምርቶች የሉም</p>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Action Buttons -->
        <div class="action-buttons">
            <button class="btn-action btn-success" onclick="exportAllToExcel()">
                <i class="fas fa-file-excel"></i> ሙሉ ሪፖርት እንደ ኤክሴል አውርድ
            </button>
            <button class="btn-action btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> አትም
            </button>
            <a href="<?php echo $user_role=='admin'?'admin_dashboard.php':'seller_pos.php'; ?>" class="btn-action btn-secondary">
                <i class="fas fa-arrow-left"></i> ተመለስ
            </a>
        </div>
    </div>
    
    <script>
    // ========== CHART ==========
    <?php if (!empty($chart_dates)): ?>
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_dates); ?>,
            datasets: [{
                label: 'ዕለታዊ ሽያጭ (ብር)',
                data: <?php echo json_encode($chart_revenue); ?>,
                borderColor: '#DAA520',
                backgroundColor: 'rgba(218, 165, 32, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: '#8B4513',
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'ሽያጭ: ' + new Intl.NumberFormat().format(context.raw) + ' ብር';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat().format(value) + ' ብር';
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>
    
    // ========== EXPORT ALL DATA TO EXCEL ==========
    function exportAllToExcel() {
        let fullHtml = '<html><head><meta charset="UTF-8">';
        fullHtml += '<style>';
        fullHtml += 'body { font-family: Arial, sans-serif; }';
        fullHtml += 'h2 { color: #8B4513; border-bottom: 2px solid #DAA520; padding-bottom: 5px; }';
        fullHtml += 'h3 { color: #333; }';
        fullHtml += 'table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }';
        fullHtml += 'th { background: #8B4513; color: white; padding: 10px; text-align: left; border: 1px solid #ddd; }';
        fullHtml += 'td { padding: 8px; border: 1px solid #ddd; }';
        fullHtml += 'tr:nth-child(even) { background: #f9f9f9; }';
        fullHtml += '.summary-row { background: #FFF8DC; font-weight: bold; }';
        fullHtml += '</style></head><body>';
        
        fullHtml += '<h2>ሙሉ የሽያጭ ሪፖርት</h2>';
        fullHtml += '<p><strong>ቅርንጫፍ:</strong> <?php echo addslashes($branch_name); ?></p>';
        fullHtml += '<p><strong>ጊዜ:</strong> <?php echo addslashes($period_text); ?></p>';
        fullHtml += '<p><strong>ቀን:</strong> <?php echo date("Y-m-d H:i:s"); ?></p>';
        fullHtml += '<hr>';
        
        // Summary
        fullHtml += '<h3>የሽያጭ ማጠቃለያ</h3>';
        fullHtml += '<table>';
        fullHtml += '<tr><td>ጠቅላላ ሽያጭ</td><td><?php echo addslashes(format_currency($total_sales)); ?></td></tr>';
        fullHtml += '<tr><td>ጠቅላላ ግብይቶች</td><td><?php echo $sales_data["total_transactions"]; ?></td></tr>';
        fullHtml += '<tr><td>አማካይ ግብይት</td><td><?php echo addslashes(format_currency($sales_data["avg_transaction"])); ?></td></tr>';
        fullHtml += '<tr><td>የዛሬ ሽያጭ</td><td><?php echo addslashes(format_currency($today_data["today_revenue"] ?? 0)); ?></td></tr>';
        fullHtml += '<tr class="summary-row"><td>የእድገት መጠን</td><td><?php echo ($growth>=0?"+":"-").abs(round($growth,1))."%"; ?></td></tr>';
        fullHtml += '</table>';
        
        // Seller Performance
        fullHtml += '<h3>የሻጭ አፈጻጸም</h3>';
        <?php if ($seller_perf_result && mysqli_num_rows($seller_perf_result) > 0): 
            mysqli_data_seek($seller_perf_result, 0);
        ?>
        fullHtml += '<table><thead><tr><th>ሻጭ</th><th>ገቢ</th><th>የሽያጭ ብዛት</th></tr></thead><tbody>';
        <?php while($sp = mysqli_fetch_assoc($seller_perf_result)): ?>
        fullHtml += '<tr><td><?php echo addslashes($sp["seller_name"]); ?></td><td><?php echo addslashes(format_currency($sp["revenue"])); ?></td><td><?php echo $sp["txns"]; ?> ጊዜ</td></tr>';
        <?php endwhile; ?>
        fullHtml += '</tbody></table>';
        <?php endif; ?>
        
        // Top Products
        fullHtml += '<h3>ከፍተኛ ሽያጭ ያላቸው ምርቶች</h3>';
        <?php if ($top_products_result && mysqli_num_rows($top_products_result) > 0): 
            mysqli_data_seek($top_products_result, 0);
        ?>
        fullHtml += '<table><thead><tr><th>ምርት</th><th>የተሸጠ ጊዜ</th><th>ጠቅላላ ብዛት</th><th>ገቢ</th></tr></thead><tbody>';
        <?php while($tp = mysqli_fetch_assoc($top_products_result)): ?>
        fullHtml += '<tr><td><?php echo addslashes($tp["product_name"]); ?></td><td><?php echo $tp["times_sold"]; ?> ጊዜ</td><td><?php echo number_format($tp["total_quantity"],2); ?></td><td><?php echo addslashes(format_currency($tp["total_revenue"])); ?></td></tr>';
        <?php endwhile; ?>
        fullHtml += '</tbody></table>';
        <?php endif; ?>
        
        // Product Performance
        fullHtml += '<h3>የምርቶች አፈጻጸም</h3>';
        <?php if ($product_perf_result && mysqli_num_rows($product_perf_result) > 0): 
            mysqli_data_seek($product_perf_result, 0);
        ?>
        fullHtml += '<table><thead><tr><th>ምርት</th><th>የተሸጠው ብዛት</th><th>የሽያጭ ጊዜ</th><th>ጠቅላላ ገቢ</th><th>አማካይ ዋጋ</th><th>ዝቅተኛ ዋጋ</th><th>ከፍተኛ ዋጋ</th></tr></thead><tbody>';
        <?php 
        $export_total = 0;
        while($pp = mysqli_fetch_assoc($product_perf_result)): 
            $export_total += $pp["total_revenue"];
            $inv = $inventory_data[$pp["product_name"]] ?? null;
            $pp_unit = $inv["unit"] ?? "";
            $pp_base = $inv["price"] ?? 0;
        ?>
        fullHtml += '<tr><td><?php echo addslashes($pp["product_name"]); ?></td><td><?php echo number_format($pp["total_sold_quantity"],2); ?> <?php echo addslashes($pp_unit); ?></td><td><?php echo $pp["times_sold"]; ?> ጊዜ</td><td><?php echo addslashes(format_currency($pp["total_revenue"])); ?></td><td><?php echo addslashes(format_currency($pp["avg_selling_price"])); ?></td><td><?php echo addslashes(format_currency($pp["min_selling_price"])); ?></td><td><?php echo addslashes(format_currency($pp["max_selling_price"])); ?></td></tr>';
        <?php endwhile; ?>
        fullHtml += '<tr class="summary-row"><td><strong>ድምር</strong></td><td>-</td><td>-</td><td><strong><?php // This will be replaced by the actual total in JS ?></strong></td><td>-</td><td>-</td><td>-</td></tr>';
        fullHtml += '</tbody></table>';
        <?php endif; ?>
        
        // Recent Sales
        fullHtml += '<h3>የቅርብ ጊዜ ሽያጮች</h3>';
        <?php if (!empty($recent_activity)): ?>
        fullHtml += '<table><thead><tr><th>ሻጭ</th><th>መጠን</th><th>የኢትዮጵያ ቀን</th><th>ሰዓት</th></tr></thead><tbody>';
        <?php foreach ($recent_activity as $act): ?>
        fullHtml += '<tr><td><?php echo addslashes($act["seller_name"] ?? "ሥርዓት"); ?></td><td><?php echo addslashes(format_currency($act["amount"])); ?></td><td><?php echo addslashes($act["ethiopian_date"] ?? ""); ?></td><td><?php echo addslashes($act["time_12hr"] ?? ""); ?></td></tr>';
        <?php endforeach; ?>
        fullHtml += '</tbody></table>';
        <?php endif; ?>
        
        fullHtml += '</body></html>';
        
        // Download
        const blob = new Blob([fullHtml], {type: 'application/vnd.ms-excel;charset=utf-8'});
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; 
        a.download = 'full_report_<?php echo date("Y-m-d"); ?>.xls'; 
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
    </script>
</body>
</html>
<?php 
// Clean up
if (isset($daily_result) && $daily_result) mysqli_free_result($daily_result);
if (isset($seller_perf_result) && $seller_perf_result) mysqli_free_result($seller_perf_result);
if (isset($top_products_result) && $top_products_result) mysqli_free_result($top_products_result);
if (isset($product_perf_result) && $product_perf_result) mysqli_free_result($product_perf_result);
if (isset($inv_result) && $inv_result) mysqli_free_result($inv_result);
if (isset($recent_sales_result) && $recent_sales_result) mysqli_free_result($recent_sales_result);
mysqli_close($conn); 
?>