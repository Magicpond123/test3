<?php
session_start();
include 'includes/db_connect.php';

// ตรวจสอบว่ามี order_buffet_id ที่เกี่ยวข้องกับโต๊ะนี้หรือไม่
if (!isset($_SESSION['order_buffet_id'])) {
    $table_id = $_SESSION['table_id'] ?? 1; // ใช้ table_id จาก session หรือค่าเริ่มต้น 1

    $sql_check_active_order = "SELECT order_buffet_id FROM active_orders WHERE table_id = ?";
    $stmt_check_active_order = $conn->prepare($sql_check_active_order);
    $stmt_check_active_order->bind_param("i", $table_id);
    $stmt_check_active_order->execute();
    $result_active_order = $stmt_check_active_order->get_result();

    if ($result_active_order->num_rows > 0) {
        $row = $result_active_order->fetch_assoc();
        $_SESSION['order_buffet_id'] = $row['order_buffet_id'];
    } else {
        die("ไม่พบออเดอร์ที่เปิดอยู่สำหรับโต๊ะนี้ กรุณาติดต่อพนักงาน");
    }
}

// จัดการตะกร้าสินค้า
if (isset($_POST['add_to_cart'])) {
    $item_id = $_POST['item_id'];
    $quantity = (int) $_POST['quantity'];
    $order_buffet_id = $_SESSION['order_buffet_id'];

    // เพิ่มรายการลงในตาราง order_buffet_details
    $sql_insert_item = "INSERT INTO order_buffet_details (order_buffet_id, item_id, quantity, status) 
                        VALUES (?, ?, ?, 1)"; // status 1 หมายถึง 'รอดำเนินการ'
    $stmt_insert_item = $conn->prepare($sql_insert_item);
    $stmt_insert_item->bind_param("iii", $order_buffet_id, $item_id, $quantity);
    
    if ($stmt_insert_item->execute()) {
        // อัพเดตตะกร้าใน session สำหรับการแสดงผล
        if (!isset($_SESSION['cart_buffet'])) {
            $_SESSION['cart_buffet'] = [];
        }
        if (isset($_SESSION['cart_buffet'][$item_id])) {
            $_SESSION['cart_buffet'][$item_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart_buffet'][$item_id] = ['quantity' => $quantity];
        }
        $cart_count = array_sum(array_column($_SESSION['cart_buffet'], 'quantity'));
        echo json_encode(['success' => true, 'cart_count' => $cart_count]);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการเพิ่มรายการอาหาร']);
    }
    exit;
}

// Query สำหรับดึงเมนู
$sql_food = "SELECT * FROM menuitems WHERE category_id = 1 AND order_type = 1";
$result_food = $conn->query($sql_food);

$sql_drink = "SELECT * FROM menuitems WHERE category_id = 2 AND order_type = 1";
$result_drink = $conn->query($sql_drink);

$sql_dessert = "SELECT * FROM menuitems WHERE category_id = 3 AND order_type = 1";
$result_dessert = $conn->query($sql_dessert);

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เมนูบุฟเฟ่ต์</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/menu_order.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function addToCart(itemId) {
            var quantity = $('#quantity-' + itemId).val();
            $.ajax({
                type: 'POST',
                url: 'menu_order_buffet_customer.php',
                data: {
                    item_id: itemId,
                    quantity: quantity,
                    add_to_cart: true
                },
                success: function(response) {
                    response = JSON.parse(response);

                    if (response.success) {
                        updateCartIcon(response.cart_count);
                        alert('เพิ่มสินค้าลงตะกร้าเรียบร้อยแล้ว!');
                    } else {
                        alert(response.message || 'เกิดข้อผิดพลาดในการเพิ่มสินค้าลงตะกร้า');
                    }
                },
                error: function() {
                    alert('เกิดข้อผิดพลาดในการเพิ่มสินค้าลงตะกร้า');
                }
            });
        }

        function updateCartIcon(cartCount) {
            $('.cart-icon .badge').text(cartCount);
        }

        function changeQuantity(amount, id) {
            var quantityInput = document.getElementById('quantity-' + id);
            var currentQuantity = parseInt(quantityInput.value, 10);
            var newQuantity = currentQuantity + amount;

            if (newQuantity >= 1) {
                quantityInput.value = newQuantity;
            }
        }

        function openTab(event, tabName) {
            var i;
            var x = document.getElementsByClassName("menu-items");
            var tabs = document.getElementsByClassName("tab");

            for (i = 0; i < x.length; i++) {
                x[i].style.display = "none";
            }

            for (i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove("active");
            }

            document.getElementById(tabName).style.display = "flex";
            event.currentTarget.classList.add("active");
        }
    </script>
    <style>
        .table-icon {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #ff4444;
            color: white;
            padding: 10px;
            border-radius: 50%;
            font-size: 1.5rem;
            z-index: 999;
        }
    </style>
</head>

<body>
    <div class="table-icon">
        <i class="fas fa-utensils"></i> <?php echo $_SESSION['table_id'] ?? 1; ?>
    </div>

    <header class="navbar">
        <img src="img/logo.jpg" alt="Logo">
    </header>

    <div class="tab-container">
        <div class="tab active" onclick="openTab(event, 'menu_food')">เมนูอาหาร</div>
        <div class="tab" onclick="openTab(event, 'menu_drink')">เครื่องดื่ม</div>
        <div class="tab" onclick="openTab(event, 'menu_dessert')">ของหวาน</div>
    </div>

    <main>
        <!-- เมนูอาหาร -->
        <section id="menu_food" class="menu-items" style="display: flex;">
            <?php while ($row = $result_food->fetch_assoc()) { ?>
                <div class="menu-item">
                    <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="menu-item-image">
                    <div class="menu-item-details">
                        <h2 class="menu-item-name"><?php echo htmlspecialchars($row['name']); ?></h2>
                        <p class="menu-item-price">ราคา: <?php echo htmlspecialchars($row['price']); ?> บาท</p>
                        <label for="quantity">จำนวน:</label>
                        <div class="quantity-controls">
                            <button type="button" class="quantity-btn" onclick="changeQuantity(-1, '<?php echo htmlspecialchars($row['item_id']); ?>')">-</button>
                            <input type="number" id="quantity-<?php echo htmlspecialchars($row['item_id']); ?>" name="quantity" value="1" min="1">
                            <button type="button" class="quantity-btn" onclick="changeQuantity(1, '<?php echo htmlspecialchars($row['item_id']); ?>')">+</button>
                        </div>
                        <button type="button" name="add_to_cart" class="add-to-cart-btn" onclick="addToCart('<?php echo htmlspecialchars($row['item_id']); ?>')">เพิ่มในตะกร้า</button>
                    </div>
                </div>
            <?php } ?>
        </section>

       <!-- เครื่องดื่ม -->
       <section id="menu_drink" class="menu-items" style="display: none;">
            <?php while ($row = $result_drink->fetch_assoc()) { ?>
                <div class="menu-item">
                    <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="menu-item-image">
                    <div class="menu-item-details">
                        <h2 class="menu-item-name"><?php echo htmlspecialchars($row['name']); ?></h2>
                        <p class="menu-item-price">ราคา: <?php echo htmlspecialchars($row['price']); ?> บาท</p>
                        <label for="quantity">จำนวน:</label>
                        <div class="quantity-controls">
                            <button type="button" class="quantity-btn" onclick="changeQuantity(-1, '<?php echo htmlspecialchars($row['item_id']); ?>')">-</button>
                            <input type="number" id="quantity-<?php echo htmlspecialchars($row['item_id']); ?>" name="quantity" value="1" min="1">
                            <button type="button" class="quantity-btn" onclick="changeQuantity(1, '<?php echo htmlspecialchars($row['item_id']); ?>')">+</button>
                        </div>
                        <button type="button" name="add_to_cart" class="add-to-cart-btn" onclick="addToCart('<?php echo htmlspecialchars($row['item_id']); ?>')">เพิ่มในตะกร้า</button>
                    </div>
                </div>
            <?php } ?>
        </section>

        <!-- ของหวาน -->
        <section id="menu_dessert" class="menu-items" style="display: none;">
            <?php while ($row = $result_dessert->fetch_assoc()) { ?>
                <div class="menu-item">
                    <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="menu-item-image">
                    <div class="menu-item-details">
                        <h2 class="menu-item-name"><?php echo htmlspecialchars($row['name']); ?></h2>
                        <p class="menu-item-price">ราคา: <?php echo htmlspecialchars($row['price']); ?> บาท</p>
                        <label for="quantity">จำนวน:</label>
                        <div class="quantity-controls">
                            <button type="button" class="quantity-btn" onclick="changeQuantity(-1, '<?php echo htmlspecialchars($row['item_id']); ?>')">-</button>
                            <input type="number" id="quantity-<?php echo htmlspecialchars($row['item_id']); ?>" name="quantity" value="1" min="1">
                            <button type="button" class="quantity-btn" onclick="changeQuantity(1, '<?php echo htmlspecialchars($row['item_id']); ?>')">+</button>
                        </div>
                        <button type="button" name="add_to_cart" class="add-to-cart-btn" onclick="addToCart('<?php echo htmlspecialchars($row['item_id']); ?>')">เพิ่มในตะกร้า</button>
                    </div>
                </div>
            <?php } ?>
        </section>
        
    </main>

    <a href="cart_buffet.php" class="cart-icon">
        <i class="fas fa-shopping-cart"></i>
        <div class="badge"><?php echo isset($_SESSION['cart_buffet']) ? array_sum(array_column($_SESSION['cart_buffet'], 'quantity')) : 0; ?></div>
    </a>

</body>

</html>