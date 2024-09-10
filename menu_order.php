<?php
session_start();
include 'includes/db_connect.php';

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// เพิ่มสินค้าลงในตะกร้า
if (isset($_POST['add_to_cart'])) {
    $item_id = $_POST['item_id'];
    $quantity = intval($_POST['quantity']);

    // ตรวจสอบว่ามีตะกร้าใน session หรือไม่
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // ตรวจสอบว่าสินค้าอยู่ในตะกร้าแล้วหรือยัง ถ้ามีให้เพิ่มจำนวน
    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$item_id] = ['quantity' => $quantity];
    }

    // รีเฟรชหน้าเพื่อนับจำนวนสินค้าใหม่ในไอคอนตะกร้า
    header("Location: menu_order.php");
    exit();
}

// ดึงข้อมูลเมนูตามประเภท
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 1;
$sql = "SELECT * FROM menuitems WHERE category_id = $category_id";
$result = $conn->query($sql);

if ($result === false) {
    die("Error: " . $conn->error);
}

// ดึงข้อมูลเครื่องดื่ม (สมมติ category_id ของเครื่องดื่มเป็น 2)
$sql_drink = "SELECT * FROM menuitems WHERE category_id = 2";
$result_drink = $conn->query($sql_drink);

if ($result_drink === false) {
    die("Error: " . $conn->error);
}

// ดึงข้อมูลของหวาน (สมมติ category_id ของของหวานเป็น 3)
$sql_dessert = "SELECT * FROM menuitems WHERE category_id = 3";
$result_dessert = $conn->query($sql_dessert);

if ($result_dessert === false) {
    die("Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เมนูอาหาร</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #fff5f5;
            color: #4b4b4b;
            font-family: Arial, sans-serif;
        }

        .navbar {
            background-color: #ff4c4c;
            padding: 10px;
            text-align: center;
        }

        .navbar img {
            max-width: 150px;
            margin: 0 auto;
        }

        .tab-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            border-bottom: 2px solid #ccc;
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            background-color: #e0e0e0;
            margin-right: 5px;
            border-radius: 10px 10px 0 0;
            color: #333;
        }

        .tab.active {
            background-color: #ff4c4c;
            color: #fff;
        }

        .menu-items {
            display: none;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
        }

        .menu-item {
            background-color: #f4f4f4;
            border: 1px solid #ccc;
            border-radius: 10px;
            margin: 10px;
            padding: 10px;
            width: calc(100% - 40px);
            max-width: 400px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .menu-item img {
            width: 100%;
            border-radius: 10px;
        }

        .menu-item-details {
            padding: 10px 0;
        }

        .menu-item-name {
            font-size: 1.5em;
            margin: 0;
        }

        .menu-item-price {
            color: #e91e63;
        }

        .add-to-cart-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 10px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 5px;
        }

        .quantity-btn {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 1.2em;
            width: 40px;
            height: 40px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s, transform 0.2s;
        }

        .quantity-btn:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        .quantity-btn:active {
            background-color: #003d7a;
            transform: scale(0.95);
        }

        input[type='number'] {
            width: 60px;
            text-align: center;
            margin: 0 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 5px;
            font-size: 1em;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .quantity-controls label {
            margin-right: 10px;
            font-size: 1em;
        }

        .cart-icon {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #f4f4f4;
            color: #4b4b4b;
            padding: 15px;
            border-radius: 50%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            font-size: 24px;
            z-index: 1000;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s, box-shadow 0.3s, transform 0.3s;
        }

        .cart-icon:hover {
            background-color: #e0e0e0;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
            transform: scale(1.1);
        }

        .cart-icon .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #ff4c4c;
            color: #ffffff;
            border-radius: 50%;
            padding: 5px 10px;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        @media (min-width: 600px) {
            .menu-item {
                width: calc(50% - 40px);
            }
        }

        @media (min-width: 900px) {
            .menu-item {
                width: calc(33.3333% - 40px);
            }
        }
    </style>
    <script>
        function changeQuantity(amount, id) {
            var quantityInput = document.getElementById('quantity-' + id);
            var currentQuantity = parseInt(quantityInput.value, 10);
            var newQuantity = currentQuantity + amount;

            if (newQuantity >= 1) {
                quantityInput.value = newQuantity;
            }
        }

        function openTab(tabName) {
            var i;
            var x = document.getElementsByClassName("menu-items");
            var tabs = document.getElementsByClassName("tab");
            
            // ซ่อนเมนูทั้งหมด
            for (i = 0; i < x.length; i++) {
                x[i].style.display = "none";
            }

            // นำ `active` class ออกจาก tab ทั้งหมด
            for (i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove("active");
            }

            // แสดงเมนูที่เลือกและเพิ่ม `active` class ไปยัง tab ที่เลือก
            document.getElementById(tabName).style.display = "flex";
            event.currentTarget.classList.add("active");
        }
    </script>
</head>

<body>
    <header class="navbar">
        <img src="img\logo.jpg" alt="Logo">
    </header>

    <div class="tab-container">
        <div class="tab active" onclick="openTab('menu_food')">สั่งเมนูอาหาร</div>
        <div class="tab" onclick="openTab('menu_drink')">สั่งเครื่องดื่ม</div>
        <div class="tab" onclick="openTab('menu_dessert')">ของหวาน</div>
    </div>

    <main>
    <section id="menu_food" class="menu-items" style="display: flex;">
        <?php
        while ($row = $result->fetch_assoc()) {
            $imagePath = isset($row['image_path']) ? $row['image_path'] : 'path/to/default/image.jpg';
            $description = isset($row['description']) ? $row['description'] : 'ไม่มีรายละเอียด';

            echo "<div class='menu-item'>";
            echo "<img src='" . htmlspecialchars($imagePath) . "' alt='" . htmlspecialchars($row['name']) . "' class='menu-item-image'>";
            echo "<div class='menu-item-details'>";
            echo "<h2 class='menu-item-name'>" . htmlspecialchars($row['name']) . "</h2>";
            echo "<p class='menu-item-price'>ราคา: " . htmlspecialchars($row['price']) . " บาท</p>";
            echo "<p class='menu-item-description'>รายละเอียด: " . htmlspecialchars($description) . "</p>";
            echo "<form action='menu_order.php' method='POST' class='add-to-cart-form'>";
            echo "<input type='hidden' name='item_id' value='" . htmlspecialchars($row['item_id']) . "'>";
            echo "<label for='quantity'>จำนวน:</label>";
            echo "<div class='quantity-controls'>";
            echo "<button type='button' class='quantity-btn' onclick='changeQuantity(-1, \"" . htmlspecialchars($row['item_id']) . "\")'>-</button>";
            echo "<input type='number' id='quantity-" . htmlspecialchars($row['item_id']) . "' name='quantity' value='1' min='1'>";
            echo "<button type='button' class='quantity-btn' onclick='changeQuantity(1, \"" . htmlspecialchars($row['item_id']) . "\")'>+</button>";
            echo "</div>";
            echo "<button type='submit' name='add_to_cart' class='add-to-cart-btn'>เพิ่มในตะกร้า</button>";
            echo "</form>";
            echo "</div>";
            echo "</div>";
        }
        ?>
    </section>

    <section id="menu_drink" class="menu-items">
    <?php
    while ($row = $result_drink->fetch_assoc()) {
        echo "<div class='menu-item'>";
        echo "<img src='" . htmlspecialchars($row['image_path']) . "' alt='" . htmlspecialchars($row['name']) . "' class='menu-item-image'>";
        echo "<div class='menu-item-details'>";
        echo "<h2 class='menu-item-name'>" . htmlspecialchars($row['name']) . "</h2>";
        echo "<p class='menu-item-price'>ราคา: " . htmlspecialchars($row['price']) . " บาท</p>";
        echo "<form action='menu_order.php' method='POST' class='add-to-cart-form'>";
        echo "<input type='hidden' name='item_id' value='" . htmlspecialchars($row['item_id']) . "'>";
        echo "<label for='quantity'>จำนวน:</label>";
        echo "<div class='quantity-controls'>";
        echo "<button type='button' class='quantity-btn' onclick='changeQuantity(-1, \"" . htmlspecialchars($row['item_id']) . "\")'>-</button>";
        echo "<input type='number' id='quantity-" . htmlspecialchars($row['item_id']) . "' name='quantity' value='1' min='1'>";
        echo "<button type='button' class='quantity-btn' onclick='changeQuantity(1, \"" . htmlspecialchars($row['item_id']) . "\")'>+</button>";
        echo "</div>";
        echo "<button type='submit' name='add_to_cart' class='add-to-cart-btn'>เพิ่มในตะกร้า</button>";
        echo "</form>";
        echo "</div>";
        echo "</div>";
    }
    ?>
</section>

<section id="menu_dessert" class="menu-items">
    <?php
    while ($row = $result_dessert->fetch_assoc()) {
        echo "<div class='menu-item'>";
        echo "<img src='" . htmlspecialchars($row['image_path']) . "' alt='" . htmlspecialchars($row['name']) . "' class='menu-item-image'>";
        echo "<div class='menu-item-details'>";
        echo "<h2 class='menu-item-name'>" . htmlspecialchars($row['name']) . "</h2>";
        echo "<p class='menu-item-price'>ราคา: " . htmlspecialchars($row['price']) . " บาท</p>";
        echo "<form action='menu_order.php' method='POST' class='add-to-cart-form'>";
        echo "<input type='hidden' name='item_id' value='" . htmlspecialchars($row['item_id']) . "'>";
        echo "<label for='quantity'>จำนวน:</label>";
        echo "<div class='quantity-controls'>";
        echo "<button type='button' class='quantity-btn' onclick='changeQuantity(-1, \"" . htmlspecialchars($row['item_id']) . "\")'>-</button>";
        echo "<input type='number' id='quantity-" . htmlspecialchars($row['item_id']) . "' name='quantity' value='1' min='1'>";
        echo "<button type='button' class='quantity-btn' onclick='changeQuantity(1, \"" . htmlspecialchars($row['item_id']) . "\")'>+</button>";
        echo "</div>";
        echo "<button type='submit' name='add_to_cart' class='add-to-cart-btn'>เพิ่มในตะกร้า</button>";
        echo "</form>";
        echo "</div>";
        echo "</div>";
    }
    ?>
</section>

    </main>

    <a href="cart.php" class="cart-icon">
        <i class="fas fa-shopping-cart"></i>
        <div class="badge"><?php echo isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0; ?></div>
    </a>
</body>
</html>
