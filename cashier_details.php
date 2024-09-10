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

// รับค่า order_id จาก URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id === 0) {
    $_SESSION['error_message'] = "ไม่พบ Order ID";
    header("Location: error.php");
    exit();
}

// ดึงข้อมูลออเดอร์จากฐานข้อมูลพร้อมหมายเลขโต๊ะ
$sql_order = "SELECT o.order_buffet_id, o.table_id, o.adult, o.child, o.order_date, t.table_number
              FROM order_buffet o
              JOIN tables t ON o.table_id = t.table_id
              WHERE o.order_buffet_id = ?";
$stmt_order = $conn->prepare($sql_order);
if ($stmt_order === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
if (!$stmt_order->bind_param("i", $order_id)) {
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
$sql_food_items = "SELECT bd.order_buffet_detail_id, bd.item_id, bd.quantity, bd.status, mi.name, mi.price
                   FROM order_buffet_details bd
                   JOIN menuitems mi ON bd.item_id = mi.item_id
                   WHERE bd.order_buffet_id = ? AND bd.status = 3";
$stmt_food_items = $conn->prepare($sql_food_items);
if ($stmt_food_items === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
if (!$stmt_food_items->bind_param("i", $order_id)) {
    die('Binding parameters failed: ' . htmlspecialchars($stmt_food_items->error));
}
if (!$stmt_food_items->execute()) {
    die('Execute failed: ' . htmlspecialchars($stmt_food_items->error));
}
$result_food_items = $stmt_food_items->get_result();

// ดึงโปรโมชั่นที่ใช้ได้
$sql_promotions = "SELECT * FROM promotions WHERE start_date <= CURDATE() AND (end_date >= CURDATE() OR end_date IS NULL)";
$result_promotions = $conn->query($sql_promotions);
if ($result_promotions === false) {
    die('Query failed: ' . htmlspecialchars($conn->error));
}

// คำนวณราคารวมสำหรับอาหาร
$total_food_price = 0;
$food_items = [];
while ($food_item = $result_food_items->fetch_assoc()) {
    $total_food_price += $food_item['price'] * $food_item['quantity'];
    $food_items[] = $food_item;
}

// คำนวณราคารวมสำหรับผู้ใหญ่และเด็ก
$adult_price = 149;
$child_price = 99;
$total_people_price = ($order['adult'] * $adult_price) + ($order['child'] * $child_price);

// ราคารวมทั้งหมดก่อนส่วนลด
$total_price = $total_food_price + $total_people_price;

// ฟังก์ชันคำนวณส่วนลด
function calculateGroupDiscount($totalPeople)
{
    $discountGroups = floor($totalPeople / 4);
    return $discountGroups * 149; // 149 บาทต่อทุกๆ 4 คน
}

$discount_amount = 0;
$discount_type = '';
$promotion = null;

// ตรวจสอบว่าผู้ใช้เลือกโปรโมชั่นหรือไม่
if (isset($_POST['promotion_id']) && $_POST['promotion_id'] != '') {
    $promotion_id = intval($_POST['promotion_id']);
    $sql_promotion = "SELECT * FROM promotions WHERE promotion_id = ?";
    $stmt_promotion = $conn->prepare($sql_promotion);
    if ($stmt_promotion === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    if (!$stmt_promotion->bind_param("i", $promotion_id)) {
        die('Binding parameters failed: ' . htmlspecialchars($stmt_promotion->error));
    }
    if (!$stmt_promotion->execute()) {
        die('Execute failed: ' . htmlspecialchars($stmt_promotion->error));
    }
    $result_promotion = $stmt_promotion->get_result();
    if ($result_promotion->num_rows > 0) {
        $promotion = $result_promotion->fetch_assoc();
        $discount_type = $promotion['type'];
        if ($discount_type == 'discount-type-person') {
            $total_people = $order['adult'] + $order['child'];
            $discount_amount = calculateGroupDiscount($total_people);
        } elseif ($discount_type == 'discount-type-birthday') {
            $discount_percent = $promotion['discount_percent'];
            $discount_amount = $total_price * ($discount_percent / 100);
        }
        $total_price = max(0, $total_price - $discount_amount);
    } else {
        $_SESSION['error_message'] = "ไม่พบโปรโมชั่นที่เลือก";
    }
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
        $order_type = 1; // ประเภทออเดอร์บุฟเฟต์
        $payment_status = 1; // 1 หมายถึง ชำระเงินแล้ว
        if (!$stmt_payment->bind_param("iidi", $order_id, $order_type, $total_price, $payment_status)) {
            throw new Exception('Binding parameters failed: ' . $stmt_payment->error);
        }
        if (!$stmt_payment->execute()) {
            throw new Exception('Execute failed: ' . $stmt_payment->error);
        }
        $payment_id = $stmt_payment->insert_id;

        // บันทึกข้อมูลโปรโมชันที่ใช้ (ถ้ามี)
        if (isset($_POST['promotion_id']) && $_POST['promotion_id'] != '') {
            $sql_order_promotion = "INSERT INTO order_promotions (order_buffet_id, promotion_id) VALUES (?, ?)";
            $stmt_order_promotion = $conn->prepare($sql_order_promotion);
            if ($stmt_order_promotion === false) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }
            if (!$stmt_order_promotion->bind_param("ii", $order_id, $_POST['promotion_id'])) {
                throw new Exception('Binding parameters failed: ' . $stmt_order_promotion->error);
            }
            if (!$stmt_order_promotion->execute()) {
                throw new Exception('Execute failed: ' . $stmt_order_promotion->error);
            }
        }

        // อัปเดตสถานะการชำระเงินในตาราง order_buffet
        $sql_update_order = "UPDATE order_buffet SET payment_status = 1 WHERE order_buffet_id = ?";
        $stmt_update_order = $conn->prepare($sql_update_order);
        if ($stmt_update_order === false) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        if (!$stmt_update_order->bind_param("i", $order_id)) {
            throw new Exception('Binding parameters failed: ' . $stmt_update_order->error);
        }
        if (!$stmt_update_order->execute()) {
            throw new Exception('Execute failed: ' . $stmt_update_order->error);
        }

        $sql_update_table = "UPDATE tables SET table_status = ? WHERE table_id = ?";
        $stmt_update_table = $conn->prepare($sql_update_table);
        if ($stmt_update_table === false) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $table_status = 1; 
        if (!$stmt_update_table->bind_param("ii", $table_status, $order['table_id'])) {
            throw new Exception('Binding parameters failed: ' . $stmt_update_table->error);
        }
        if (!$stmt_update_table->execute()) {
            throw new Exception('Execute failed: ' . $stmt_update_table->error);
        }

        // เสร็จสิ้นการทำธุรกรรม
        if (!$conn->commit()) {
            throw new Exception('Commit failed: ' . $conn->error);
        }

        $_SESSION['success_message'] = "ชำระเงินเรียบร้อยแล้ว!";
        $_SESSION['payment_id'] = $payment_id;
        header("Location: payment_success.php");
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
    <title>รายละเอียดออเดอร์</title>
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
                    <label for="table_id" class="form-label">หมายเลขโต๊ะ</label>
                    <input type="text" id="table_id" class="form-control" value="<?php echo htmlspecialchars($order['table_number']); ?>" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="ad" class="form-label">จำนวนผู้ใหญ่</label>
                    <input type="number" id="ad" class="form-control" value="<?php echo htmlspecialchars($order['adult']); ?>" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="hc" class="form-label">จำนวนเด็ก</label>
                    <input type="number" id="hc" class="form-control" value="<?php echo htmlspecialchars($order['child']); ?>" readonly>
                </div>
            </div>
        </div>

        <!-- รายการอาหาร -->
        <div class="mb-4">
            <h3>รายการอาหาร</h3>
            <div class="alert alert-info">
                <strong>หมายเหตุ:</strong> การคิดเงินจะคำนวณเฉพาะรายการอาหารที่จัดส่งแล้วเท่านั้น
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>รหัสอาหาร</th>
                        <th>ชื่ออาหาร</th>
                        <th>จำนวน</th>
                        <th>ราคา/หน่วย</th>
                        <th>รวม</th>
                        <th>สถานะ</th>
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
                            <td><?php echo ($food_item['status'] == 3) ? 'จัดส่งแล้ว' : 'รอดำเนินการ'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ราคารวม -->
        <div class="total-price-section mb-4">
            <h4 class="total-price-title">ราคารวมทั้งหมด</h4>
            <p class="total-food-price">ราคารวมอาหาร: <?php echo number_format($total_food_price, 2); ?> บาท</p>
            <p class="total-people-price">ราคารวมสำหรับผู้ใหญ่และเด็ก: <?php echo number_format($total_people_price, 2); ?> บาท</p>
            <?php if ($discount_amount > 0): ?>
                <p class="total-discount">ส่วนลด: <?php echo number_format($discount_amount, 2); ?> บาท</p>
                <p class="total-price-after-discount">ราคารวมหลังส่วนลด: <?php echo number_format($total_price, 2); ?> บาท</p>
            <?php else: ?>
                <p class="total-price-after-discount">ราคารวมทั้งหมด: <?php echo number_format($total_price, 2); ?> บาท</p>
            <?php endif; ?>
        </div>

        <!-- เลือกโปรโมชั่น -->
        <div class="mb-4">
            <h3>เลือกโปรโมชั่น:</h3>
            <form method="post" action="">
                <select name="promotion_id" class="form-select mb-3" onchange="this.form.submit()">
                    <option value="">ไม่ใช้โปรโมชั่น</option>
                    <?php while ($promo = $result_promotions->fetch_assoc()) { ?>
                        <option value="<?php echo $promo['promotion_id']; ?>" <?php echo isset($_POST['promotion_id']) && $_POST['promotion_id'] == $promo['promotion_id'] ? 'selected' : ''; ?>>
                            <?php
                            if ($promo['type'] == 'discount-type-person') {
                                echo $promo['name'] . " (มา 4 จ่าย 3)";
                            } elseif ($promo['type'] == 'discount-type-birthday') {
                                echo $promo['name'] . " (ส่วนลด " . $promo['discount_percent'] . "%)";
                            }
                            ?>
                        </option>
                    <?php } ?>
                </select>

                <h3>เลือกวิธีการชำระเงิน:</h3>
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