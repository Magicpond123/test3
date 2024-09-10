<?php
session_start();
include 'includes/db_connect.php';

date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['cart_pickup'])) {
    $_SESSION['cart_pickup'] = [];
}

// Remove item from pickup cart
if (isset($_POST['remove'])) {
    $itemToRemove = htmlspecialchars($_POST['remove']);
    if (isset($_SESSION['cart_pickup'][$itemToRemove])) {
        unset($_SESSION['cart_pickup'][$itemToRemove]);
    }
}

// Update item quantity in pickup cart
if (isset($_POST['update_quantity'])) {
    $item_id = htmlspecialchars($_POST['item_id']);
    $new_quantity = (int) $_POST['quantity'];
    if (isset($_SESSION['cart_pickup'][$item_id])) {
        if ($new_quantity <= 0) {
            unset($_SESSION['cart_pickup'][$item_id]);
        } else {
            $_SESSION['cart_pickup'][$item_id]['quantity'] = $new_quantity;
        }
    }
}

// Complete pickup order
if (isset($_POST['action']) && $_POST['action'] === 'complete_order') {
    $orderSuccess = true;
    $conn->begin_transaction();
    try {
        $emp_id = $_SESSION['emp_id'] ?? 2; // ใช้ emp_id จาก session หรือค่าเริ่มต้น
        $order_date = date('Y-m-d H:i:s');

        // Insert into order_pickup table
        $stmt = $conn->prepare("INSERT INTO order_pickup (emp_id, order_date) VALUES (?, ?)");
        $stmt->bind_param("is", $emp_id, $order_date);
        $stmt->execute();
        $order_pickup_id = $stmt->insert_id;
        $stmt->close();

        // Insert into order_pickup_details table
        foreach ($_SESSION['cart_pickup'] as $item_id => $details) {
            $stmt = $conn->prepare("INSERT INTO order_pickup_details (order_pickup_id, item_id, quantity) VALUES (?, ?, ?)");
            $quantity = $details['quantity'];
            $stmt->bind_param("iii", $order_pickup_id, $item_id, $quantity);
            $stmt->execute();
            $stmt->close();
        }

        $_SESSION['cart_pickup'] = []; // Clear the cart
        $conn->commit();
        header("Location: success_page_pickup.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตะกร้าสินค้า (สั่งกลับบ้าน)</title>
    <link rel="stylesheet" href="css/cart_pickup.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="page-container">
        <div class="header-container">
            <h1>ตะกร้าสินค้า (สั่งกลับบ้าน)</h1>
        </div>
        <div class="cart-container">
            <?php if (empty($_SESSION['cart_pickup'])) : ?>
                <p>ไม่มีรายการอาหารในตะกร้า</p>
            <?php else : ?>
                <?php foreach ($_SESSION['cart_pickup'] as $item_id => $details) : ?>
                    <?php
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
                        <div style="text-align: center;">
                            <form action="cart_pickup.php" method="POST">
                                <input type="hidden" name="remove" value="<?php echo htmlspecialchars($item_id); ?>">
                                <button type="submit" class="remove-btn">ลบ</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                <form id="orderForm" action="cart_pickup.php" method="POST">
                    <input type="hidden" name="action" value="complete_order">
                    <button type="button" class="checkout-btn" onclick="completeOrder()">สั่งอาหาร</button>
                </form>
            <?php endif; ?>
        </div>
        <a href="menu_order_pickup.php" class="checkout-btn">กลับไปที่เมนูอาหาร</a>
    </div>

    <script>
        function updateQuantity(itemId, change) {
            const quantityInput = document.getElementById('quantity_' + itemId);
            let currentQuantity = parseInt(quantityInput.value, 10);
            let newQuantity = currentQuantity + change;

            if (newQuantity >= 1) {
                quantityInput.value = newQuantity;

                const xhr = new XMLHttpRequest();
                xhr.open("POST", "cart_pickup.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.send("update_quantity=1&item_id=" + encodeURIComponent(itemId) + "&quantity=" + encodeURIComponent(newQuantity));
            }
        }

        function completeOrder() {
            Swal.fire({
                title: 'ยืนยันการสั่งซื้อ',
                text: "คุณแน่ใจว่าต้องการสั่งรายการอาหารนี้?",
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