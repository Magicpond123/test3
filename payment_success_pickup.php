<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['payment_id'])) {
    header("Location: error.php");
    exit();
}

$payment_id = $_SESSION['payment_id'];

// ดึงข้อมูลการชำระเงินและรายละเอียดออเดอร์
$sql = "SELECT p.payment_id, p.order_id, p.total_amount, p.payment_time, 
               op.emp_id, op.order_date
        FROM payments p
        JOIN order_pickup op ON p.order_id = op.order_pickup_id
        WHERE p.payment_id = ? AND p.order_type = 2";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $payment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "ไม่พบข้อมูลการชำระเงิน";
    header("Location: error.php");
    exit();
}

$payment_data = $result->fetch_assoc();

// ดึงรายการอาหารที่สั่ง
$sql_items = "SELECT mi.name, opd.quantity
              FROM order_pickup_details opd
              JOIN menuitems mi ON opd.item_id = mi.item_id
              WHERE opd.order_pickup_id = ?";

$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $payment_data['order_id']);
$stmt_items->execute();
$result_items = $stmt_items->get_result();

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ชำระเงินสำเร็จ - สั่งกลับบ้าน</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .success-container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .success-icon {
            font-size: 48px;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-container text-center">
            <i class="success-icon bi bi-check-circle-fill"></i>
            <h2 class="mt-3">การชำระเงินสำเร็จ</h2>
            <p>ขอบคุณสำหรับการสั่งอาหาร</p>
            
            <div class="mt-4">
                <h4>รายละเอียดการสั่งซื้อ</h4>
                <p><strong>หมายเลขคำสั่งซื้อ:</strong> <?php echo $payment_data['order_id']; ?></p>
                <p><strong>วันที่สั่งซื้อ:</strong> <?php echo date('d/m/Y H:i', strtotime($payment_data['order_date'])); ?></p>
                <p><strong>ยอดรวม:</strong> <?php echo number_format($payment_data['total_amount'], 2); ?> บาท</p>
            </div>

            <div class="mt-4">
                <h4>รายการอาหารที่สั่ง</h4>
                <ul class="list-group">
                    <?php while ($item = $result_items->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($item['name']); ?>
                            <span class="badge bg-primary rounded-pill"><?php echo $item['quantity']; ?></span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <div class="mt-4">
                <p>กรุณานำหมายเลขคำสั่งซื้อมาแสดงเมื่อมารับอาหาร</p>
                <p>เราจะแจ้งให้ทราบเมื่ออาหารพร้อมรับ</p>
            </div>

            <div class="mt-4">
                <a href="menu_order_pickup.php" class="btn btn-primary">กลับหน้าหลัก</a>
                <a href="bills_pickup.php?payment_id=<?php echo $payment_id; ?>" class="btn btn-secondary">ดูใบเสร็จ</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>