<?php
require_once 'config.php';
if (!isLoggedIn()) redirect('index.php');

date_default_timezone_set('Africa/Addis_Ababa');
mysqli_query($conn, "SET time_zone = '+03:00'");

// Only admin can access this page
if (!isAdmin()) {
    redirect('seller_pos.php');
}

$admin_id = $_SESSION['user_id'];
$admin_name = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Admin';
$branch_id = $_SESSION['branch_id'] ?? 1;
$branch_name = getCurrentBranchName($conn, $branch_id);

$success_msg = '';
$error_msg = '';

// Ethiopian date
function eth_date_display($ts = null) {
    if (!$ts) $ts = time();
    $gy = (int)date('Y', $ts); $gm = (int)date('n', $ts); $gd = (int)date('j', $ts);
    $months = [1=>'መስከረም',2=>'ጥቅምት',3=>'ህዳር',4=>'ታህሳስ',5=>'ጥር',6=>'የካቲት',7=>'መጋቢት',8=>'ሚያዝያ',9=>'ግንቦት',10=>'ሰኔ',11=>'ሐምሌ',12=>'ነሐሴ',13=>'ጳጉሜ'];
    $il = (($gy%4==0&&$gy%100!=0)||$gy%400==0);
    $nyd = ($il && $gm>9) ? 12 : 11;
    $ey = ($gm>9||($gm==9&&$gd>=$nyd)) ? $gy-7 : $gy-8;
    $md = [30,30,30,30,30,30,30,30,30,30,30,30,5];
    if(($ey%4)==3) $md[12]=6;
    $nygy = ($gm<9||($gm==9&&$gd<$nyd)) ? $gy-1 : $gy;
    $inl = (($nygy%4==0&&$nygy%100!=0)||$nygy%400==0);
    $nydf = $inl ? 12 : 11;
    $nyts = mktime(0,0,0,9,$nydf,$nygy);
    $ds = floor(($ts-$nyts)/86400);
    $rem = $ds; $em = 1;
    for($m=0;$m<13;$m++){if($rem<$md[$m]){$em=$m+1;$ed=$rem+1;break;}$rem-=$md[$m];}
    return $ed.' '.$months[$em].' '.$ey;
}

$current_eth = eth_date_display();
$current_time = date('h:i:s A');

// Handle expense
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_expense'])) {
    $amount = floatval($_POST['amount']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $note = mysqli_real_escape_string($conn, $_POST['note'] ?? '');
    
    if ($amount > 0) {
        $insert = "INSERT INTO expenses (amount, category, note, created_by, branch_id, date_time) 
                   VALUES ($amount, '$category', '$note', $admin_id, $branch_id, NOW())";
        
        if (mysqli_query($conn, $insert)) {
            $success_msg = "✅ ወጪ $amount ብር በተሳካ ሁኔታ ተመዝግቧል!";
        } else {
            $error_msg = "❌ ስህተት: " . mysqli_error($conn);
        }
    }
}

// Get today's expenses
$today_start = date('Y-m-d 00:00:00');
$today_end = date('Y-m-d 23:59:59');
$today_expenses = mysqli_query($conn, "SELECT * FROM expenses WHERE branch_id=$branch_id AND date_time BETWEEN '$today_start' AND '$today_end' ORDER BY date_time DESC");
$today_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as total FROM expenses WHERE branch_id=$branch_id AND DATE(date_time)=CURDATE()"));

// Get today's sales
$today_sales = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(total_amount),0) as total FROM transactions WHERE branch_id=$branch_id AND DATE(transaction_date)=CURDATE()"));
$balance = $today_sales['total'] - $today_total['total'];

// Get all expenses for history
$all_expenses = mysqli_query($conn, "SELECT * FROM expenses WHERE branch_id=$branch_id ORDER BY date_time DESC LIMIT 100");

// Get expense categories summary
$categories_query = "SELECT category, COUNT(*) as count, SUM(amount) as total FROM expenses WHERE branch_id=$branch_id GROUP BY category ORDER BY total DESC";
$categories_result = mysqli_query($conn, $categories_query);
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ወጪ አስተዳደር - አጸደ ትጉሃን</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8B4513;
            --gold: #DAA520;
            --dark: #5C2E0B;
            --light: #FFF8DC;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ff9800;
            --info: #17a2b8;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', 'Nyala', sans-serif; }
        
        body { 
            background: radial-gradient(circle at center, #F6E27A 0%, #F4A640 35%, #D96B2B 65%, #7A1E1E 100%);
            min-height: 100vh; 
            padding: 20px; 
        }
        
        .container { max-width: 1200px; margin: 0 auto; }
        
        .header {
            background: white;
            border-radius: 15px;
            padding: 20px 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .header h1 { font-size: 1.5rem; color: var(--dark); display: flex; align-items: center; gap: 10px; }
        
        .eth-date {
            background: var(--light);
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .balance-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 600px) { .balance-row { grid-template-columns: 1fr; } }
        
        .balance-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .balance-card:hover { transform: translateY(-3px); }
        .balance-card .label { font-size: 0.8rem; color: #888; text-transform: uppercase; margin-bottom: 8px; }
        .balance-card .value { font-size: 1.5rem; font-weight: 700; }
        .balance-card.sales .value { color: var(--success); }
        .balance-card.expense .value { color: var(--warning); }
        .balance-card.balance .value { color: <?php echo $balance >= 0 ? 'var(--info)' : 'var(--danger)'; ?>; }
        
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) { .main-grid { grid-template-columns: 1fr; } }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }
        
        .card h3 {
            color: var(--dark);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: 600; color: #555; margin-bottom: 6px; font-size: 14px; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 0.9rem;
            transition: border-color 0.3s;
        }
        .form-group textarea { min-height: 80px; resize: vertical; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--primary); }
        
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary), #6B3410);
            color: var(--gold);
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(139,69,19,0.3);
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(139,69,19,0.4); }
        
        .btn-back {
            background: var(--info);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(23,162,184,0.3);
        }
        .btn-back:hover { transform: translateY(-2px); }
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: #d4edda; color: #155724; border: 2px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 2px solid #dc3545; }
        
        table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        th { background: var(--primary); color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #f0f0f0; }
        tr:hover { background: var(--light); }
        
        .category-badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            background: #e3f2fd;
            color: #1565c0;
        }
        
        .amount-negative {
            color: #dc3545;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <img src="icon.png" style="width:35px;height:35px;border-radius:8px;">
                ወጪ አስተዳደር
            </h1>
            <div style="display:flex; align-items:center; gap:15px;">
                <span class="eth-date"><i class="fas fa-calendar-alt"></i> <?php echo $current_eth; ?></span>
                <span id="liveTime" style="font-family:monospace; font-weight:600; color:var(--primary);"><?php echo $current_time; ?></span>
                <a href="admin_dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> ወደ ዳሽቦርድ</a>
            </div>
        </div>
        
        <?php if ($success_msg): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <div class="balance-row">
            <div class="balance-card sales">
                <div class="label">የዛሬ ሽያጭ</div>
                <div class="value"><?php echo number_format($today_sales['total'], 2); ?> ብር</div>
            </div>
            <div class="balance-card expense">
                <div class="label">የዛሬ ወጪ</div>
                <div class="value">- <?php echo number_format($today_total['total'], 2); ?> ብር</div>
            </div>
            <div class="balance-card balance">
                <div class="label">ቀሪ</div>
                <div class="value"><?php echo number_format($balance, 2); ?> ብር</div>
            </div>
        </div>
        
        <div class="main-grid">
            <div class="card">
                <h3><i class="fas fa-plus-circle" style="color:var(--gold);"></i> አዲስ ወጪ መዝግብ</h3>
                <form method="POST" id="expenseForm">
                    <div class="form-group">
                        <label><i class="fas fa-coins"></i> መጠን (ብር)</label>
                        <input type="number" name="amount" step="0.01" min="0.01" placeholder="የወጪውን መጠን ያስገቡ" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> ምድብ</label>
                        <select name="category" required>
                            <option value="">ምድብ ይምረጡ</option>
                            <option value="product purchase">የምርት ግዢ</option>
                            <option value="transport">ትራንስፖርት</option>
                            <option value="food">ምግብ</option>
                            <option value="office supplies">የቢሮ እቃዎች</option>
                            <option value="utilities">ውሃ እና መብራት</option>
                            <option value="rent">ኪራይ</option>
                            <option value="salary">ደሞዝ</option>
                            <option value="maintenance">ጥገና</option>
                            <option value="other">ሌላ</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-comment"></i> ማስታወሻ</label>
                        <textarea name="note" placeholder="ስለ ወጪው ማብራሪያ..."></textarea>
                    </div>
                    
                    <button type="submit" name="add_expense" class="btn-submit">
                        <i class="fas fa-save"></i> ወጪውን መዝግብ
                    </button>
                </form>
            </div>
            
            <div class="card">
                <h3><i class="fas fa-chart-pie" style="color:var(--gold);"></i> ወጪ በምድብ</h3>
                <?php if ($categories_result && mysqli_num_rows($categories_result) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ምድብ</th>
                            <th>ብዛት</th>
                            <th>ጠቅላላ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                        <tr>
                            <td><span class="category-badge"><?php echo htmlspecialchars($cat['category']); ?></span></td>
                            <td><?php echo $cat['count']; ?> ጊዜ</td>
                            <td class="amount-negative"><?php echo number_format($cat['total'], 2); ?> ብር</td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div style="text-align:center; padding:40px; color:#ccc;">
                        <i class="fas fa-chart-bar" style="font-size:40px;"></i>
                        <p>ምንም ወጪ አልተመዘገበም</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card" style="margin-top:20px;">
            <h3><i class="fas fa-list" style="color:var(--gold);"></i> የቅርብ ጊዜ ወጪዎች</h3>
            <?php if (mysqli_num_rows($all_expenses) > 0): ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>መጠን</th>
                            <th>ምድብ</th>
                            <th>ማስታወሻ</th>
                            <th>ቀን</th>
                            <th>ሰዓት</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($expense = mysqli_fetch_assoc($all_expenses)): 
                            $eth_date = eth_date_display(strtotime($expense['date_time']));
                            $time = date('h:i A', strtotime($expense['date_time']) + 10800);
                        ?>
                        <tr>
                            <td class="amount-negative"><?php echo number_format($expense['amount'], 2); ?> ብር</td>
                            <td><span class="category-badge"><?php echo htmlspecialchars($expense['category']); ?></span></td>
                            <td><?php echo htmlspecialchars(substr($expense['note'] ?? '', 0, 50)); ?></td>
                            <td><?php echo $eth_date; ?></td>
                            <td><?php echo $time; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div style="text-align:center; padding:40px; color:#ccc;">
                    <i class="fas fa-inbox" style="font-size:40px;"></i>
                    <p>ምንም ወጪ አልተመዘገበም</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    function updateTime() {
        const now = new Date();
        let h = now.getHours();
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        document.getElementById('liveTime').textContent = `${h}:${m}:${s} ${ampm}`;
    }
    setInterval(updateTime, 1000);
    
    document.getElementById('expenseForm').addEventListener('submit', function(e) {
        const amount = parseFloat(this.querySelector('input[name="amount"]').value);
        if (amount <= 0) {
            e.preventDefault();
            alert('እባክዎ ትክክለኛ መጠን ያስገቡ!');
        }
    });
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>