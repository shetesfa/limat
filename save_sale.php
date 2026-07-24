<?php
session_start();
require_once 'config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

header('Content-Type: application/json');

$cart = json_decode($_POST['cart'] ?? '[]', true);
$total = floatval($_POST['total'] ?? 0);
$payment = $_POST['payment'] ?? 'cash';
$seller_id = intval($_POST['seller_id'] ?? $_SESSION['user_id']);
$seller_name = mysqli_real_escape_string($conn, $_POST['seller_name'] ?? ($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Seller'));
$branch_id = intval($_SESSION['branch_id'] ?? 1);

if (empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit();
}

// First verify the sales table exists, if not create it
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'sales'");
if (mysqli_num_rows($table_check) == 0) {
    $create_table = "CREATE TABLE `sales` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `seller_id` int(11) NOT NULL,
        `seller_name` varchar(255) NOT NULL,
        `items` text NOT NULL,
        `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
        `payment_method` varchar(50) NOT NULL DEFAULT 'cash',
        `sale_date` varchar(20) NOT NULL,
        `branch_id` int(11) NOT NULL DEFAULT 1,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!mysqli_query($conn, $create_table)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create sales table: ' . mysqli_error($conn)]);
        exit();
    }
}

// Start transaction AFTER table creation
mysqli_begin_transaction($conn);

try {
    // Update seller inventory - subtract EXACT qty for each item
    foreach ($cart as $item) {
        $item_name = mysqli_real_escape_string($conn, $item['name']);
        $qty = floatval($item['qty']);
        
        // Update this seller's inventory - subtract exactly the quantity sold
        $update_query = "UPDATE seller_inventory 
                        SET current_stock = current_stock - $qty 
                        WHERE seller_id = $seller_id 
                        AND item_name = '$item_name' 
                        AND branch_id = $branch_id
                        AND current_stock >= $qty";
        
        if (!mysqli_query($conn, $update_query)) {
            throw new Exception("Failed to update inventory for: $item_name");
        }
        
        // Check if any row was affected (meaning stock was sufficient)
        if (mysqli_affected_rows($conn) == 0) {
            // Check current stock
            $check_query = "SELECT current_stock FROM seller_inventory 
                           WHERE seller_id = $seller_id 
                           AND item_name = '$item_name' 
                           AND branch_id = $branch_id";
            $result = mysqli_query($conn, $check_query);
            
            if ($row = mysqli_fetch_assoc($result)) {
                throw new Exception("Insufficient stock for: $item_name. Available: {$row['current_stock']}, Requested: $qty");
            } else {
                throw new Exception("Product not found in inventory: $item_name");
            }
        }
    }
    
    // Get Ethiopian date (simplified version for save)
    $greg_date = date('Y-m-d');
    list($y, $m, $d) = explode('-', $greg_date);
    $eth_year = $y - 8;
    if ($m >= 9 || ($m == 9 && $d >= 11)) $eth_year++;
    $eth_date = $eth_year . '-' . str_pad($m, 2, '0', STR_PAD_LEFT) . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
    
    $items_json = mysqli_real_escape_string($conn, json_encode($cart));
    
    // Insert sale record
    $insert_sale = "INSERT INTO sales (seller_id, seller_name, items, total_amount, payment_method, sale_date, branch_id) 
                   VALUES ($seller_id, '$seller_name', '$items_json', $total, '$payment', '$eth_date', $branch_id)";
    
    if (!mysqli_query($conn, $insert_sale)) {
        throw new Exception("Failed to save sale record: " . mysqli_error($conn));
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Sale completed successfully',
        'sale_id' => mysqli_insert_id($conn)
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($conn);
?>