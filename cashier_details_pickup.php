<?php
session_start();
include 'includes/db_connect.php';

// เพิ่ม error reporting สำหรับการ debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

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
if ($stmt_order === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
if (!$stmt_order->bind_param("i", $order_pickup_id)) {
    die('Binding parameters failed: ' . htmlspecialchars($stmt_order->error));
}
if (!$stmt_order->execute()) {
    die('Execute failed: ' . htmlspecialchars($stmt_order->error));
}
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
if ($stmt_food_items === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
if (!$stmt_food_items->bind_param("i", $order_pickup_id)) {
    die('Binding parameters failed: ' . htmlspecialchars($stmt_food_items->error));
}
if (!$stmt_food_items->execute()) {
    die('Execute failed: ' . htmlspecialchars($stmt_food_items->error));
}
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
        if ($stmt_payment === false) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $order_type = 2; // ประเภทออเดอร์สั่งกลับบ้าน
        $payment_status = 1; // 1 หมายถึง ชำระเงินแล้ว
        if (!$stmt_payment->bind_param("iidi", $order_pickup_id, $order_type, $total_price, $payment_status)) {
            throw new Exception('Binding parameters failed: ' . $stmt_payment->error);
        }
        if (!$stmt_payment->execute()) {
            throw new Exception('Execute failed: ' . $stmt_payment->error);
        }
        $payment_id = $stmt_payment->insert_id;

        // บันทึกข้อมูลในตาราง bills
        $sql_bill = "INSERT INTO bills (payment_id, order_id, total_amount, bill_date, payment_method, status) 
                     VALUES (?, ?, ?, NOW(), ?, 'paid')";
        $stmt_bill = $conn->prepare($sql_bill);
        if ($stmt_bill === false) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        if (!$stmt_bill->bind_param("iids", $payment_id, $order_pickup_id, $total_price, $payment_method)) {
            throw new Exception('Binding parameters failed: ' . $stmt_bill->error);
        }
        if (!$stmt_bill->execute()) {
            throw new Exception('Execute failed: ' . $stmt_bill->error);
        }

        // เสร็จสิ้นการทำธุรกรรม
        if (!$conn->commit()) {
            throw new Exception('Commit failed: ' . $conn->error);
        }

        $_SESSION['success_message'] = "ชำระเงินเรียบร้อยแล้ว!";
        $_SESSION['payment_id'] = $payment_id;
        header("Location: payment_success_pickup.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Payment Error: " . $e->getMessage());
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
        <!-- แสดงข้อความสถานะ -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>

        <!-- ข้อมูลออเดอร์ -->
        <div class="mb-4">
            <h3>ข้อมูลออเดอร์</h3>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="order_id" class="form-label">รหัสออเดอร์</label>
                    <input type="text" id="order_id" class="form-control" value="<?php echo htmlspecialchars($order['order_pickup_id']); ?>" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="order_date" class="form-label">วันที่สั่ง</label>
                    <input type="text" id="order_date" class="form-control" value="<?php echo htmlspecialchars($order['order_date']); ?>" readonly>
                </div>
            </div>
        </div>

        <!-- รายการอาหาร -->
        <div class="mb-4">
            <h3>รายการอาหาร</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>รหัสอาหาร</th>
                        <th>ชื่ออาหาร</th>
                        <th>จำนวน</th>
                        <th>ราคา/หน่วย</th>
                        <th>รวม</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($food_items as $food_item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($food_item['item_id']); ?></td>
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
                    <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash" required onclick="toggleQRCode(false)">
                    <label class="form-check-label" for="cash">เงินสด</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment_method" id="qr" value="qr" onclick="toggleQRCode(true)">
                    <label class="form-check-label" for="qr">QR Code พร้อมเพย์</label>
                </div>
                <button type="submit" name="payment" class="btn btn-success mt-4">ยืนยันการชำระเงิน</button>
            </form>
        </div>

        <!-- แสดง QR Code เมื่อเลือกชำระด้วยพร้อมเพย์ -->
        <div class="qr-code-section mt-4" id="qr-code-section" style="display: none;">
            <h3>สแกน QR Code เพื่อชำระเงิน:</h3>
            <img src="qrcodes/pp.jfif" alt="QR Code" class="img-fluid">
        </div>
    </div>

    <script>
        function toggleQRCode(show) {
            var qrSection = document.getElementById('qr-code-section');
            if (show) {
                qrSection.style.display = 'block';
            } else {
                qrSection.style.display = 'none';
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>