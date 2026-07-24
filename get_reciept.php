<?php
require_once 'config.php';
header('Content-Type: application/json');

$id = intval($_GET['id'] ?? 0);

$txn = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM transactions WHERE id = $id"));
if (!$txn) {
    echo json_encode(['success' => false, 'message' => 'Not found']);
    exit;
}

$items_result = mysqli_query($conn, "SELECT * FROM transaction_items WHERE transaction_id = $id");
$items = [];
while ($item = mysqli_fetch_assoc($items_result)) {
    $items[] = [
        'name' => $item['product_name'],
        'qty' => number_format($item['quantity'], 2),
        'price' => number_format($item['unit_price'], 2),
        'subtotal' => number_format($item['subtotal'], 2)
    ];
}

// Ethiopian date conversion
function getEthDate($d) {
    $ts = strtotime($d);
    $gy = (int)date('Y', $ts);
    $gm = (int)date('m', $ts);
    $gd = (int)date('d', $ts);
    $months = ['','መስከረም','ጥቅምት','ኅዳር','ታኅሣሥ','ጥር','የካቲት','መጋቢት','ሚያዝያ','ግንቦት','ሰኔ','ሐምሌ','ነሐሴ','ጳጉሜ'];
    $ey = $gy - 8;
    if ($gm >= 9 || ($gm == 9 && $gd >= 11)) $ey++;
    $em = (($gm + 3) % 12) + 1;
    if ($em > 13) $em -= 13;
    $ed = $gd;
    return "$ed {$months[$em]} $ey";
}

$ethTime = date('h:i A', strtotime($txn['transaction_date']) + 10800);

echo json_encode([
    'success' => true,
    'id' => $txn['id'],
    'date' => getEthDate($txn['transaction_date']),
    'time' => $ethTime,
    'seller' => $txn['seller_name'],
    'payment' => $txn['payment_method'],
    'total' => number_format($txn['total_amount'], 2),
    'items' => $items
]);
?>