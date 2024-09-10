<?php
session_start();
include 'includes/db_connect.php';

// ตรวจสอบว่ามี payment_id ถูกส่งมาหรือไม่
if (!isset($_GET['payment_id'])) {
    $_SESSION['error_message'] = "ไม่พบข้อมูลการชำระเงิน";
    header("Location: error.php");
    exit();
}

$payment_id = intval($_GET['payment_id']);

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

$bill_data = $result->fetch_assoc();

// ดึงรายการอาหารที่สั่ง
$sql_items = "SELECT mi.name, opd.quantity, mi.price
              FROM order_pickup_details opd
              JOIN menuitems mi ON opd.item_id = mi.item_id
              WHERE opd.order_pickup_id = ?";

$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $bill_data['order_id']);
$stmt_items->execute();
$result_items = $stmt_items->get_result();

// ข้อมูลร้าน
$restaurant_name = "ต้วงหมูกระทะ";
$restaurant_address = "123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพมหานคร 10110";
$restaurant_phone = "02-123-4567";

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบเสร็จรับเงิน (สั่งกลับบ้าน) - <?php echo $restaurant_name; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h2><?php echo $restaurant_name; ?></h2>
                <p><?php echo $restaurant_address; ?></p>
                <p>โทร: <?php echo $restaurant_phone; ?></p>
            </div>
        </div>

        <div class="row">
            <div class="col-6">
                <p><strong>เลขที่บิล:</strong> <?php echo $bill_data['payment_id']; ?></p>
                <p><strong>วันที่สั่ง:</strong> <?php echo date('d/m/Y H:i', strtotime($bill_data['order_date'])); ?></p>
            </div>
            <div class="col-6 text-end">
                <p><strong>วันที่ชำระเงิน:</strong> <?php echo date('d/m/Y H:i', strtotime($bill_data['payment_time'])); ?></p>
                <p><strong>ประเภท:</strong> สั่งกลับบ้าน</p>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th>รายการ</th>
                            <th class="text-end">จำนวน</th>
                            <th class="text-end">ราคา/หน่วย</th>
                            <th class="text-end">รวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $subtotal = 0;
                        while ($item = $result_items->fetch_assoc()) {
                            $item_total = $item['quantity'] * $item['price'];
                            $subtotal += $item_total;
                        ?>
                            <tr>
                                <td><?php echo $item['name']; ?></td>
                                <td class="text-end"><?php echo $item['quantity']; ?></td>
                                <td class="text-end"><?php echo number_format($item['price'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($item_total, 2); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>ยอดรวมสุทธิ</strong></td>
                            <td class="text-end"><strong><?php echo number_format($subtotal, 2); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12 text-center">
                <p>ขอบคุณที่ใช้บริการ</p>
                <p>กรุณาเก็บใบเสร็จไว้เป็นหลักฐาน</p>
            </div>
        </div>

        <div class="row mt-4 no-print">
            <div class="col-12 text-center">
                <button onclick="window.print();" class="btn btn-primary">พิมพ์ใบเสร็จ</button>
                <a href="manage_orders.php" class="btn btn-secondary">กลับหน้าจัดการออเดอร์</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>