<?php
session_start();
include 'includes/db_connect.php';

// ตรวจสอบว่าเข้าสู่ระบบแล้วหรือยัง
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// ดึงข้อมูลพนักงานจากฐานข้อมูล
$username = $_SESSION['username'];
$sql = "SELECT * FROM employees WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $employee = $result->fetch_assoc();

    // ตรวจสอบสิทธิ์ (role) ของผู้ใช้ว่าตรงกับ role 3 (พนักงานต้อนรับ) หรือไม่
    if ($employee['role'] != 3) {
        echo "คุณไม่มีสิทธิ์เข้าถึงหน้านี้";
        exit();
    }
} else {
    echo "ไม่พบข้อมูลพนักงาน";
    exit();
}

// ตรวจสอบว่าผู้ใช้เลือกโต๊ะแล้วหรือยัง
if (isset($_POST['table_id']) && !empty($_POST['table_id'])) {
    $_SESSION['table_id'] = $_POST['table_id'];
    header("Location: menu_order_buffet.php"); // ไปยังหน้าสั่งอาหาร
    exit();
}

// Query ดึงข้อมูลโต๊ะ
$sql_tables = "SELECT table_id, table_number FROM tables WHERE table_status = 1";
$result_tables = $conn->query($sql_tables);

// จัดการตะกร้าสินค้า
if (isset($_POST['add_to_cart'])) {
    $item_id = $_POST['item_id'];
    $quantity = (int) $_POST['quantity'];

    if (!isset($_SESSION['cart_buffet'])) {
        $_SESSION['cart_buffet'] = [];
    }

    if (isset($_SESSION['cart_buffet'][$item_id])) {
        $_SESSION['cart_buffet'][$item_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart_buffet'][$item_id] = ['quantity' => $quantity];
    }

    // คำนวณจำนวนสินค้าในตะกร้า
    $cart_count = array_sum(array_column($_SESSION['cart_buffet'], 'quantity'));

    // ส่งผลลัพธ์กลับเป็น JSON
    echo json_encode(['cart_count' => $cart_count]);
    exit;
}

// Query สำหรับดึงเมนู
$sql_food = "SELECT * FROM menuitems WHERE category_id = 1 AND order_type = 1";
$result_food = $conn->query($sql_food);

$sql_drink = "SELECT * FROM menuitems WHERE category_id = 2 AND order_type = 1";
$result_drink = $conn->query($sql_drink);

$sql_dessert = "SELECT * FROM menuitems WHERE category_id = 3 AND order_type = 1";
$result_dessert = $conn->query($sql_dessert);

// Query ดึงข้อมูลโต๊ะ
$sql_tables = "SELECT table_id, table_number FROM tables WHERE table_status = 1";
$result_tables = $conn->query($sql_tables);

if (isset($_POST['update_customer_count'])) {
    $_SESSION['adults'] = (int) $_POST['adults'];
    $_SESSION['children'] = (int) $_POST['children'];

    $price_adults = $_SESSION['adults'] * 149;
    $price_children = $_SESSION['children'] * 99;

    $_SESSION['price_adults'] = $price_adults;
    $_SESSION['price_children'] = $price_children;

    header("Location: menu_order_buffet.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เมนูบุฟเฟ่ต์สำหรับพนักงานต้อนรับ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/menu_order.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function addToCart(itemId) {
            var quantity = $('#quantity-' + itemId).val();
            $.ajax({
                type: 'POST',
                url: 'menu_order_buffet.php',
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
                x[i].style.display = "none"; // ซ่อนทุกแท็บ
            }

            for (i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove("active"); // ลบคลาส active จากทุกแท็บ
            }

            document.getElementById(tabName).style.display = "flex"; // แสดงแท็บที่ถูกเลือก
            event.currentTarget.classList.add("active"); // เพิ่มคลาส active ให้แท็บที่ถูกคลิก
        }
    </script>
</head>

<body>
    <header class="navbar">
        <img src="img/logo.jpg" alt="Logo">
        <div class="table-dropdown">
            <form action="menu_order_buffet.php" method="POST">
                <label for="table_id">เลือกโต๊ะ:</label>
                <select name="table_id" id="table_id" onchange="this.form.submit()">
                    <?php
                    if ($result_tables->num_rows > 0) {
                        while ($row = $result_tables->fetch_assoc()) {
                            $selected = (isset($_SESSION['table_id']) && $_SESSION['table_id'] == $row['table_id']) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($row['table_id']) . "' $selected>โต๊ะ " . htmlspecialchars($row['table_number']) . "</option>";
                        }
                    } else {
                        echo "<option value=''>ไม่มีโต๊ะว่าง</option>";
                    }
                    ?>
                </select>
            </form>
        </div>
    </header>

    <div class="tab-container">
        <div class="tab active" onclick="openTab(event, 'menu_food')">เมนูอาหาร</div>
        <div class="tab" onclick="openTab(event, 'menu_drink')">เครื่องดื่ม</div>
        <div class="tab" onclick="openTab(event, 'menu_dessert')">ของหวาน</div>
    </div>


    <main>
        <div id=pd>
            <form id="customer-count-form" action="menu_order_buffet.php" method="POST">
                <label for="adults">จำนวนผู้ใหญ่:</label>
                <input type="number" id="adults" name="adults" value="<?php echo isset($_SESSION['adults']) ? $_SESSION['adults'] : 1; ?>" min="1">
                <label for="children">จำนวนเด็ก:</label>
                <input type="number" id="children" name="children" value="<?php echo isset($_SESSION['children']) ? $_SESSION['children'] : 0; ?>" min="0">
                <div id="btn">
                    <button type="submit" name="update_customer_count">อัปเดตจำนวนลูกค้า</button>
                </div>
            </form>
        </div>
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