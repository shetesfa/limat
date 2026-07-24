<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

mysqli_set_charset($conn, "utf8mb4");

$user_id = $_SESSION['user_id'];
$current_user = $_SESSION['full_name'] ?? $_SESSION['username'] ?? "User";
$user_role = $_SESSION['role'] ?? 'seller';

// Get branch info - using functions from config.php
$branch_id = getCurrentBranchId($conn, $user_id, $user_role);
$branch_name = getCurrentBranchName($conn, $branch_id);

/**
 * Ethiopian calendar conversion
 */
function gregorian_to_ethiopian($gregorian_datetime) {
    try {
        if (strlen($gregorian_datetime) == 10) {
            $gregorian_datetime .= ' 00:00:00';
        }
        
        $date = new DateTime($gregorian_datetime, new DateTimeZone('Africa/Addis_Ababa'));
        
        $year = (int)$date->format('Y');
        $month = (int)$date->format('m');
        $day = (int)$date->format('d');
        $hour = (int)$date->format('H');
        $minute = (int)$date->format('i');
        $second = (int)$date->format('s');
        
        $ethiopian_months = [
            "መስከረም", "ጥቅምት", "ህዳር", "ታህሳስ", "ጥር", "የካቲት",
            "መጋቢት", "ሚያዝያ", "ግንቦት", "ሰኔ", "ሐምሌ", "ነሐሴ", "ጳጉሜ"
        ];
        
        $is_greg_leap = ($year % 4 == 0 && ($year % 100 != 0 || $year % 400 == 0));
        $new_year_day = $is_greg_leap ? 12 : 11;
        
        if ($month < 9 || ($month == 9 && $day < $new_year_day)) {
            $ethiopian_year = $year - 8;
        } else {
            $ethiopian_year = $year - 7;
        }
        
        $new_year = new DateTime($year . '-09-' . $new_year_day . ' 00:00:00', new DateTimeZone('Africa/Addis_Ababa'));
        
        if ($date < $new_year) {
            $prev_year = $year - 1;
            $is_prev_greg_leap = ($prev_year % 4 == 0 && ($prev_year % 100 != 0 || $prev_year % 400 == 0));
            $prev_new_year_day = $is_prev_greg_leap ? 12 : 11;
            $new_year = new DateTime($prev_year . '-09-' . $prev_new_year_day . ' 00:00:00', new DateTimeZone('Africa/Addis_Ababa'));
        }
        
        $interval = $new_year->diff($date);
        $days_diff = $interval->days;
        
        $ethiopian_month = floor($days_diff / 30) + 1;
        $ethiopian_day = ($days_diff % 30) + 1;
        
        if ($ethiopian_month > 13) {
            $ethiopian_month = 13;
            $is_eth_leap = ($ethiopian_year % 4 == 3);
            $max_pagume_days = $is_eth_leap ? 6 : 5;
            if ($ethiopian_day > $max_pagume_days) {
                $ethiopian_day = $max_pagume_days;
            }
        }
        
        $hour_12 = $hour % 12;
        $hour_12 = $hour_12 == 0 ? 12 : $hour_12;
        $am_pm = $hour < 12 ? 'ጥዋት' : 'ከሰዓት';
        
        return [
            'year' => $ethiopian_year,
            'month' => $ethiopian_month,
            'month_name' => $ethiopian_months[$ethiopian_month - 1] ?? '',
            'day' => $ethiopian_day,
            'full_date' => sprintf("%d-%02d-%02d", $ethiopian_year, $ethiopian_month, $ethiopian_day),
            'hour' => $hour,
            'minute' => $minute,
            'second' => $second,
            'hour_12' => $hour_12,
            'am_pm' => $am_pm,
            'time_12h' => sprintf("%d:%02d:%02d %s", $hour_12, $minute, $second, $am_pm),
            'time_24h' => sprintf("%02d:%02d:%02d", $hour, $minute, $second)
        ];
        
    } catch (Exception $e) {
        return get_current_ethiopian_date();
    }
}

function get_current_ethiopian_date() {
    $ethiopia_time = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));
    return gregorian_to_ethiopian($ethiopia_time->format('Y-m-d H:i:s'));
}

function getEthiopianDayBoundaries($eth_date) {
    $parts = explode('-', $eth_date);
    $eth_year = (int)$parts[0];
    $eth_month = (int)$parts[1];
    $eth_day = (int)$parts[2];
    
    $greg_year = $eth_year + 7;
    
    $is_greg_leap = ($greg_year % 4 == 0 && ($greg_year % 100 != 0 || $greg_year % 400 == 0));
    $new_year_day = $is_greg_leap ? 12 : 11;
    
    $days_from_start = ($eth_month - 1) * 30 + ($eth_day - 1);
    
    $start_greg = new DateTime("$greg_year-09-$new_year_day 00:00:00", new DateTimeZone('Africa/Addis_Ababa'));
    
    if ($days_from_start > 0) {
        $start_greg->modify("+{$days_from_start} days");
    }
    
    $end_greg = clone $start_greg;
    $end_greg->modify('+1 day');
    $end_greg->modify('-1 second');
    
    return [
        'start_greg' => $start_greg->format('Y-m-d H:i:s'),
        'end_greg' => $end_greg->format('Y-m-d H:i:s')
    ];
}

$current_ethiopian = get_current_ethiopian_date();
$date_range = isset($_GET['date_range']) ? $_GET['date_range'] : 'today';

$start_date_greg = '';
$end_date_greg = '';
$start_date_eth = '';
$end_date_eth = '';

$today_boundaries = getEthiopianDayBoundaries($current_ethiopian['full_date']);
$today_start_greg = $today_boundaries['start_greg'];
$today_end_greg = $today_boundaries['end_greg'];

if ($date_range == 'today') {
    $start_date_greg = $today_start_greg;
    $end_date_greg = $today_end_greg;
    $start_date_eth = $current_ethiopian['full_date'];
    $end_date_eth = $current_ethiopian['full_date'];
} elseif ($date_range == 'yesterday') {
    $yesterday_eth = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));
    $yesterday_eth->modify('-1 day');
    $yesterday_eth_date = gregorian_to_ethiopian($yesterday_eth->format('Y-m-d H:i:s'));
    $yesterday_boundaries = getEthiopianDayBoundaries($yesterday_eth_date['full_date']);
    $start_date_greg = $yesterday_boundaries['start_greg'];
    $end_date_greg = $yesterday_boundaries['end_greg'];
    $start_date_eth = $yesterday_eth_date['full_date'];
    $end_date_eth = $yesterday_eth_date['full_date'];
} elseif ($date_range == '3day') {
    $end_date_greg = $today_boundaries['end_greg'];
    $end_date_eth = $current_ethiopian['full_date'];
    $start_eth_obj = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));
    $start_eth_obj->modify('-2 days');
    $start_eth = gregorian_to_ethiopian($start_eth_obj->format('Y-m-d H:i:s'));
    $start_boundaries = getEthiopianDayBoundaries($start_eth['full_date']);
    $start_date_greg = $start_boundaries['start_greg'];
    $start_date_eth = $start_eth['full_date'];
} elseif ($date_range == '7day') {
    $end_date_greg = $today_boundaries['end_greg'];
    $start_eth_obj = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));
    $start_eth_obj->modify('-6 days');
    $start_eth = gregorian_to_ethiopian($start_eth_obj->format('Y-m-d H:i:s'));
    $start_boundaries = getEthiopianDayBoundaries($start_eth['full_date']);
    $start_date_greg = $start_boundaries['start_greg'];
} elseif ($date_range == '14day') {
    $end_date_greg = $today_boundaries['end_greg'];
    $start_eth_obj = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));
    $start_eth_obj->modify('-13 days');
    $start_eth = gregorian_to_ethiopian($start_eth_obj->format('Y-m-d H:i:s'));
    $start_boundaries = getEthiopianDayBoundaries($start_eth['full_date']);
    $start_date_greg = $start_boundaries['start_greg'];
} elseif ($date_range == '21day') {
    $end_date_greg = $today_boundaries['end_greg'];
    $start_eth_obj = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));
    $start_eth_obj->modify('-20 days');
    $start_eth = gregorian_to_ethiopian($start_eth_obj->format('Y-m-d H:i:s'));
    $start_boundaries = getEthiopianDayBoundaries($start_eth['full_date']);
    $start_date_greg = $start_boundaries['start_greg'];
} elseif ($date_range == '30day') {
    $end_date_greg = $today_boundaries['end_greg'];
    $start_eth_obj = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));
    $start_eth_obj->modify('-30 days');
    $start_eth = gregorian_to_ethiopian($start_eth_obj->format('Y-m-d H:i:s'));
    $start_boundaries = getEthiopianDayBoundaries($start_eth['full_date']);
    $start_date_greg = $start_boundaries['start_greg'];
} elseif ($date_range == '60day') {
    $end_date_greg = $today_boundaries['end_greg'];
    $start_eth_obj = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));
    $start_eth_obj->modify('-60 days');
    $start_eth = gregorian_to_ethiopian($start_eth_obj->format('Y-m-d H:i:s'));
    $start_boundaries = getEthiopianDayBoundaries($start_eth['full_date']);
    $start_date_greg = $start_boundaries['start_greg'];
} elseif ($date_range == '90day') {
    $end_date_greg = $today_boundaries['end_greg'];
    $start_eth_obj = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));
    $start_eth_obj->modify('-90 days');
    $start_eth = gregorian_to_ethiopian($start_eth_obj->format('Y-m-d H:i:s'));
    $start_boundaries = getEthiopianDayBoundaries($start_eth['full_date']);
    $start_date_greg = $start_boundaries['start_greg'];
} elseif ($date_range == '180day') {
    $end_date_greg = $today_boundaries['end_greg'];
    $start_eth_obj = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));
    $start_eth_obj->modify('-180 days');
    $start_eth = gregorian_to_ethiopian($start_eth_obj->format('Y-m-d H:i:s'));
    $start_boundaries = getEthiopianDayBoundaries($start_eth['full_date']);
    $start_date_greg = $start_boundaries['start_greg'];
} elseif ($date_range == '365day') {
    $end_date_greg = $today_boundaries['end_greg'];
    $start_eth_obj = new DateTime('now', new DateTimeZone('Africa/Addis_Ababa'));
    $start_eth_obj->modify('-365 days');
    $start_eth = gregorian_to_ethiopian($start_eth_obj->format('Y-m-d H:i:s'));
    $start_boundaries = getEthiopianDayBoundaries($start_eth['full_date']);
    $start_date_greg = $start_boundaries['start_greg'];
} else {
    $start_date_greg = $today_start_greg;
    $end_date_greg = $today_end_greg;
}

function format_ethiopian_date($ethiopian_date, $include_time = false) {
    if (!$ethiopian_date) return '';
    $parts = explode('-', $ethiopian_date);
    $year = $parts[0];
    $month = $parts[1];
    $day = $parts[2];
    $ethiopian_months = ["መስከረም","ጥቅምት","ህዳር","ታህሳስ","ጥር","የካቲት","መጋቢት","ሚያዝያ","ግንቦት","ሰኔ","ሐምሌ","ነሐሴ","ጳጉሜ"];
    $month_name = $ethiopian_months[(int)$month - 1] ?? '';
    return "$day $month_name $year";
}

$display_start_date = format_ethiopian_date($start_date_eth);
$display_end_date = format_ethiopian_date($end_date_eth);

$filter_seller = isset($_GET['seller']) ? mysqli_real_escape_string($conn, $_GET['seller']) : '';
$filter_payment = isset($_GET['payment_method']) ? mysqli_real_escape_string($conn, $_GET['payment_method']) : '';
$search_item = isset($_GET['search_item']) ? mysqli_real_escape_string($conn, $_GET['search_item']) : '';
$search_receipt_id = isset($_GET['search_receipt_id']) ? mysqli_real_escape_string($conn, $_GET['search_receipt_id']) : '';

// Build main query
$query = "SELECT DISTINCT t.*, 
          (SELECT COUNT(*) FROM transaction_items ti WHERE ti.transaction_id = t.id) as item_count,
          t.transaction_date as raw_date
          FROM transactions t 
          LEFT JOIN transaction_items ti ON t.id = ti.transaction_id
          WHERE t.transaction_date BETWEEN '$start_date_greg' AND '$end_date_greg'
          AND t.branch_id = $branch_id";
          
if ($filter_seller) {
    $query .= " AND t.seller_name LIKE '%$filter_seller%'";
}
if ($filter_payment) {
    $query .= " AND t.payment_method = '$filter_payment'";
}
if ($search_item) {
    $query .= " AND ti.product_name LIKE '%$search_item%'";
}
if ($search_receipt_id) {
    $query .= " AND t.id = '$search_receipt_id'";
}

$query .= " ORDER BY t.id DESC";
$result = mysqli_query($conn, $query);

$transactions_with_eth_dates = [];
$total_transactions = 0;
$total_sales = 0;

while($row = mysqli_fetch_assoc($result)) {
    $eth_date = gregorian_to_ethiopian($row['raw_date']);
    $row['eth_date'] = $eth_date;
    $transactions_with_eth_dates[] = $row;
    $total_transactions++;
    $total_sales += $row['total_amount'];
}

// Payment summary
$payment_query = "SELECT t.payment_method, COUNT(DISTINCT t.id) as count, SUM(t.total_amount) as amount
                  FROM transactions t 
                  WHERE t.transaction_date BETWEEN '$start_date_greg' AND '$end_date_greg'
                  AND t.branch_id = $branch_id";
if ($search_item) {
    $payment_query .= " AND t.id IN (SELECT DISTINCT transaction_id FROM transaction_items WHERE product_name LIKE '%$search_item%')";
}
$payment_query .= " GROUP BY t.payment_method";
$payment_result = mysqli_query($conn, $payment_query);

// Daily data for chart
$daily_query = "SELECT DATE(t.transaction_date) as gregorian_day, COUNT(DISTINCT t.id) as transactions, SUM(t.total_amount) as total
                FROM transactions t 
                WHERE t.transaction_date BETWEEN '$start_date_greg' AND '$end_date_greg' AND t.branch_id = $branch_id
                GROUP BY DATE(t.transaction_date) ORDER BY gregorian_day";
$daily_result = mysqli_query($conn, $daily_query);

$daily_data = [];
while($day = mysqli_fetch_assoc($daily_result)) {
    $ethiopian_date = gregorian_to_ethiopian($day['gregorian_day'] . ' 12:00:00');
    $day['ethiopian_day'] = $ethiopian_date['day'] . ' ' . $ethiopian_date['month_name'];
    $daily_data[] = $day;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpg" href="image/photo_2026-01-12_07-44-10.jpg">
    <title>Sales History - Aleltu POS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: radial-gradient(circle at center, #F6E27A 0%, #F4A640 35%, #D96B2B 65%, #7A1E1E 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; }
        .header { background: white; border-radius: 15px; padding: 25px; margin-bottom: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        .header h1 { color: #333; font-size: 28px; display: flex; align-items: center; gap: 15px; }
        .header h1 i { color: #667eea; }
        .branch-badge { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 8px 20px; border-radius: 30px; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; margin-left: 15px; box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3); }
        .back-btn { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 12px 25px; border-radius: 10px; cursor: pointer; font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: transform 0.3s; text-decoration: none; }
        .back-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .daily-btn { background: #2196F3; color: white; border: none; padding: 12px 25px; border-radius: 10px; cursor: pointer; font-size: 16px; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: transform 0.3s; text-decoration: none; }
        .daily-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(33, 150, 243, 0.4); }
        .summary-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px; }
        .card { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); transition: transform 0.3s; display: flex; align-items: center; gap: 20px; }
        .card:hover { transform: translateY(-5px); }
        .card-icon { width: 70px; height: 70px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 28px; flex-shrink: 0; }
        .card:nth-child(1) .card-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .card:nth-child(2) .card-icon { background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%); color: white; }
        .card:nth-child(3) .card-icon { background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%); color: white; }
        .card-content { flex: 1; }
        .card h3 { color: #666; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .card .value { font-size: 32px; font-weight: 700; color: #333; margin-bottom: 5px; line-height: 1; }
        .card .subtext { font-size: 14px; color: #888; display: flex; align-items: center; gap: 5px; }
        .filters { background: white; border-radius: 15px; padding: 25px; margin-bottom: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
        .filters h2 { color: #333; margin-bottom: 20px; font-size: 20px; display: flex; align-items: center; gap: 10px; }
        .filter-row { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; margin-bottom: 15px; }
        .filter-group { flex: 1; min-width: 200px; }
        .filter-group label { display: block; font-weight: 600; color: #555; font-size: 14px; margin-bottom: 8px; }
        .filter-group input, .filter-group select { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; transition: border-color 0.3s; }
        .filter-group input:focus, .filter-group select:focus { outline: none; border-color: #667eea; }
        .date-buttons { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px; justify-content: center; }
        .date-btn { padding: 10px 15px; border: 2px solid #ddd; background: white; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; color: #555; transition: all 0.3s; flex: 1; min-width: 100px; text-align: center; white-space: nowrap; }
        .date-btn:hover { border-color: #667eea; color: #667eea; transform: translateY(-2px); }
        .date-btn.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-color: #667eea; }
        .filter-actions { display: flex; gap: 10px; margin-top: 20px; }
        .filter-btn { padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.3s; display: flex; align-items: center; gap: 8px; height: 44px; }
        .apply-btn { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .reset-btn { background: #f5f5f5; color: #666; }
        .filter-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .ethiopian-date { background: #fff8e1; border-radius: 8px; padding: 12px 20px; margin-bottom: 15px; text-align: center; border-left: 4px solid #f57c00; font-weight: 600; color: #5d4037; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; }
        .ethiopian-date .date-info { display: flex; align-items: center; gap: 10px; }
        .ethiopian-date .time-info { display: flex; align-items: center; gap: 10px; font-family: monospace; background: #5d4037; color: white; padding: 5px 15px; border-radius: 20px; font-size: 14px; }
        .chart-container { background: white; border-radius: 15px; padding: 25px; margin-bottom: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
        .chart-container h2 { color: #333; margin-bottom: 20px; font-size: 20px; display: flex; align-items: center; gap: 10px; }
        .chart-wrapper { height: 300px; position: relative; }
        .transactions-section { background: white; border-radius: 15px; padding: 25px; margin-bottom: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
        .section-header h2 { color: #333; font-size: 20px; display: flex; align-items: center; gap: 10px; }
        .section-buttons { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        .export-btn { background: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px; transition: all 0.3s; text-decoration: none; }
        .export-btn:hover { background: #388e3c; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3); }
        .daily-report-btn { background: #2196F3; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px; transition: all 0.3s; text-decoration: none; }
        .daily-report-btn:hover { background: #1976D2; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(33, 150, 243, 0.3); }
        .transactions-table { width: 100%; border-collapse: collapse; overflow: hidden; border-radius: 10px; }
        .transactions-table thead { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .transactions-table th { padding: 15px; text-align: left; font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
        .transactions-table tbody tr { border-bottom: 1px solid #f0f0f0; transition: background 0.3s; }
        .transactions-table tbody tr:hover { background: #f8f9ff; }
        .transactions-table td { padding: 15px; color: #555; font-size: 14px; }
        .transaction-id { font-weight: 700; color: #667eea; }
        .amount { font-weight: 700; color: #333; font-size: 16px; }
        .payment-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-block; }
        .payment-cash { background: #e8f5e9; color: #2e7d32; }
        .payment-abyssinia { background: #e3f2fd; color: #1565c0; }
        .payment-cbe { background: #f3e5f5; color: #7b1fa2; }
        .payment-telebirr { background: #fff3e0; color: #f57c00; }
        .view-btn { background: #667eea; color: white; border: none; padding: 6px 12px; border-radius: 5px; cursor: pointer; font-size: 12px; font-weight: 600; transition: background 0.3s; display: flex; align-items: center; gap: 5px; }
        .view-btn:hover { background: #5a6fd8; }
        .no-data { text-align: center; padding: 40px; color: #999; font-size: 16px; }
        .no-data i { font-size: 48px; margin-bottom: 15px; color: #ddd; }
        .payment-methods { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); }
        .payment-methods h2 { color: #333; margin-bottom: 20px; font-size: 20px; display: flex; align-items: center; gap: 10px; }
        .payment-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .payment-item { padding: 15px; border-radius: 10px; background: #f8f9ff; border-left: 4px solid #667eea; }
        .payment-name { font-weight: 600; color: #333; margin-bottom: 5px; display: flex; align-items: center; gap: 8px; }
        .payment-stats { display: flex; justify-content: space-between; font-size: 14px; color: #666; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; }
        .modal-content { background: white; border-radius: 15px; padding: 25px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-close { background: none; border: none; font-size: 24px; cursor: pointer; color: #999; }
        .receipt-container { background: white; padding: 20px; font-family: 'Courier New', monospace; }
        .receipt-header { text-align: center; padding-bottom: 15px; margin-bottom: 15px; border-bottom: 2px dashed #333; }
        .receipt-header .title { font-size: 22px; font-weight: bold; margin-bottom: 5px; color: #2c3e50; }
        .receipt-header .subtitle { font-size: 14px; color: #555; margin-bottom: 8px; }
        .receipt-header .address { font-size: 11px; color: #777; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 13px; }
        .info-label { font-weight: bold; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 13px; }
        .items-table th { text-align: left; padding: 8px 0; border-bottom: 2px solid #333; font-weight: bold; }
        .items-table td { padding: 5px 0; border-bottom: 1px solid #eee; }
        .items-table .qty { text-align: center; width: 60px; }
        .items-table .price { text-align: right; width: 80px; }
        .items-table .total { text-align: right; width: 80px; }
        .receipt-totals { padding-top: 10px; border-top: 2px solid #333; }
        .grand-total { display: flex; justify-content: space-between; font-size: 16px; font-weight: bold; margin-top: 10px; padding-top: 10px; border-top: 2px dashed #333; }
        .receipt-footer { text-align: center; padding-top: 15px; margin-top: 15px; border-top: 1px dashed #333; font-size: 11px; color: #777; }
        @media (max-width: 768px) {
            .summary-cards { grid-template-columns: 1fr; }
            .header { flex-direction: column; gap: 15px; }
            .filter-row { flex-direction: column; }
            .filter-group { min-width: 100%; }
            .date-buttons { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
            .date-btn { min-width: auto; padding: 8px 10px; font-size: 12px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <img src="icon.png" style="width:40px;height:40px;border-radius:10px;" onerror="this.style.display='none'">
                <i class="fas fa-history"></i> የሽያጭ መዝገብ 
                <span class="branch-badge"><i class="fas fa-store"></i> <?php echo htmlspecialchars($branch_name); ?></span>
            </h1>
            <div style="display: flex; gap: 10px;">
                <a href="<?php echo $user_role == 'admin' ? 'admin_dashboard.php' : 'seller_pos.php'; ?>" class="back-btn">
                    <i class="fas fa-arrow-left"></i> ወደ መሸጫው ለመመለስ
                </a>
            </div>
        </div>

        <div class="ethiopian-date">
            <div class="date-info">
                <i class="fas fa-calendar-alt"></i>
                ዛሬ በኢትዮጵያ ዘመን አቆጣጠር: 
                <span><?php echo $current_ethiopian['day'] . ' ' . $current_ethiopian['month_name'] . ' ' . $current_ethiopian['year']; ?></span>
                <span style="background: #5d4037; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px;">
                    ቀን <?php echo $current_ethiopian['day']; ?>
                </span>
            </div>
            <div class="time-info">
                <i class="fas fa-clock"></i>
                ሰዓት: <span id="ethTimeDisplay"><?php echo $current_ethiopian['time_12h']; ?></span>
            </div>
        </div>

        <div class="summary-cards">
            <div class="card">
                <div class="card-icon"><i class="fas fa-receipt"></i></div>
                <div class="card-content">
                    <h3>ጠቅላላ ሽያጭ</h3>
                    <div class="value"><?php echo number_format($total_transactions); ?></div>
                    <div class="subtext"><i class="fas fa-calendar"></i> <?php echo $display_start_date; ?> - <?php echo $display_end_date; ?></div>
                </div>
            </div>
            <div class="card">
                <div class="card-icon"><i class="fas fa-money-bill-wave"></i></div>
                <div class="card-content">
                    <h3>ጠቅላላ የተገኘው ብር</h3>
                    <div class="value"><?php echo number_format($total_sales, 2); ?> ETB</div>
                    <div class="subtext"><i class="fas fa-chart-line"></i> አማካይ: <?php echo $total_transactions > 0 ? number_format($total_sales / $total_transactions, 2) : '0.00'; ?> ETB</div>
                </div>
            </div>
            <div class="card">
                <div class="card-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="card-content">
                    <h3>የተመረጠው ጊዜ</h3>
                    <div class="value" style="font-size:20px;"><?php echo $display_start_date; ?></div>
                    <div class="subtext"><i class="fas fa-arrow-right"></i> <?php echo $display_end_date; ?></div>
                </div>
            </div>
        </div>

        <div class="filters">
            <h2><i class="fas fa-filter"></i> መፈለጊያ እና መለያ</h2>
            <form method="GET" id="filterForm">
                <div class="date-buttons">
                    <?php
                    $dateRanges = ['today'=>'ዛሬ','yesterday'=>'ትላንት','3day'=>'3 ቀን','7day'=>'7 ቀን','14day'=>'2 ሳምንት','21day'=>'3 ሳምንት','30day'=>'1 ወር','60day'=>'2 ወር','90day'=>'3 ወር','180day'=>'6 ወር','365day'=>'1 አመት'];
                    foreach ($dateRanges as $key => $label) {
                        $active = ($key == $date_range) ? 'active' : '';
                        echo '<button type="button" class="date-btn ' . $active . '" onclick="setDateRange(\'' . $key . '\')">' . $label . '</button>';
                    }
                    ?>
                </div>
                <div class="filter-row">
                    <div class="filter-group">
                        <label><i class="fas fa-receipt"></i> በደረሰኝ ቁጥር</label>
                        <input type="number" name="search_receipt_id" value="<?php echo htmlspecialchars($search_receipt_id); ?>" placeholder="የደረሰኝ ቁጥር...">
                    </div>
                    <div class="filter-group">
                        <label><i class="fas fa-search"></i> በምርት ስም</label>
                        <input type="text" name="search_item" value="<?php echo htmlspecialchars($search_item); ?>" placeholder="ስሙን ይጻፉ...">
                    </div>
                    <div class="filter-group">
                        <label><i class="fas fa-credit-card"></i> የክፍያ መንገድ</label>
                        <select name="payment_method">
                            <option value="">ሁሉም</option>
                            <option value="cash" <?php echo $filter_payment=='cash'?'selected':''; ?>>💵 ካሽ</option>
                            <option value="abyssinia" <?php echo $filter_payment=='abyssinia'?'selected':''; ?>>🏦 አቢሲንያ</option>
                            <option value="cbe" <?php echo $filter_payment=='cbe'?'selected':''; ?>>🏦 CBE</option>
                            <option value="telebirr" <?php echo $filter_payment=='telebirr'?'selected':''; ?>>📱 ቴሌብር</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label><i class="fas fa-user"></i> በሻጭ ስም</label>
                        <input type="text" name="seller" value="<?php echo htmlspecialchars($filter_seller); ?>" placeholder="የሻጭ ስም...">
                    </div>
                </div>
                <input type="hidden" name="date_range" id="date_range_input" value="<?php echo htmlspecialchars($date_range); ?>">
                <div class="filter-actions">
                    <button type="submit" class="filter-btn apply-btn"><i class="fas fa-search"></i> ፈልግ</button>
                    <button type="button" class="filter-btn reset-btn" onclick="resetFilters()"><i class="fas fa-redo"></i> አጥፋ</button>
                </div>
            </form>
        </div>

        <?php if(count($daily_data) > 0): ?>
        <div class="chart-container">
            <h2><i class="fas fa-chart-line"></i> ዕለታዊ የሽያጭ መጠን</h2>
            <div class="chart-wrapper"><canvas id="salesChart"></canvas></div>
        </div>
        <?php endif; ?>

        <div class="transactions-section">
            <div class="section-header">
                <h2><i class="fas fa-list"></i> የግብይት ዝርዝሮች</h2>
                <div class="section-buttons">
                    <span style="color: #666; margin-right: 10px;"><?php echo count($transactions_with_eth_dates); ?> ግብይቶች</span>
                    <a href="daily_report_simple.php" class="daily-report-btn">
                        <i class="fas fa-calendar-day"></i> ዕለታዊ ሪፖርት
                    </a>
                </div>
            </div>
            <?php if(count($transactions_with_eth_dates) > 0): ?>
                <table class="transactions-table">
                    <thead>
                        <tr><th>ደረሰኝ</th><th>የኢትዮጵያ ቀን</th><th>ሻጭ</th><th>እቃዎች</th><th>ጠቅላላ</th><th>ክፍያ</th><th>ደረሰኝ</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($transactions_with_eth_dates as $row): 
                            $eth_date = $row['eth_date'];
                            $eth_date_display = $eth_date['day'] . ' ' . $eth_date['month_name'] . ' ' . $eth_date['year'] . ', ' . $eth_date['time_12h'];
                            $is_today = ($eth_date['full_date'] == $current_ethiopian['full_date']);
                            $payment_class = 'payment-' . str_replace('-', '-', $row['payment_method']);
                        ?>
                        <tr style="<?php echo $is_today ? 'background-color: #f0f9ff;' : ''; ?>">
                            <td class="transaction-id">#<?php echo str_pad($row['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td>
                                <?php echo htmlspecialchars($eth_date_display); ?>
                                <?php if($is_today): ?><span style="background: #4CAF50; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; margin-left: 5px;">ዛሬ</span><?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($row['seller_name']); ?></strong></td>
                            <td><?php echo $row['item_count']; ?> እቃ</td>
                            <td class="amount"><?php echo number_format($row['total_amount'], 2); ?> ETB</td>
                            <td><span class="payment-badge <?php echo $payment_class; ?>"><?php echo $row['payment_method']; ?></span></td>
                            <td><button class="view-btn" onclick="viewReceipt(<?php echo $row['id']; ?>)"><i class="fas fa-eye"></i> አሳይ</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data"><i class="fas fa-clipboard-list"></i><h3>ምንም ግብይት አልተገኘም</h3></div>
            <?php endif; ?>
        </div>

        <?php if($payment_result && mysqli_num_rows($payment_result) > 0): ?>
        <div class="payment-methods">
            <h2><i class="fas fa-credit-card"></i> የክፍያ መንገዶች</h2>
            <div class="payment-list">
                <?php while($payment = mysqli_fetch_assoc($payment_result)): ?>
                <div class="payment-item">
                    <div class="payment-name"><?php echo ucfirst($payment['payment_method']); ?></div>
                    <div class="payment-stats">
                        <span><?php echo $payment['count']; ?> ግብይቶች</span>
                        <span><strong><?php echo number_format($payment['amount'], 2); ?> ETB</strong></span>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Receipt Modal -->
    <div class="modal" id="receiptModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-receipt"></i> የደረሰኝ ዝርዝር</h3>
                <button class="modal-close" onclick="closeReceipt()">✕</button>
            </div>
            <div id="receiptContent"><div style="text-align:center;padding:20px;"><i class="fas fa-spinner fa-spin"></i> በመጫን ላይ...</div></div>
        </div>
    </div>

    <script>
    const transactionEthDates = <?php echo json_encode(array_column($transactions_with_eth_dates, 'eth_date', 'id')); ?>;
    const currentEthDate = '<?php echo $current_ethiopian['full_date']; ?>';

    <?php if(count($daily_data) > 0): ?>
    const dailyData = <?php echo json_encode($daily_data); ?>;
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dailyData.map(d => d.ethiopian_day),
            datasets: [{
                label: 'ዕለታዊ ሽያጭ (ETB)',
                data: dailyData.map(d => d.total),
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderColor: 'rgba(102, 126, 234, 1)',
                borderWidth: 3, fill: true, tension: 0.4,
                pointBackgroundColor: 'rgba(102, 126, 234, 1)',
                pointRadius: 5, pointHoverRadius: 7
            }]
        },
        options: { responsive: true, maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { callback: v => v.toFixed(2) + ' ETB' } } }
        }
    });
    <?php endif; ?>

    function setDateRange(range) {
        document.getElementById('date_range_input').value = range;
        document.getElementById('filterForm').submit();
    }

    function resetFilters() {
        window.location.href = 'history.php';
    }

    function viewReceipt(id) {
        document.getElementById('receiptModal').style.display = 'flex';
        document.getElementById('receiptContent').innerHTML = '<div style="text-align:center;padding:20px;"><i class="fas fa-spinner fa-spin"></i> በመጫን ላይ...</div>';
        
        fetch('get_transaction_details.php?id=' + id)
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    const t = data.transaction;
                    const items = data.items;
                    const eth = transactionEthDates[id] || {day:'?',month_name:'?',year:'?',time_12h:'?'};
                    const isToday = (eth.full_date === currentEthDate);
                    
                    let html = `<div class="receipt-container">
                        <div class="receipt-header">
                            <div class="title">አጸደ ትጉሃን ሰንበት ትምህርት ቤት</div>
                            <div class="subtitle">atsedeTeguhan sunday school</div>
                            <div class="address">አዲስ አበባ፣ ኢትዮጵያ | ስልክ: +251 921664431</div>
                        </div>
                        <div style="margin-bottom:15px;">
                            <div class="info-row"><span class="info-label">ደረሰኝ ቁጥር:</span><span>#${t.id.toString().padStart(6,'0')}</span></div>
                            <div class="info-row"><span class="info-label">ቀን:</span><span>${eth.day} ${eth.month_name} ${eth.year}, ${eth.time_12h}</span>${isToday?' <span style="color:#4CAF50;">(ዛሬ)</span>':''}</div>
                            <div class="info-row"><span class="info-label">ሻጭ:</span><span>${t.seller_name}</span></div>
                            <div class="info-row"><span class="info-label">ክፍያ:</span><span>${t.payment_method}</span></div>
                        </div>
                        <table class="items-table">
                            <thead><tr><th>ምርት</th><th class="qty">ብዛት</th><th class="price">ዋጋ</th><th class="total">ድምር</th></tr></thead>
                            <tbody>${items.map(i => `<tr><td>${i.product_name}</td><td class="qty">${parseFloat(i.quantity).toFixed(2)}</td><td class="price">${parseFloat(i.unit_price).toFixed(2)}</td><td class="total">${parseFloat(i.subtotal).toFixed(2)}</td></tr>`).join('')}</tbody>
                        </table>
                        <div class="receipt-totals">
                            <div class="grand-total"><span>ጠቅላላ ድምር:</span><span>${parseFloat(t.total_amount).toFixed(2)} ብር</span></div>
                        </div>
                        <div class="receipt-footer">
                            <div>ስለገዙን እናመሰግናለን! | Thank you!</div>
                        </div>
                    </div>`;
                    document.getElementById('receiptContent').innerHTML = html;
                } else {
                    document.getElementById('receiptContent').innerHTML = '<div style="text-align:center;color:red;"><i class="fas fa-exclamation-circle"></i> ደረሰኙ አልተገኘም</div>';
                }
            })
            .catch(() => {
                document.getElementById('receiptContent').innerHTML = '<div style="text-align:center;color:red;">ስህተት ተከስቷል</div>';
            });
    }

    function closeReceipt() {
        document.getElementById('receiptModal').style.display = 'none';
    }

    document.getElementById('receiptModal').addEventListener('click', function(e) {
        if (e.target === this) closeReceipt();
    });

    // Live Ethiopian time
    function updateTime() {
        const now = new Date();
        const ethTime = new Date(now.getTime() + (3 * 60 * 60 * 1000));
        let h = ethTime.getUTCHours();
        const m = String(ethTime.getUTCMinutes()).padStart(2,'0');
        const s = String(ethTime.getUTCSeconds()).padStart(2,'0');
        const h12 = h % 12 || 12;
        const ampm = h < 12 ? 'ጥዋት' : 'ከሰዓት';
        document.getElementById('ethTimeDisplay').textContent = `${h12}:${m}:${s} ${ampm}`;
    }
    updateTime();
    setInterval(updateTime, 1000);
    </script>
</body>
</html>
<?php 
if (isset($result)) mysqli_free_result($result);
if (isset($payment_result)) mysqli_free_result($payment_result);
mysqli_close($conn); 
?>