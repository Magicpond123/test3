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
               o.table_id, o.adult, o.child, o.order_date, t.table_number
        FROM payments p
        JOIN order_buffet o ON p.order_id = o.order_buffet_id
        JOIN tables t ON o.table_id = t.table_id
        WHERE p.payment_id = ? AND p.order_type = 1";

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
$sql_items = "SELECT mi.name, obd.quantity
              FROM order_buffet_details obd
              JOIN menuitems mi ON obd.item_id = mi.item_id
              WHERE obd.order_buffet_id = ?";

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
    <title>ชำระเงินสำเร็จ - บุฟเฟ่ต์</title>
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
            <p>ขอบคุณสำหรับการใช้บริการ</p>
            
            <div class="mt-4">
                <h4>รายละเอียดการสั่งซื้อ</h4>
                <p><strong>หมายเลขคำสั่งซื้อ:</strong> <?php echo $payment_data['order_id']; ?></p>
                <p><strong>โต๊ะ:</strong> <?php echo $payment_data['table_number']; ?></p>
                <p><strong>จำนวนลูกค้า:</strong> ผู้ใหญ่ <?php echo $payment_data['adult']; ?> คน, เด็ก <?php echo $payment_data['child']; ?> คน</p>
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
                <p>หากมีข้อสงสัยหรือต้องการความช่วยเหลือ กรุณาติดต่อพนักงาน</p>
            </div>

            <div class="mt-4">
                <a href="manage_orders.php" class="btn btn-primary">กลับหน้าจัดการออเดอร์</a>
                <a href="bills.php?payment_id=<?php echo $payment_id; ?>" class="btn btn-secondary">ดูใบเสร็จ</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
unset($_SESSION['success_message']);
unset($_SESSION['payment_id']);
?>