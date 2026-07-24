<?php
session_start();
require_once 'config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

header('Content-Type: application/json');

$transaction_id = intval($_GET['id'] ?? 0);

if ($transaction_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid transaction ID']);
    exit();
}

// Get transaction details
$query = "SELECT * FROM transactions WHERE id = $transaction_id";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Transaction not found']);
    exit();
}

$transaction = mysqli_fetch_assoc($result);

// Get transaction items
$items_query = "SELECT * FROM transaction_items WHERE transaction_id = $transaction_id";
$items_result = mysqli_query($conn, $items_query);

$items = [];
while($item = mysqli_fetch_assoc($items_result)) {
    $items[] = $item;
}

$transaction['items'] = $items;

// Convert to Ethiopian date
function getEthiopianDateTime($gregorian_datetime) {
    if (strlen($gregorian_datetime) == 10) $gregorian_datetime .= ' 00:00:00';
    
    $date = new DateTime($gregorian_datetime, new DateTimeZone('Africa/Addis_Ababa'));
    $year = (int)$date->format('Y');
    $month = (int)$date->format('m');
    $day = (int)$date->format('d');
    $hour = (int)$date->format('H');
    $minute = (int)$date->format('i');
    
    $eth_months = ["መስከረም","ጥቅምት","ህዳር","ታህሳስ","ጥር","የካቲት","መጋቢት","ሚያዝያ","ግንቦት","ሰኔ","ሐምሌ","ነሐሴ","ጳጉሜ"];
    
    $is_leap = ($year % 4 == 0 && ($year % 100 != 0 || $year % 400 == 0));
    $new_year_day = $is_leap ? 12 : 11;
    
    if ($month < 9 || ($month == 9 && $day < $new_year_day)) {
        $eth_year = $year - 8;
    } else {
        $eth_year = $year - 7;
    }
    
    $new_year = new DateTime($year . '-09-' . $new_year_day, new DateTimeZone('Africa/Addis_Ababa'));
    if ($date < $new_year) {
        $prev_leap = (($year-1) % 4 == 0 && (($year-1) % 100 != 0 || ($year-1) % 400 == 0));
        $prev_new_day = $prev_leap ? 12 : 11;
        $new_year = new DateTime(($year-1) . '-09-' . $prev_new_day, new DateTimeZone('Africa/Addis_Ababa'));
    }
    
    $days_diff = $new_year->diff($date)->days;
    $eth_month = floor($days_diff / 30) + 1;
    $eth_day = ($days_diff % 30) + 1;
    
    if ($eth_month > 13) { $eth_month = 13; $eth_day = min($eth_day, 5); }
    
    $hour_12 = $hour % 12 ?: 12;
    $ampm = $hour < 12 ? 'ጥዋት' : 'ከሰዓት';
    
    return $eth_day . ' ' . $eth_months[$eth_month-1] . ' ' . $eth_year . ', ' . $hour_12 . ':' . str_pad($minute,2,'0',STR_PAD_LEFT) . ' ' . $ampm;
}

$transaction['eth_date'] = getEthiopianDateTime($transaction['transaction_date']);

echo json_encode([
    'success' => true,
    'transaction' => $transaction,
    'items' => $items
]);

mysqli_close($conn);
?>