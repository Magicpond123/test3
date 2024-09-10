<?php
session_start();
include 'includes/db_connect.php';

date_default_timezone_set('Asia/Bangkok');

// ตรวจสอบว่ามีการสร้างตะกร้าบุฟเฟ่ต์หรือไม่ ถ้าไม่ให้สร้าง
if (!isset($_SESSION['cart_buffet'])) {
    $_SESSION['cart_buffet'] = [];
}

// ตรวจสอบว่ามีการเลือกโต๊ะหรือยัง
if (!isset($_SESSION['table_id'])) {
    die("กรุณาเลือกโต๊ะก่อนทำการสั่งอาหาร");
}

// ลบสินค้าจากตะกร้า
if (isset($_POST['remove'])) {
    $itemToRemove = htmlspecialchars($_POST['remove']);
    if (isset($_SESSION['cart_buffet'][$itemToRemove])) {
        unset($_SESSION['cart_buffet'][$itemToRemove]);
    }
}

// อัปเดตจำนวนสินค้าตามที่ลูกค้ากำหนด
if (isset($_POST['update_quantity'])) {
    $item_id = htmlspecialchars($_POST['item_id']);
    $new_quantity = (int) $_POST['quantity'];
    if (isset($_SESSION['cart_buffet'][$item_id])) {
        if ($new_quantity <= 0) {
            unset($_SESSION['cart_buffet'][$item_id]);
        } else {
            $_SESSION['cart_buffet'][$item_id]['quantity'] = $new_quantity;
        }
    }
}

// ตรวจสอบว่ามีการสั่งอาหารครบถ้วนแล้วหรือไม่
if (isset($_POST['action']) && $_POST['action'] === 'complete_order') {
    $orderSuccess = true;
    $conn->begin_transaction(); // เริ่มต้นธุรกรรม
    try {
        $table_id = $_SESSION['table_id']; 
        $emp_id = 2; // ตัวอย่าง employee_id
        $order_date = date('Y-m-d H:i:s');
        $adults = $_SESSION['adults'] ?? 1; // ตรวจสอบให้แน่ใจว่ามีข้อมูล
        $children = $_SESSION['children'] ?? 0;
        $price_adults = $_SESSION['price_adults'] ?? 149;
        $price_children = $_SESSION['price_children'] ?? 99;

        // เพิ่มข้อมูลลงในตาราง order_buffet
        $stmt = $conn->prepare("INSERT INTO order_buffet (table_id, emp_id, order_date, adult, child, price_adult, price_child) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            throw new Exception('Prepare failed: ' . htmlspecialchars($conn->error));
        }
        $stmt->bind_param("iisiiii", $table_id, $emp_id, $order_date, $adults, $children, $price_adults, $price_children);
        if (!$stmt->execute()) {
            throw new Exception("Buffet order insertion failed: " . $stmt->error);
        }
        $order_buffet_id = $stmt->insert_id;
        $stmt->close();
        
        // เพิ่มข้อมูลสินค้าในตาราง order_buffet_details
        foreach ($_SESSION['cart_buffet'] as $item_id => $details) {
            $status = 1; // สถานะของรายการอาหาร
            $stmt = $conn->prepare("INSERT INTO order_buffet_details (order_buffet_id, item_id, quantity, status) VALUES (?, ?, ?, ?)");
            if ($stmt === false) {
                throw new Exception('Prepare failed: ' . htmlspecialchars($conn->error));
            }
            $quantity = $details['quantity'];
            $stmt->bind_param("iiii", $order_buffet_id, $item_id, $quantity, $status);
            if (!$stmt->execute()) {
                throw new Exception("Buffet order details insertion failed: " . $stmt->error);
            }
            $stmt->close();
        }

        // อัปเดตสถานะโต๊ะให้เป็นไม่ว่าง (2)
        $stmt = $conn->prepare("UPDATE tables SET table_status = 2 WHERE table_id = ?");
        if ($stmt === false) {
            throw new Exception("Table status update failed: " . $conn->error);
        }
        $stmt->bind_param("i", $table_id);
        if (!$stmt->execute()) {
            throw new Exception("Table status update execution failed: " . $stmt->error);
        }
        $stmt->close();

        // ล้างตะกร้าและ session
        $_SESSION['cart_buffet'] = [];
        $_SESSION['adults'] = 1;
        $_SESSION['children'] = 0;
        $_SESSION['price_adults'] = 0;
        $_SESSION['price_children'] = 0;

        $conn->commit(); // บันทึกการทำธุรกรรม
        header("Location: success_page_buffet.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback(); // ยกเลิกการทำธุรกรรมหากมีข้อผิดพลาด
        echo "Error: " . $e->getMessage(); // แสดงข้อความข้อผิดพลาด
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตะกร้าสินค้า (บุฟเฟ่ต์)</title>
    <link rel="stylesheet" href="css/cart_buffet.css">
</head>

<body>
    <div class="page-container">
        <div class="header-container">
            <h1>ตะกร้าสินค้า (บุฟเฟ่ต์)</h1>
        </div>
        <div class="cart-container">
            <?php if (empty($_SESSION['cart_buffet'])) : ?>
                <p>ไม่มีรายการอาหารในตะกร้า</p>
            <?php else : ?>
                <?php foreach ($_SESSION['cart_buffet'] as $item_id => $details) : ?>
                    <?php
                    // ดึงรายละเอียดรายการอาหารจากฐานข้อมูล
                    $stmt = $conn->prepare("SELECT name, price, image_path FROM menuitems WHERE item_id = ?");
                    $stmt->bind_param("i", $item_id);
                    $stmt->execute();
                    $stmt->bind_result($name, $price, $image_path);
                    $stmt->fetch();
                    $stmt->close();
                    ?>
                    <div class="cart-item">
                        <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($name); ?>">
                        <div>
                            <h3><?php echo htmlspecialchars($name); ?></h3>
                            <p>ราคา: <?php echo htmlspecialchars($price); ?> บาท</p>
                            <p>จำนวน:
                                <div class="quantity-controls">
                                    <button type="button" class="quantity-btn" onclick="updateQuantity('<?php echo htmlspecialchars($item_id); ?>', -1)">-</button>
                                    <input type="text" id="quantity_<?php echo htmlspecialchars($item_id); ?>" value="<?php echo htmlspecialchars($details['quantity']); ?>" readonly>
                                    <button type="button" class="quantity-btn" onclick="updateQuantity('<?php echo htmlspecialchars($item_id); ?>', 1)">+</button>
                                </div>
                            </p>
                        </div>
                        <div>
                            <form action="cart_buffet.php" method="POST">
                                <input type="hidden" name="remove" value="<?php echo htmlspecialchars($item_id); ?>">
                                <button type="submit" class="remove-btn">ลบ</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                <form id="orderForm" action="cart_buffet.php" method="POST">
                    <input type="hidden" name="action" value="complete_order">
                    <button type="button" class="checkout-btn" onclick="completeOrder()">สั่งอาหาร</button>
                </form>
            <?php endif; ?>
        </div>
        <a href="menu_order_buffet.php" class="checkout-btn">กลับไปที่เมนูอาหาร</a>
    </div>

    <!-- SweetAlert2 Integration -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function updateQuantity(itemId, change) {
            const quantityInput = document.getElementById('quantity_' + itemId);
            let currentQuantity = parseInt(quantityInput.value, 10);
            let newQuantity = currentQuantity + change;

            // ตรวจสอบให้แน่ใจว่าจำนวนสินค้าไม่ต่ำกว่า 1
            if (newQuantity < 1) {
                newQuantity = 1;
            }

            // อัปเดตข้อมูลที่แสดงผล
            quantityInput.value = newQuantity;

            // ส่งคำขอ AJAX เพื่ออัปเดตจำนวนในเซิร์ฟเวอร์
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "cart_buffet.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("update_quantity=1&item_id=" + encodeURIComponent(itemId) + "&quantity=" + encodeURIComponent(newQuantity));
        }

        function completeOrder() {
            Swal.fire({
                title: 'ยืนยันการสั่งซื้อ',
                text: "คุณแน่ใจว่าต้องการสั่งรายการอาหารเรียบร้อย?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ใช่, สั่งเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('orderForm').submit();
                }
            });
        }
    </script>
</body>

</html>
