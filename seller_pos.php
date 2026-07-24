<?php
session_start();
require_once 'config.php';

if (!isLoggedIn()) redirect('index.php');

mysqli_set_charset($conn, "utf8mb4");

$current_user = $_SESSION['full_name'] ?? $_SESSION['username'] ?? "Seller";
$seller_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'seller';
$branch_id = $_SESSION['branch_id'] ?? 1;

// Ethiopian date function
function get_ethiopian_date_time_seller() {
    $gregorian_date = date('Y-m-d');
    list($greg_year, $greg_month, $greg_day) = explode('-', $gregorian_date);
    
    $ethiopian_months = array(
        1 => array('start' => '09-11', 'name' => 'መስከረም'),
        2 => array('start' => '10-11', 'name' => 'ጥቅምት'),
        3 => array('start' => '11-10', 'name' => 'ኅዳር'),
        4 => array('start' => '12-10', 'name' => 'ታኅሣሥ'),
        5 => array('start' => '01-09', 'name' => 'ጥር'),
        6 => array('start' => '02-08', 'name' => 'የካቲት'),
        7 => array('start' => '03-10', 'name' => 'መጋቢት'),
        8 => array('start' => '04-09', 'name' => 'ሚያዝያ'),
        9 => array('start' => '05-09', 'name' => 'ግንቦት'),
        10 => array('start' => '06-08', 'name' => 'ሰኔ'),
        11 => array('start' => '07-08', 'name' => 'ሐምሌ'),
        12 => array('start' => '08-07', 'name' => 'ነሐሴ'),
        13 => array('start' => '09-06', 'name' => 'ጳጉሜ')
    );
    
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
    
    $timestamp = time();
    $ethiopian_timestamp = $timestamp + (3 * 3600);
    $eth_time_12h = date('h:i A', $ethiopian_timestamp);
    
    return [
        'date' => $ethiopian_year . '-' . str_pad($eth_month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($eth_day, 2, '0', STR_PAD_LEFT),
        'time' => $eth_time_12h,
        'year' => $ethiopian_year,
        'month' => $eth_month,
        'day' => $eth_day,
        'month_name' => isset($ethiopian_months[$eth_month]) ? $ethiopian_months[$eth_month]['name'] : ''
    ];
}

// Handle AJAX sale saving - MUST be before any HTML output
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_sale') {
    // Turn off error display and set JSON header
    ini_set('display_errors', 0);
    error_reporting(0);
    header('Content-Type: application/json');
    
    $response = ['success' => false, 'message' => ''];
    
    try {
        $cart = json_decode($_POST['cart'], true);
        if (!$cart) {
            throw new Exception('Invalid cart data');
        }
        
        $total = floatval($_POST['total']);
        $payment = mysqli_real_escape_string($conn, $_POST['payment']);
        $seller_name = mysqli_real_escape_string($conn, $_POST['seller_name']);
        
        // Start transaction
        mysqli_begin_transaction($conn);
        
        // Insert into TRANSACTIONS table
        $transaction_date = date('Y-m-d H:i:s');
        
        $insert_transaction = "INSERT INTO transactions (seller_id, seller_name, total_amount, payment_method, transaction_date, branch_id) 
                               VALUES ($seller_id, '$seller_name', $total, '$payment', '$transaction_date', $branch_id)";
        
        if (!mysqli_query($conn, $insert_transaction)) {
            throw new Exception("Failed to save transaction: " . mysqli_error($conn));
        }
        
        $transaction_id = mysqli_insert_id($conn);
        
        // Process each item
        foreach ($cart as $item) {
            $item_name = mysqli_real_escape_string($conn, $item['name']);
            $qty = floatval($item['qty']);
            $price = floatval($item['price']);
            $subtotal = floatval($item['subtotal']);
            
            // Insert into transaction_items
            $insert_item = "INSERT INTO transaction_items (transaction_id, product_name, quantity, unit_price, subtotal) 
                           VALUES ($transaction_id, '$item_name', $qty, $price, $subtotal)";
            
            if (!mysqli_query($conn, $insert_item)) {
                throw new Exception("Failed to save item: " . mysqli_error($conn));
            }
            
            // Check if inventory exists for this seller and product
            $check_inventory = mysqli_query($conn, 
                "SELECT id, current_stock FROM seller_inventory 
                 WHERE seller_id = $seller_id 
                 AND item_name = '$item_name' 
                 AND branch_id = $branch_id");
            
            if (!$check_inventory) {
                throw new Exception("Failed to check inventory: " . mysqli_error($conn));
            }
            
            if (mysqli_num_rows($check_inventory) == 0) {
                // Create inventory entry if it doesn't exist
                $create_inventory = "INSERT INTO seller_inventory (seller_id, item_name, current_stock, unit, price, branch_id) 
                                    VALUES ($seller_id, '$item_name', 0, 'pcs', $price, $branch_id)";
                if (!mysqli_query($conn, $create_inventory)) {
                    throw new Exception("Failed to create inventory: " . mysqli_error($conn));
                }
            }
            
            // Update seller inventory - subtract EXACT qty (1 item = -1, not -3)
            $update_inventory = "UPDATE seller_inventory 
                               SET current_stock = current_stock - $qty 
                               WHERE seller_id = $seller_id 
                               AND item_name = '$item_name' 
                               AND branch_id = $branch_id";
            
            if (!mysqli_query($conn, $update_inventory)) {
                throw new Exception("Failed to update inventory: " . mysqli_error($conn));
            }
            
            // Check affected rows
            if (mysqli_affected_rows($conn) == 0) {
                throw new Exception("No inventory updated for $item_name. Seller may not have this item.");
            }
            
            // Check if stock went negative
            $check_stock = mysqli_query($conn, "SELECT current_stock FROM seller_inventory 
                WHERE seller_id = $seller_id AND item_name = '$item_name' AND branch_id = $branch_id");
            
            if (!$check_stock) {
                throw new Exception("Failed to check stock: " . mysqli_error($conn));
            }
            
            $stock_data = mysqli_fetch_assoc($check_stock);
            
            if ($stock_data && $stock_data['current_stock'] < 0) {
                // Rollback if stock goes negative
                throw new Exception("Insufficient stock for $item_name. Available: " . ($stock_data['current_stock'] + $qty) . ", Requested: $qty");
            }
        }
        
        // Commit transaction
        mysqli_commit($conn);
        $response['success'] = true;
        $response['message'] = 'Sale completed successfully';
        $response['transaction_id'] = $transaction_id;
        
    } catch (Exception $e) {
        // Rollback on error
        mysqli_rollback($conn);
        $response['success'] = false;
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit();
}

// Handle add new product - ONLY FOR ADMIN
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_new_product']) && isAdmin()) {
    $new_product_name = mysqli_real_escape_string($conn, $_POST['new_product_name']);
    $new_product_price = floatval($_POST['new_product_price']);
    
    $check = mysqli_query($conn, "SELECT id FROM products WHERE name = '$new_product_name'");
    
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('ይህ ምርት አስቀድሞ አለ!');</script>";
    } else {
        $insert = "INSERT INTO products (name, unit_price, last_edited_by, last_edited_at) VALUES ('$new_product_name', $new_product_price, '$current_user', NOW())";
        
        if (mysqli_query($conn, $insert)) {
            $new_id = mysqli_insert_id($conn);
            
            // Add to all sellers' inventory
            $sellers = mysqli_query($conn, "SELECT id FROM users WHERE role = 'seller' AND branch_id = $branch_id");
            while ($s = mysqli_fetch_assoc($sellers)) {
                $inv_insert = "INSERT INTO seller_inventory (seller_id, item_name, current_stock, unit, price, branch_id) VALUES ({$s['id']}, '$new_product_name', 0, 'pcs', $new_product_price, $branch_id)";
                mysqli_query($conn, $inv_insert);
            }
            
            echo "<script>alert('ምርቱ በትክክል ተመዝግቧል!'); location.reload();</script>";
            exit();
        }
    }
}

// Get products with stock from ALL sellers' inventory combined (branch-level stock)
$products_query = "SELECT p.*, 
                   COALESCE(SUM(si.current_stock), 0) as current_stock, 
                   COALESCE(si.unit, 'pcs') as unit
                   FROM products p
                   LEFT JOIN seller_inventory si ON p.name = si.item_name AND si.branch_id = $branch_id
                   WHERE p.is_active = 1
                   GROUP BY p.id, p.name, p.unit_price, p.is_active, si.unit
                   ORDER BY p.name";
$products_result = mysqli_query($conn, $products_query);

$products_data = [];
while($p = mysqli_fetch_assoc($products_result)) {
    $products_data[] = $p;
}
mysqli_data_seek($products_result, 0);

$ethiopian_datetime = get_ethiopian_date_time_seller();
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" type="image/jpg" href="image/photo_2026-01-12_07-44-10.jpg">
    <title>አጸደ ትጉሃን ሰንበት ትምህርት ቤት - POS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8B4513;
            --gold: #DAA520;
            --dark: #5C2E0B;
            --light: #FFF8DC;
            --danger: #DC3545;
            --success: #28A745;
            --info: #17A2B8;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(circle at center, #F6E27A 0%, #F4A640 35%, #D96B2B 65%, #7A1E1E 100%);
            min-height: 100vh;
            color: #333;
            font-size: 14px;
        }
        
        .ethiopian-time {
            background: var(--primary);
            color: var(--gold);
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .container {
            display: grid;
            grid-template-columns: 300px 1fr 300px;
            grid-template-rows: auto 1fr auto;
            min-height: 100vh;
            gap: 12px;
            padding: 12px;
        }
        
        .top-header {
            grid-column: 1 / -1;
            background: white;
            border-radius: 15px;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(45deg, var(--primary), var(--gold));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-name {
            font-weight: 600;
            color: var(--dark);
        }
        
        .user-role {
            font-size: 11px;
            color: #999;
            background: #f0f0f0;
            padding: 2px 8px;
            border-radius: 10px;
        }
        
        .logout-btn {
            background: white;
            color: var(--danger);
            border: 2px solid var(--danger);
            padding: 10px 18px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .logout-btn:hover {
            background: var(--danger);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(220,53,69,0.4);
        }
        
        .left-sidebar {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .total-box {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .total-box h2 {
            color: #555;
            font-size: 13px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        
        #total-amount {
            font-size: 30px;
            font-weight: 700;
            color: var(--primary);
        }
        
        .payment-methods {
            background: white;
            border-radius: 15px;
            padding: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }
        
        .payment-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 12px;
        }
        
        .payment-btn {
            padding: 10px;
            border: 2px solid #e0d5c1;
            background: white;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 500;
            font-size: 12px;
            transition: all 0.3s;
        }
        
        .payment-btn:hover, .payment-btn.active {
            border-color: var(--gold);
            background: var(--light);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(218,165,32,0.3);
        }
        
        .finish-box {
            background: white;
            border-radius: 15px;
            padding: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        #finish-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(40,167,69,0.3);
        }
        
        #finish-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }
        
        #finish-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            box-shadow: none;
        }
        
        .calculator {
            background: white;
            border-radius: 15px;
            padding: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }
        
        .calc-input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0d5c1;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .calc-input:focus {
            outline: none;
            border-color: var(--gold);
        }
        
        .calc-result {
            padding: 10px;
            border-radius: 10px;
            text-align: center;
            font-weight: 600;
            min-height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .center {
            background: white;
            border-radius: 15px;
            padding: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 180px);
            overflow-y: auto;
        }
        
        .transaction-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        
        .transaction-table th {
            background: var(--primary);
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        
        .transaction-table td {
            padding: 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .qty-control {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .qty-btn {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.2s;
        }
        
        .qty-btn:hover {
            background: var(--primary);
            color: var(--gold);
        }
        
        .right-sidebar {
            background: white;
            border-radius: 15px;
            padding: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 180px);
            overflow-y: auto;
        }
        
        .product-btn {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: white;
            border: 2px solid #e0d5c1;
            border-radius: 10px;
            cursor: pointer;
            margin-bottom: 6px;
            transition: all 0.3s;
        }
        
        .product-btn:hover {
            border-color: var(--gold);
            background: var(--light);
            transform: translateX(3px);
            box-shadow: 0 4px 15px rgba(218,165,32,0.2);
        }
        
        .product-btn.out-of-stock {
            opacity: 0.6;
            cursor: not-allowed;
            border-color: #ddd;
        }
        
        .product-btn.out-of-stock:hover {
            border-color: #ddd;
            background: white;
            transform: none;
            box-shadow: none;
        }
        
        .product-name {
            font-weight: 500;
            font-size: 13px;
            flex: 1;
        }
        
        .product-price {
            color: #888;
            font-size: 12px;
            margin-left: 8px;
        }
        
        .stock-badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 600;
        }
        
        .stock-available { background: #d4edda; color: #155724; }
        .stock-low { background: #fff3cd; color: #856404; }
        .stock-out { background: #f8d7da; color: #721c24; }
        
        .add-product-box {
            background: white;
            border-radius: 15px;
            padding: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }
        
        .add-product-form {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .add-product-form input {
            padding: 10px;
            border: 2px solid #e0d5c1;
            border-radius: 10px;
            font-size: 13px;
        }
        
        .add-product-btn {
            padding: 10px;
            background: var(--primary);
            color: var(--gold);
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(139,69,19,0.3);
        }
        
        .footer {
            grid-column: 1 / -1;
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .footer-btn {
            padding: 10px 20px;
            background: white;
            color: var(--primary);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .footer-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(139, 69, 19, 0.3);
            background: linear-gradient(135deg, #8B4513, #DAA520);
            color: white;
        }
        
        @media (max-width: 768px) {
            .container {
                display: block;
                padding: 8px;
            }
            .left-sidebar, .right-sidebar, .center {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="top-header">
        <div class="user-info">
            <img src="icon.png" alt="Icon" style="width:35px;height:35px;border-radius:8px;">
            <div class="user-avatar"><?php echo strtoupper(substr($current_user, 0, 1)); ?></div>
            <div>
                <div class="user-name"><?php echo htmlspecialchars($current_user); ?></div>
                <div class="user-role"><?php echo isAdmin() ? 'አድሚን' : 'ሻጭ'; ?></div>
            </div>
        </div>
        <div style="display:flex; align-items:center; gap:12px;">
            <div class="ethiopian-time">
                <i class="fas fa-calendar-alt"></i>
                <span><?php echo $ethiopian_datetime['day'] . ' ' . $ethiopian_datetime['month_name'] . ' ' . $ethiopian_datetime['year']; ?></span>
                <span><?php echo $ethiopian_datetime['time']; ?></span>
            </div>
            <button class="logout-btn" onclick="window.location.href='logout.php'">
                <i class="fas fa-sign-out-alt"></i> ውጣ
            </button>
        </div>
    </div>
    
    <div class="container">
        <div class="left-sidebar">
            <div class="total-box">
                <h2><i class="fas fa-receipt"></i> ጠቅላላ ድምር</h2>
                <div id="total-amount">0.00 ብር</div>
            </div>
            
            <div class="payment-methods">
                <h3 style="margin-bottom:10px; font-size:13px;"><i class="fas fa-credit-card"></i> የመክፈያ መንገድ</h3>
                <div class="payment-options">
                    <button class="payment-btn active" onclick="selectPayment('cash', this)">💵 ካሽ</button>
                    <button class="payment-btn" onclick="selectPayment('telebirr', this)">📱 ቴሌብር</button>
                    <button class="payment-btn" onclick="selectPayment('cbe', this)">🏦 CBE</button>
                    <button class="payment-btn" onclick="selectPayment('abyssinia', this)">🏦 አቢሲንያ</button>
                </div>
            </div>
            
            <div class="finish-box">
                <h3 style="margin-bottom:10px; font-size:13px;">✅ ሽያጩን ጨርስ</h3>
                <button id="finish-btn" onclick="finishTransaction()" disabled>
                    <i class="fas fa-check"></i> ሽያጩ ተጠናቋል
                </button>
            </div>
            
            <div class="calculator">
                <div style="text-align:center; margin-bottom:8px; font-weight:600; font-size:13px;">
                    <i class="fas fa-calculator"></i> ቀሪ ማስያ
                </div>
                <div style="background:var(--primary); color:var(--gold); padding:8px; border-radius:10px; text-align:center; margin-bottom:8px;">
                    ጠቅላላ: <span id="calc-total">0.00 ብር</span>
                </div>
                <input type="number" id="calc-paid" class="calc-input" placeholder="የተቀበሉትን ያስገቡ" oninput="calculateChange()">
                <div id="calc-result" class="calc-result">መጠኑን ያስገቡ</div>
            </div>
            
            <?php if(isAdmin()): ?>
            <div class="add-product-box">
                <h3 style="margin-bottom:10px; font-size:13px;"><i class="fas fa-plus-circle"></i> አዲስ ምርት መዝግብ</h3>
                <form method="POST" class="add-product-form">
                    <input type="text" name="new_product_name" placeholder="የምርት ስም" required>
                    <input type="number" name="new_product_price" placeholder="ዋጋ (ብር)" step="0.01" required>
                    <button type="submit" name="add_new_product" class="add-product-btn">
                        <i class="fas fa-plus"></i> መዝግብ
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="center">
            <h2 style="text-align:center; margin-bottom:15px;"><i class="fas fa-shopping-cart"></i> የተመረጡ እቃዎች</h2>
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>ምርት</th>
                        <th>ብዛት</th>
                        <th>ዋጋ</th>
                        <th>ድምር</th>
                        <th>ሰርዝ</th>
                    </tr>
                </thead>
                <tbody id="cart-items">
                    <tr><td colspan="5" style="text-align:center; padding:40px; color:#ccc;">ምንም እቃ አልተመረጠም</td></tr>
                </tbody>
            </table>
        </div>
        
        <div class="right-sidebar">
            <h2 style="text-align:center; margin-bottom:15px;"><i class="fas fa-store"></i> ምርቶች</h2>
            <div id="product-list">
            <?php 
            mysqli_data_seek($products_result, 0);
            while($product = mysqli_fetch_assoc($products_result)): 
                $stock_class = 'stock-available';
                $stock_text = number_format($product['current_stock'], 1) . ' ' . $product['unit'];
                $out_of_stock = false;
                
                if ($product['current_stock'] <= 0) {
                    $stock_class = 'stock-out';
                    $stock_text = 'አልቋል';
                    $out_of_stock = true;
                } elseif ($product['current_stock'] <= 5) {
                    $stock_class = 'stock-low';
                }
                
                $product_class = $out_of_stock ? 'product-btn out-of-stock' : 'product-btn';
                $onclick = $out_of_stock ? '' : "addToCart({$product['id']}, '".addslashes(htmlspecialchars($product['name']))."', {$product['unit_price']})";
            ?>
                <div class="<?php echo $product_class; ?>" <?php if(!$out_of_stock) echo "onclick=\"$onclick\""; ?>>
                    <span class="product-name"><?php echo htmlspecialchars($product['name']); ?></span>
                    <span class="product-price"><?php echo number_format($product['unit_price'], 2); ?> ብር</span>
                    <span class="stock-badge <?php echo $stock_class; ?>"><?php echo $stock_text; ?></span>
                </div>
            <?php endwhile; ?>
            </div>
        </div>
        
        <div class="footer">
            <a href="history.php" class="footer-btn"><i class="fas fa-history"></i> የሽያጭ ታሪክ</a>
            <?php if(isAdmin()): ?>
            <a href="admin_products.php" class="footer-btn"><i class="fas fa-box"></i> ምርት መቀበያ</a>
            <a href="admin_dashboard.php" class="footer-btn"><i class="fas fa-tachometer-alt"></i> ዳሽቦርድ</a>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    let cart = [];
    let paymentMethod = 'cash';
    
    function addToCart(id, name, price) {
        let existing = cart.find(item => item.id === id);
        if (existing) {
            existing.qty += 1;
            existing.subtotal = existing.qty * price;
        } else {
            cart.push({id, name, price, qty: 1, subtotal: price});
        }
        renderCart();
    }
    
    function removeFromCart(index) {
        cart.splice(index, 1);
        renderCart();
    }
    
    function changeQty(index, delta) {
        if (cart[index].qty + delta > 0) {
            cart[index].qty += delta;
            cart[index].subtotal = cart[index].qty * cart[index].price;
        } else {
            cart.splice(index, 1);
        }
        renderCart();
    }
    
    function renderCart() {
        const tbody = document.getElementById('cart-items');
        const totalEl = document.getElementById('total-amount');
        const calcTotal = document.getElementById('calc-total');
        const finishBtn = document.getElementById('finish-btn');
        
        if (cart.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; padding:40px; color:#ccc;">ምንም እቃ አልተመረጠም</td></tr>';
            totalEl.textContent = '0.00 ብር';
            calcTotal.textContent = '0.00 ብር';
            finishBtn.disabled = true;
            return;
        }
        
        let html = '';
        let total = 0;
        cart.forEach((item, i) => {
            total += item.subtotal;
            html += `<tr>
                <td>${item.name}</td>
                <td>
                    <div class="qty-control">
                        <button class="qty-btn" onclick="changeQty(${i}, -1)">−</button>
                        <span>${item.qty}</span>
                        <button class="qty-btn" onclick="changeQty(${i}, 1)">+</button>
                    </div>
                </td>
                <td>${item.price.toFixed(2)}</td>
                <td><strong>${item.subtotal.toFixed(2)}</strong></td>
                <td><button class="qty-btn" onclick="removeFromCart(${i})" style="color:red;">✕</button></td>
            </tr>`;
        });
        
        tbody.innerHTML = html;
        totalEl.textContent = total.toFixed(2) + ' ብር';
        calcTotal.textContent = total.toFixed(2) + ' ብር';
        finishBtn.disabled = false;
        
        calculateChange();
    }
    
    function selectPayment(method, btn) {
        paymentMethod = method;
        document.querySelectorAll('.payment-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }
    
    function calculateChange() {
        const paid = parseFloat(document.getElementById('calc-paid').value) || 0;
        const total = cart.reduce((sum, item) => sum + item.subtotal, 0);
        const result = document.getElementById('calc-result');
        
        if (paid <= 0) {
            result.textContent = 'መጠኑን ያስገቡ';
            result.style.background = '';
            return;
        }
        
        const change = paid - total;
        if (change < 0) {
            result.textContent = 'ቀሪ: ' + Math.abs(change).toFixed(2) + ' ያስፈልጋል';
            result.style.background = '#fff3cd';
            result.style.color = '#856404';
        } else {
            result.textContent = 'መልስ: ' + change.toFixed(2) + ' ብር';
            result.style.background = '#d4edda';
            result.style.color = '#155724';
        }
    }
    
    function finishTransaction() {
        if (cart.length === 0) return;
        
        const total = cart.reduce((sum, item) => sum + item.subtotal, 0);
        
        const formData = new URLSearchParams();
        formData.append('action', 'save_sale');
        formData.append('cart', JSON.stringify(cart));
        formData.append('total', total);
        formData.append('payment', paymentMethod);
        formData.append('seller_id', '<?php echo $seller_id; ?>');
        formData.append('seller_name', '<?php echo addslashes($current_user); ?>');
        
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData.toString()
        })
        .then(response => response.text())
        .then(text => {
            try {
                const resp = JSON.parse(text);
                if (resp.success) {
                    alert('✅ ሽያጩ ተመዝግቧል! ደረሰኝ #' + resp.transaction_id);
                    cart = [];
                    document.getElementById('calc-paid').value = '';
                    renderCart();
                    document.getElementById('calc-result').textContent = 'መጠኑን ያስገቡ';
                    document.getElementById('calc-result').style.background = '';
                    setTimeout(() => location.reload(), 500);
                } else {
                    alert('❌ ስህተት: ' + resp.message);
                }
            } catch (e) {
                console.error('Error:', text);
                alert('Server error. Check console.');
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            alert('Network error: ' + error.message);
        });
    }
    
    // AJAX stock refresh every 30 seconds
    function refreshStock() {
        fetch('get_stock.php?branch_id=<?php echo $branch_id; ?>')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const productList = document.getElementById('product-list');
                    let html = '';
                    data.products.forEach(p => {
                        let stockClass = 'stock-available';
                        let stockText = parseFloat(p.stock).toFixed(1) + ' ' + p.unit;
                        let outOfStock = false;
                        
                        if (parseFloat(p.stock) <= 0) {
                            stockClass = 'stock-out';
                            stockText = 'አልቋል';
                            outOfStock = true;
                        } else if (parseFloat(p.stock) <= 5) {
                            stockClass = 'stock-low';
                        }
                        
                        const pClass = outOfStock ? 'product-btn out-of-stock' : 'product-btn';
                        const onclick = outOfStock ? '' : `onclick="addToCart(${p.id}, '${p.name.replace(/'/g, "\\'")}', ${p.price})"`;
                        
                        html += `<div class="${pClass}" ${onclick}>
                            <span class="product-name">${p.name}</span>
                            <span class="product-price">${parseFloat(p.price).toFixed(2)} ብር</span>
                            <span class="stock-badge ${stockClass}">${stockText}</span>
                        </div>`;
                    });
                    productList.innerHTML = html;
                }
            });
    }
    
    setInterval(refreshStock, 30000);
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>