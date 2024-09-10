<?php
session_start();
include 'includes/db_connect.php';

// ตรวจสอบว่าเข้าสู่ระบบแล้วหรือยัง
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// จัดการตะกร้าสินค้า
if (isset($_POST['add_to_cart'])) {
    $item_id = $_POST['item_id'];
    $quantity = (int) $_POST['quantity'];

    if (!isset($_SESSION['cart_pickup'])) {
        $_SESSION['cart_pickup'] = [];
    }

    if (isset($_SESSION['cart_pickup'][$item_id])) {
        $_SESSION['cart_pickup'][$item_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart_pickup'][$item_id] = ['quantity' => $quantity];
    }

    // คำนวณจำนวนสินค้าในตะกร้า
    $cart_count = array_sum(array_column($_SESSION['cart_pickup'], 'quantity'));

    // ส่งผลลัพธ์กลับเป็น JSON
    echo json_encode(['cart_count' => $cart_count]);
    exit;
}

// Query สำหรับดึงเมนู
$sql_food = "SELECT * FROM menuitems WHERE category_id = 1 AND order_type = 2";
$result_food = $conn->query($sql_food);

$sql_drink = "SELECT * FROM menuitems WHERE category_id = 2 AND order_type = 2";
$result_drink = $conn->query($sql_drink);

$sql_dessert = "SELECT * FROM menuitems WHERE category_id = 3 AND order_type = 2";
$result_dessert = $conn->query($sql_dessert);

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เมนูสั่งกลับบ้าน</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/menu_order.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function addToCart(itemId) {
            var quantity = $('#quantity-' + itemId).val();
            $.ajax({
                type: 'POST',
                url: 'menu_order_pickup.php',
                data: {
                    item_id: itemId,
                    quantity: quantity,
                    add_to_cart: true
                },
                success: function(response) {
                    response = JSON.parse(response);

                    if (response.cart_count !== undefined) {
                        updateCartIcon(response.cart_count);
                        alert('เพิ่มสินค้าลงตะกร้าเรียบร้อยแล้ว!');
                    } else {
                        alert('เกิดข้อผิดพลาดในการอัพเดตไอคอนตะกร้า');
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
</head>

<body>
    <header class="navbar">
        <img src="img/logo.jpg" alt="Logo">
        
    </header>
    <h1 style="text-align: center;">เมนูสั่งกลับบ้าน</h1>

    <div class="tab-container">
        <div class="tab active" onclick="openTab(event, 'menu_food')">เมนูอาหาร</div>
        <div class="tab" onclick="openTab(event, 'menu_drink')">เครื่องดื่ม</div>
        <div class="tab" onclick="openTab(event, 'menu_dessert')">ของหวาน</div>
    </div>

    <main>
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

    <a href="cart_pickup.php" class="cart-icon">
        <i class="fas fa-shopping-cart"></i>
        <div class="badge"><?php echo isset($_SESSION['cart_pickup']) ? array_sum(array_column($_SESSION['cart_pickup'], 'quantity')) : 0; ?></div>
    </a>
</body>

</html>