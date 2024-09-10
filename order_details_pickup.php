<?php
session_start();
include 'includes/db_connect.php';

// รับค่า order_pickup_id จาก URL
$order_pickup_id = isset($_GET['order_pickup_id']) ? intval($_GET['order_pickup_id']) : 0;

if ($order_pickup_id === 0) {
    $_SESSION['error_message'] = "ไม่พบ Order ID";
    header("Location: error.php");
    exit();
}

// ดึงข้อมูลออเดอร์จากฐานข้อมูล
$sql_order = "SELECT o.order_pickup_id, o.emp_id, o.order_date
              FROM order_pickup o
              WHERE o.order_pickup_id = ?";
$stmt_order = $conn->prepare($sql_order);
$stmt_order->bind_param("i", $order_pickup_id);
$stmt_order->execute();
$result_order = $stmt_order->get_result();

if ($result_order->num_rows === 0) {
    $_SESSION['error_message'] = "ไม่พบข้อมูลออเดอร์";
    header("Location: error.php");
    exit();
}

$order = $result_order->fetch_assoc();

// ดึงรายการอาหารจากฐานข้อมูลพร้อมชื่อและราคา
$sql_food_items = "SELECT pd.order_pickup_detail_id, pd.item_id, pd.quantity, mi.name, mi.price
                   FROM order_pickup_details pd
                   JOIN menuitems mi ON pd.item_id = mi.item_id
                   WHERE pd.order_pickup_id = ?";
$stmt_food_items = $conn->prepare($sql_food_items);
$stmt_food_items->bind_param("i", $order_pickup_id);
$stmt_food_items->execute();
$result_food_items = $stmt_food_items->get_result();

// คำนวณราคารวม
$total_price = 0;
$food_items = [];
while ($food_item = $result_food_items->fetch_assoc()) {
    $total_price += $food_item['price'] * $food_item['quantity'];
    $food_items[] = $food_item;
}

// ประมวลผลข้อมูลเมื่อมีการกดปุ่มชำระเงิน
if (isset($_POST['payment'])) {
    $payment_method = $_POST['payment_method'];

    $conn->begin_transaction();

    try {
        // บันทึกข้อมูลการชำระเงินในตาราง payments
        $sql_payment = "INSERT INTO payments (order_id, order_type, payment_time, total_amount, payment_status) 
                        VALUES (?, ?, NOW(), ?, ?)";
        $stmt_payment = $conn->prepare($sql_payment);
        $order_type = 2; // ประเภทออเดอร์สั่งกลับบ้าน
        $payment_status = 1; // 1 หมายถึง ชำระเงินแล้ว
        $stmt_payment->bind_param("iidi", $order_pickup_id, $order_type, $total_price, $payment_status);
        $stmt_payment->execute();
        $payment_id = $stmt_payment->insert_id;

        // บันทึกข้อมูลในตาราง bills
        $sql_bill = "INSERT INTO bills (payment_id, order_id, total_amount, bill_date, payment_method, status) 
                     VALUES (?, ?, ?, NOW(), ?, 'paid')";
        $stmt_bill = $conn->prepare($sql_bill);
        $stmt_bill->bind_param("iids", $payment_id, $order_pickup_id, $total_price, $payment_method);
        $stmt_bill->execute();

        $conn->commit();

        $_SESSION['success_message'] = "ชำระเงินเรียบร้อยแล้ว!";
        $_SESSION['payment_id'] = $payment_id;
        header("Location: payment_success.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "เกิดข้อผิดพลาดในการชำระเงิน: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดออเดอร์สั่งกลับบ้าน</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/cashier_details.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center">รายละเอียดออเดอร์สั่งกลับบ้าน</h1>

        <!-- แสดงข้อความสถานะ -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <!-- ข้อมูลออเดอร์ -->
        <div class="mb-4">
            <h3>ข้อมูลออเดอร์</h3>
            <p>รหัสออเดอร์: <?php echo htmlspecialchars($order['order_pickup_id']); ?></p>
            <p>วันที่สั่ง: <?php echo htmlspecialchars($order['order_date']); ?></p>
        </div>

        <!-- รายการอาหาร -->
        <div class="mb-4">
            <h3>รายการอาหาร</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ชื่ออาหาร</th>
                        <th>จำนวน</th>
                        <th>ราคา/หน่วย</th>
                        <th>รวม</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($food_items as $food_item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($food_item['name']); ?></td>
                            <td><?php echo htmlspecialchars($food_item['quantity']); ?></td>
                            <td><?php echo number_format($food_item['price'], 2); ?> บาท</td>
                            <td><?php echo number_format($food_item['price'] * $food_item['quantity'], 2); ?> บาท</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ราคารวม -->
        <div class="total-price-section mb-4">
            <h4 class="total-price-title">ราคารวมทั้งหมด</h4>
            <p class="total-price-after-discount">ราคารวมทั้งหมด: <?php echo number_format($total_price, 2); ?> บาท</p>
        </div>

        <!-- เลือกวิธีการชำระเงิน -->
        <div class="mb-4">
            <h3>เลือกวิธีการชำระเงิน:</h3>
            <form method="post" action="">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash" required>
                    <label class="form-check-label" for="cash">เงินสด</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment_method" id="qr" value="qr">
                    <label class="form-check-label" for="qr">QR Code พร้อมเพย์</label>
                </div>
                <button type="submit" name="payment" class="btn btn-success mt-4">ยืนยันการชำระเงิน</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>