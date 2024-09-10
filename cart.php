<?php
session_start();
include 'includes/db_connect.php';

date_default_timezone_set('Asia/Bangkok');

// Initialize cart if not already set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add item to cart
if (isset($_POST['item_id']) && isset($_POST['quantity'])) {
    $item_id = htmlspecialchars($_POST['item_id']);
    $quantity = (int)$_POST['quantity'];

    // Check if item already in cart
    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$item_id] = [
            'quantity' => $quantity,
            'price' => htmlspecialchars($_POST['price']),
            'image' => htmlspecialchars($_POST['image'])
        ];
    }
    header("Location: cart.php");
    exit();
}

// Remove item from cart
if (isset($_POST['remove'])) {
    $itemToRemove = htmlspecialchars($_POST['remove']);
    if (isset($_SESSION['cart'][$itemToRemove])) {
        unset($_SESSION['cart'][$itemToRemove]);
    }
}

// Complete order
if (isset($_POST['action']) && $_POST['action'] === 'complete_order') {
    $orderSuccess = true;
    $conn->begin_transaction(); // Start transaction
    try {
        $table_id = 2;
        $emp_id = 2;
        $order_date = date('Y-m-d H:i:s');
        $order_status = 2; // Default to 'not paid'

        // Insert into orders table
        $stmt = $conn->prepare("INSERT INTO orders (table_id, emp_id, order_date, order_status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $table_id, $emp_id, $order_date, $order_status);
        if (!$stmt->execute()) {
            throw new Exception("Order insertion failed: " . $stmt->error);
        }
        $order_id = $stmt->insert_id;
        $stmt->close();

        foreach ($_SESSION['cart'] as $item_id => $details) {
            $stmt = $conn->prepare("INSERT INTO orderdetails (order_id, item_id, quantity) VALUES (?, ?, ?)");
            $quantity = $details['quantity'];
            $stmt->bind_param("iii", $order_id, $item_id, $quantity);
            if (!$stmt->execute()) {
                throw new Exception("Order details insertion failed: " . $stmt->error);
            }
            $stmt->close();
        }

        // Clear the cart
        $_SESSION['cart'] = [];
        $conn->commit(); // Commit transaction
        header("Location: success_page.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback(); // Rollback transaction
        echo "Error: " . $e->getMessage(); // Display the error message
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตะกร้าสินค้า</title>
    <link rel="stylesheet" href="css/styles1.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            height: 100vh;
        }

        .page-container {
            width: 100%;
            max-width: 800px;
            padding: 20px;
            background-color: #F5EDED;
            border: 2px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            margin-top: 20px;
        }

        .header-container {
            text-align: center;
        }

        h1 {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            border-radius: 10px;
            border: 3px solid #388E3C;
            display: inline-block;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .cart-container {
            margin: 20px 0;
            padding: 20px;
            background-color: #fff;
            border: 2px solid #ddd;
            border-radius: 10px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 20px;
        }

        .cart-item div {
            text-align: left;
        }

        .cart-item p {
            margin: 5px 0;
        }

        .checkout-btn,
        .remove-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 18px;
        }

        .remove-btn {
            background-color: #f44336;
        }

        .remove-btn:hover {
            background-color: #e53935;
        }

        .checkout-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <div class="page-container">
        <div class="header-container">
            <h1>ตะกร้าสินค้า</h1>
        </div>
        <div class="cart-container">
            <?php if (empty($_SESSION['cart'])) : ?>
                <p>ไม่มีรายการอาหารในตะกร้า</p>
            <?php else : ?>
                <?php foreach ($_SESSION['cart'] as $item_id => $details) : ?>
                    <?php
                    // Fetch item details from the database
                    $stmt = $conn->prepare("SELECT name, price, image_path FROM menuitems WHERE item_id = ?");
                    $stmt->bind_param("i", $item_id);
                    $stmt->execute();
                    $stmt->bind_result($name, $price, $image_path);
                    $stmt->fetch();
                    $stmt->close();

                    // Use image_path from database if available, otherwise use the session value
                    $image = $image_path ?: $details['image'];
                    ?>
                    <div class="cart-item">
                        <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($name); ?>">
                        <div>
                            <h3><?php echo htmlspecialchars($name); ?></h3>
                            <p>ราคา: <?php echo htmlspecialchars($price); ?> บาท</p>
                            <p>จำนวน: <?php echo htmlspecialchars($details['quantity']); ?></p>
                        </div>
                        <div style="text-align: center;">
                            <form action="cart.php" method="POST">
                                <input type="hidden" name="remove" value="<?php echo htmlspecialchars($item_id); ?>">
                                <button type="submit" class="remove-btn">ลบ</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                <form action="cart.php" method="POST">
                    <input type="hidden" name="action" value="complete_order">
                    <button type="submit" class="checkout-btn" onclick="completeOrder()">สั่งอาหาร</button>
                </form>
            <?php endif; ?>
        </div>
        <a href="menu_order.php" class="checkout-btn">กลับไปที่เมนูอาหาร</a>
    </div>

    <!-- SweetAlert2 Integration -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
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
                    document.querySelector('form[action="cart.php"]').submit();
                }
            });
        }
    </script>
</body>

</html>
