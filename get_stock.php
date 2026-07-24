<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$branch_id = $_GET['branch_id'] ?? ($_SESSION['branch_id'] ?? 1);

$query = "SELECT p.id, p.name, p.unit_price as price, 
          COALESCE(SUM(si.current_stock), 0) as stock, 
          COALESCE(si.unit, 'pcs') as unit
          FROM products p
          LEFT JOIN seller_inventory si ON p.name = si.item_name AND si.branch_id = $branch_id
          WHERE p.is_active = 1
          GROUP BY p.id, p.name, p.unit_price, si.unit
          ORDER BY p.name";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    exit();
}

$products = [];
while($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

echo json_encode(['success' => true, 'products' => $products]);
mysqli_close($conn);
?>