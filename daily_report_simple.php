<?php
// daily_report_simple.php - Daily Transaction Report with Navigation
session_start();
require_once 'config.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'seller';

// Get branch info
$branch_id = getCurrentBranchId($conn, $user_id, $user_role);
$branch_name = getCurrentBranchName($conn, $branch_id);

// ========== ETHIOPIAN CALENDAR FUNCTIONS ==========
function gregorian_to_ethiopian($gregorian_date) {
    try {
        $date = new DateTime($gregorian_date, new DateTimeZone('Africa/Addis_Ababa'));
        
        $year = (int)$date->format('Y');
        $month = (int)$date->format('m');
        $day = (int)$date->format('d');
        $hour = (int)$date->format('H');
        $minute = (int)$date->format('i');
        
        $ethiopian_months = [
            "መስከረም", "ጥቅምት", "ህዳር", "ታህሳስ", "ጥር", "የካቲት",
            "መጋቢት", "ሚያዝያ", "ግንቦት", "ሰኔ", "ሐምሌ", "ነሐሴ", "ጳጉሜ"
        ];
        
        $ethiopian_year = $year - 8;
        
        $is_leap_year = (($year % 4 == 0) && ($year % 100 != 0)) || ($year % 400 == 0);
        $new_year_day = $is_leap_year ? 12 : 11;
        
        if ($month > 9 || ($month == 9 && $day >= $new_year_day)) {
            $ethiopian_year = $year - 7;
        }
        
        $new_year_date = new DateTime("$year-09-{$new_year_day}", new DateTimeZone('Africa/Addis_Ababa'));
        
        if ($month < 9 || ($month == 9 && $day < $new_year_day)) {
            $new_year_date = new DateTime(($year - 1) . "-09-{$new_year_day}", new DateTimeZone('Africa/Addis_Ababa'));
        }
        
        $diff = $date->diff($new_year_date);
        $days_from_new_year = $diff->days;
        
        $ethiopian_month = floor($days_from_new_year / 30) + 1;
        $ethiopian_day = ($days_from_new_year % 30) + 1;
        
        if ($ethiopian_month == 13) {
            $max_pagume_days = ($ethiopian_year % 4 == 3) ? 6 : 5;
            $ethiopian_day = min($ethiopian_day, $max_pagume_days);
        }
        
        if ($ethiopian_month > 13) {
            $ethiopian_month = 13;
        }
        
        return [
            'year' => $ethiopian_year,
            'month' => $ethiopian_month,
            'month_name' => $ethiopian_months[$ethiopian_month - 1] ?? '',
            'day' => $ethiopian_day,
            'full_date' => sprintf("%d-%02d-%02d", $ethiopian_year, $ethiopian_month, $ethiopian_day),
            'time' => sprintf("%02d:%02d", $hour, $minute)
        ];
    } catch (Exception $e) {
        return [
            'year' => 2018,
            'month' => 6,
            'month_name' => 'የካቲት',
            'day' => 21,
            'full_date' => '2018-06-21',
            'time' => date('H:i')
        ];
    }
}

function ethiopian_to_gregorian($ethiopian_date) {
    try {
        list($year, $month, $day) = explode('-', $ethiopian_date);
        $year = (int)$year;
        $month = (int)$month;
        $day = (int)$day;
        
        $gregorian_year = $year + 7;
        
        $is_eth_leap = ($year % 4 == 3);
        $new_year_day = $is_eth_leap ? 12 : 11;
        
        $gregorian_date = new DateTime("$gregorian_year-09-$new_year_day", new DateTimeZone('Africa/Addis_Ababa'));
        
        $days_to_add = (($month - 1) * 30) + ($day - 1);
        if ($days_to_add > 0) {
            $gregorian_date->modify("+{$days_to_add} days");
        }
        
        return $gregorian_date->format('Y-m-d');
    } catch (Exception $e) {
        return '2026-02-28';
    }
}

// Get current Ethiopian date
$current_ethiopian_time = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));
$current_gregorian_for_eth = $current_ethiopian_time->format('Y-m-d H:i:s');
$current_ethiopian = gregorian_to_ethiopian($current_gregorian_for_eth);

// Handle date selection
$selected_date = isset($_GET['date']) ? $_GET['date'] : $current_ethiopian['full_date'];

// Parse selected date
list($sel_year, $sel_month, $sel_day) = explode('-', $selected_date);
$sel_year = (int)$sel_year;
$sel_month = (int)$sel_month;
$sel_day = (int)$sel_day;

// Calculate previous day
$prev_day = $sel_day - 1;
$prev_month = $sel_month;
$prev_year = $sel_year;

if ($prev_day < 1) {
    $prev_month--;
    if ($prev_month < 1) {
        $prev_month = 13;
        $prev_year--;
    }
    $days_in_prev_month = ($prev_month == 13) ? (($prev_year % 4 == 3) ? 6 : 5) : 30;
    $prev_day = $days_in_prev_month;
}
$prev_date = sprintf("%d-%02d-%02d", $prev_year, $prev_month, $prev_day);

// Calculate next day
$days_in_current_month = ($sel_month == 13) ? (($sel_year % 4 == 3) ? 6 : 5) : 30;
$next_day = $sel_day + 1;
$next_month = $sel_month;
$next_year = $sel_year;

if ($next_day > $days_in_current_month) {
    $next_month++;
    $next_day = 1;
    if ($next_month > 13) {
        $next_month = 1;
        $next_year++;
    }
}
$next_date = sprintf("%d-%02d-%02d", $next_year, $next_month, $next_day);

// Month names
$ethiopian_months = [
    1 => "መስከረም", 2 => "ጥቅምት", 3 => "ህዳር", 4 => "ታህሳስ",
    5 => "ጥር", 6 => "የካቲት", 7 => "መጋቢት", 8 => "ሚያዝያ",
    9 => "ግንቦት", 10 => "ሰኔ", 11 => "ሐምሌ", 12 => "ነሐሴ", 13 => "ጳጉሜ"
];

// Get date range for selected day
$start_greg = ethiopian_to_gregorian($selected_date) . ' 00:00:00';
$end_greg = ethiopian_to_gregorian($selected_date) . ' 23:59:59';

// Fetch all transactions for selected day with branch filter
$query = "SELECT 
            t.id,
            t.transaction_date,
            t.payment_method,
            t.seller_name,
            ti.product_name,
            ti.quantity,
            ti.unit_price,
            ti.subtotal
          FROM transactions t
          INNER JOIN transaction_items ti ON t.id = ti.transaction_id
          WHERE t.transaction_date BETWEEN '$start_greg' AND '$end_greg'
          AND t.branch_id = $branch_id
          ORDER BY t.transaction_date ASC";

$result = mysqli_query($conn, $query);

$all_rows = [];
$daily_total = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $eth = gregorian_to_ethiopian($row['transaction_date']);
    $row['eth_time'] = $eth['time'];
    $all_rows[] = $row;
    $daily_total += $row['subtotal'];
}

// Handle Excel download
if (isset($_GET['export_excel'])) {
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="Daily_Report_' . $branch_name . '_' . $selected_date . '.xls"');
    
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head><meta charset="UTF-8"></head>';
    echo '<body>';
    echo '<table border="1">';
    
    // Title
    echo '<tr><th colspan="8" style="background:#667eea; color:white; font-size:16px; padding:10px;">';
    echo 'ቅርንጫፍ: ' . htmlspecialchars($branch_name) . ' | የ' . $sel_day . ' ' . $ethiopian_months[$sel_month] . ' ' . $sel_year . ' ዕለታዊ ሪፖርት';
    echo '</th></tr>';
    
    // Headers
    echo '<tr style="background:#4CAF50; color:white;">';
    echo '<th>ደረሰኝ #</th>';
    echo '<th>ሰዓት</th>';
    echo '<th>ሻጭ</th>';
    echo '<th>ክፍያ</th>';
    echo '<th>ዕቃ</th>';
    echo '<th>ብዛት</th>';
    echo '<th>አንድ ዋጋ</th>';
    echo '<th>ጠቅላላ</th>';
    echo '</tr>';
    
    foreach ($all_rows as $row) {
        echo '<tr>';
        echo '<td>#' . str_pad($row['id'], 6, '0', STR_PAD_LEFT) . '</td>';
        echo '<td>' . $row['eth_time'] . '</td>';
        echo '<td>' . htmlspecialchars($row['seller_name']) . '</td>';
        echo '<td>' . ucfirst($row['payment_method']) . '</td>';
        echo '<td>' . htmlspecialchars($row['product_name']) . '</td>';
        echo '<td align="right">' . number_format($row['quantity'], 2) . '</td>';
        echo '<td align="right">' . number_format($row['unit_price'], 2) . '</td>';
        echo '<td align="right">' . number_format($row['subtotal'], 2) . '</td>';
        echo '</tr>';
    }
    
    // Total row
    echo '<tr style="background:#e8f5e9; font-weight:bold;">';
    echo '<td colspan="7" align="right">የዕለቱ ጠቅላላ ድምር:</td>';
    echo '<td align="right"><strong>' . number_format($daily_total, 2) . '</strong></td>';
    echo '</tr>';
    
    echo '</table>';
    echo '</body></html>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpg" href="image/photo_2026-01-12_07-44-10.jpg">
    <title>ዕለታዊ ሪፖርት - <?php echo htmlspecialchars($branch_name); ?> | <?php echo $sel_day . ' ' . $ethiopian_months[$sel_month] . ' ' . $sel_year; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --success: #4CAF50;
            --info: #2196F3;
            --warning: #ff9800;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            background: radial-gradient(circle at center, #F6E27A 0%, #F4A640 35%, #D96B2B 65%, #7A1E1E 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            border-radius: 15px;
            padding: 20px 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .header h1 {
            color: #333;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .header h1 i {
            color: var(--primary);
        }
        
        .branch-badge {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-info {
            background: var(--info);
            color: white;
        }
        
        .btn-warning {
            background: var(--warning);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .nav-card {
            background: white;
            border-radius: 15px;
            padding: 15px 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .date-display {
            font-size: 22px;
            font-weight: 700;
            color: #333;
            text-align: center;
        }
        
        .date-display .eth-date {
            color: var(--primary);
        }
        
        .today-badge {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .nav-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .info-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            background: #f8f9ff;
        }
        
        .info-item i {
            font-size: 30px;
            margin-bottom: 10px;
            color: var(--primary);
        }
        
        .info-item .value {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        
        .info-item .label {
            font-size: 12px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .table-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            overflow-x: auto;
        }
        
        .table-card h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        
        thead th {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 12px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        
        tbody td {
            padding: 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        tbody tr:hover {
            background: #f8f9ff;
        }
        
        tbody tr:nth-child(even) {
            background: #fafafa;
        }
        
        tbody tr:nth-child(even):hover {
            background: #f0f0ff;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .receipt-id {
            font-weight: 700;
            color: var(--primary);
            white-space: nowrap;
        }
        
        .payment-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .payment-cash { background: #e8f5e9; color: #2e7d32; }
        .payment-telebirr { background: #fff3e0; color: #f57c00; }
        .payment-cbe { background: #f3e5f5; color: #7b1fa2; }
        .payment-abyssinia { background: #e3f2fd; color: #1565c0; }
        
        .total-row {
            background: #e8f5e9 !important;
            font-weight: 700;
            font-size: 15px;
        }
        
        .total-row td {
            border-top: 2px solid var(--success);
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .no-data i {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
            color: #ddd;
        }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid var(--info);
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #1565c0;
        }
        
        @media (max-width: 768px) {
            body { padding: 10px; }
            .header { flex-direction: column; text-align: center; }
            .nav-card { flex-direction: column; }
            .date-display { font-size: 18px; }
            .nav-buttons { justify-content: center; }
            .btn { padding: 8px 15px; font-size: 12px; }
            .info-card { grid-template-columns: 1fr; }
            table { font-size: 11px; }
        }
        
        @media print {
            body { background: white; }
            .header, .nav-card, .info-box, .btn, .nav-buttons { display: none !important; }
            .table-card { box-shadow: none; border: none; }
            .info-card { display: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1>
                    <i class="fas fa-chart-bar"></i> ዕለታዊ ሪፖርት
                </h1>
                <span class="branch-badge">
                    <i class="fas fa-store"></i> <?php echo htmlspecialchars($branch_name); ?>
                </span>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="history.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> ወደ ታሪክ ተመለስ
                </a>
                <a href="seller_pos.php" class="btn btn-success">
                    <i class="fas fa-cash-register"></i> መሸጫ
                </a>
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="nav-card">
            <a href="?date=<?php echo $prev_date; ?>" class="btn btn-primary">
                <i class="fas fa-chevron-left"></i> ትናንት
            </a>
            
            <div class="date-display">
                <span class="eth-date"><?php echo $sel_day . ' ' . $ethiopian_months[$sel_month] . ' ' . $sel_year; ?></span>
                <?php if ($selected_date == $current_ethiopian['full_date']): ?>
                    <span class="today-badge"><i class="fas fa-star"></i> ዛሬ</span>
                <?php endif; ?>
            </div>
            
            <a href="?date=<?php echo $next_date; ?>" class="btn btn-primary">
                ነገ <i class="fas fa-chevron-right"></i>
            </a>
            
            <div class="nav-buttons">
                <?php if ($selected_date != $current_ethiopian['full_date']): ?>
                <a href="?date=<?php echo $current_ethiopian['full_date']; ?>" class="btn btn-info">
                    <i class="fas fa-calendar-check"></i> ዛሬ
                </a>
                <?php endif; ?>
                <button onclick="window.print()" class="btn btn-warning">
                    <i class="fas fa-print"></i> ማተም
                </button>
                <a href="?export_excel=1&date=<?php echo $selected_date; ?>" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> ኤክሴል
                </a>
            </div>
        </div>
        
        <!-- Info message when viewing non-today date -->
        <?php if ($selected_date != $current_ethiopian['full_date']): ?>
        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            እየተመለከቱ ያሉት የቀን <strong><?php echo $sel_day . ' ' . $ethiopian_months[$sel_month] . ' ' . $sel_year; ?></strong> ግብይቶች ነው።
            ወደ ዛሬ ለመመለስ "ዛሬ" የሚለውን ይጫኑ።
        </div>
        <?php endif; ?>
        
        <!-- Summary Cards -->
        <div class="info-card">
            <div class="info-item">
                <i class="fas fa-receipt"></i>
                <div class="value"><?php echo count($all_rows); ?></div>
                <div class="label">ጠቅላላ ዕቃዎች</div>
            </div>
            <div class="info-item">
                <i class="fas fa-money-bill-wave"></i>
                <div class="value"><?php echo number_format($daily_total, 2); ?></div>
                <div class="label">ጠቅላላ ብር (ETB)</div>
            </div>
            <div class="info-item">
                <i class="fas fa-calendar-day"></i>
                <div class="value"><?php echo $sel_day; ?></div>
                <div class="label"><?php echo $ethiopian_months[$sel_month] . ' ' . $sel_year; ?></div>
            </div>
        </div>
        
        <!-- Transactions Table -->
        <div class="table-card">
            <h2>
                <i class="fas fa-list-alt"></i> 
                የ<?php echo $sel_day . ' ' . $ethiopian_months[$sel_month] . ' ' . $sel_year; ?> ግብይቶች
            </h2>
            
            <?php if (empty($all_rows)): ?>
            <div class="no-data">
                <i class="fas fa-box-open"></i>
                <h3>በዚህ ቀን ምንም ግብይት የለም</h3>
                <p><?php echo $sel_day . ' ' . $ethiopian_months[$sel_month] . ' ' . $sel_year; ?></p>
            </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ደረሰኝ #</th>
                        <th>ሰዓት</th>
                        <th>ሻጭ</th>
                        <th>ክፍያ</th>
                        <th>ዕቃ</th>
                        <th class="text-right">ብዛት</th>
                        <th class="text-right">ዋጋ</th>
                        <th class="text-right">ጠቅላላ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_rows as $row): 
                        $payment_class = 'payment-' . strtolower($row['payment_method']);
                    ?>
                    <tr>
                        <td class="receipt-id">#<?php echo str_pad($row['id'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo $row['eth_time']; ?></td>
                        <td><?php echo htmlspecialchars($row['seller_name']); ?></td>
                        <td>
                            <span class="payment-badge <?php echo $payment_class; ?>">
                                <?php echo ucfirst($row['payment_method']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td class="text-right"><?php echo number_format($row['quantity'], 2); ?></td>
                        <td class="text-right"><?php echo number_format($row['unit_price'], 2); ?></td>
                        <td class="text-right"><strong><?php echo number_format($row['subtotal'], 2); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="7" class="text-right">
                            <i class="fas fa-calculator"></i> የዕለቱ ጠቅላላ ድምር:
                        </td>
                        <td class="text-right"><?php echo number_format($daily_total, 2); ?> ETB</td>
                    </tr>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    // Auto-refresh only if viewing today
    <?php if ($selected_date == $current_ethiopian['full_date']): ?>
    console.log('Auto-refresh enabled - viewing today');
    setTimeout(() => {
        location.reload();
    }, 60000); // Refresh every 60 seconds
    <?php endif; ?>
    </script>
</body>
</html>
<?php 
if (isset($result)) mysqli_free_result($result);
mysqli_close($conn); 
?>