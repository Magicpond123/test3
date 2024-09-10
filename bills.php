<?php
session_start();
include 'includes/db_connect.php';

// กำหนดค่าคงที่สำหรับราคาบุฟเฟ่ต์
define('ADULT_PRICE', 149);
define('CHILD_PRICE', 99);

// ตรวจสอบว่ามี payment_id ถูกส่งมาหรือไม่
if (!isset($_GET['payment_id'])) {
    $_SESSION['error_message'] = "ไม่พบข้อมูลการชำระเงิน";
    header("Location: error.php");
    exit();
}

function calculateGroupDiscount($totalPeople)
{
    $discountGroups = floor($totalPeople / 4);
    return $discountGroups * ADULT_PRICE; // ใช้ ADULT_PRICE แทน 149
}
$payment_id = intval($_GET['payment_id']);

// ดึงข้อมูลการชำระเงินและรายละเอียดออเดอร์
$sql = "SELECT p.payment_id, p.order_id, p.total_amount, p.payment_time, 
               o.table_id, o.adult, o.child,
               t.table_number
        FROM payments p
        JOIN order_buffet o ON p.order_id = o.order_buffet_id
        JOIN tables t ON o.table_id = t.table_id
        WHERE p.payment_id = ?";

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
$sql_items = "SELECT mi.name, obd.quantity, mi.price
              FROM order_buffet_details obd
              JOIN menuitems mi ON obd.item_id = mi.item_id
              WHERE obd.order_buffet_id = ?";

$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $bill_data['order_id']);
$stmt_items->execute();
$result_items = $stmt_items->get_result();

// ดึงข้อมูลโปรโมชั่นที่ใช้ (ถ้ามี)
$sql_promotion = "SELECT p.name, p.discount, p.discount_percent, p.type
                  FROM order_promotions op
                  JOIN promotions p ON op.promotion_id = p.promotion_id
                  WHERE op.order_buffet_id = ?";

$stmt_promotion = $conn->prepare($sql_promotion);
$stmt_promotion->bind_param("i", $bill_data['order_id']);
$stmt_promotion->execute();
$result_promotion = $stmt_promotion->get_result();
$promotion = $result_promotion->fetch_assoc();

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
    <title>ใบเสร็จรับเงิน - <?php echo $restaurant_name; ?></title>
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
                <p><strong>วันที่:</strong> <?php echo date('d/m/Y H:i', strtotime($bill_data['payment_time'])); ?></p>
            </div>
            <div class="col-6 text-end">
                <p><strong>โต๊ะ:</strong> <?php echo $bill_data['table_number']; ?></p>
                <p><strong>จำนวนลูกค้า:</strong> ผู้ใหญ่ <?php echo $bill_data['adult']; ?> คน, เด็ก <?php echo $bill_data['child']; ?> คน</p>
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
                        <tr>
                            <td>บุฟเฟต์ผู้ใหญ่</td>
                            <td class="text-end"><?php echo $bill_data['adult']; ?></td>
                            <td class="text-end"><?php echo number_format(ADULT_PRICE, 2); ?></td>
                            <td class="text-end"><?php echo number_format($bill_data['adult'] * ADULT_PRICE, 2); ?></td>
                        </tr>
                        <tr>
                            <td>บุฟเฟต์เด็ก</td>
                            <td class="text-end"><?php echo $bill_data['child']; ?></td>
                            <td class="text-end"><?php echo number_format(CHILD_PRICE, 2); ?></td>
                            <td class="text-end"><?php echo number_format($bill_data['child'] * CHILD_PRICE, 2); ?></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <?php
                        $total_buffet_price = ($bill_data['adult'] * ADULT_PRICE) + ($bill_data['child'] * CHILD_PRICE);
                        $total_before_discount = $subtotal + $total_buffet_price;
                        ?>
                        <tr>
                            <td colspan="3" class="text-end"><strong>รวม</strong></td>
                            <td class="text-end"><?php echo number_format($total_before_discount, 2); ?></td>
                        </tr>
                        <?php
                        $discount_amount = 0;
                        if ($promotion) {
                            if ($promotion['type'] == 'discount-type-person') {
                                $discount_amount = calculateGroupDiscount($bill_data['adult'] + $bill_data['child']);
                            } elseif ($promotion['type'] == 'discount-type-birthday') {
                                $discount_amount = $total_before_discount * ($promotion['discount_percent'] / 100);
                            }
                        ?>
                            <tr>
                                <td colspan="3" class="text-end"><strong>ส่วนลด (<?php echo $promotion['name']; ?>)</strong></td>
                                <td class="text-end">- <?php echo number_format($discount_amount, 2); ?></td>
                            </tr>
                        <?php } ?>
                        <?php $total_after_discount = $total_before_discount - $discount_amount; ?>
                        <tr>
                            <td colspan="3" class="text-end"><strong>ยอดรวมสุทธิ</strong></td>
                            <td class="text-end"><strong><?php echo number_format($total_after_discount, 2); ?></strong></td>
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