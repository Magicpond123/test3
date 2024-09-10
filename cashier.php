<?php
session_start();

// ตรวจสอบว่าเข้าสู่ระบบหรือไม่
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

// ตรวจสอบสิทธิ์การเข้าถึงตาม Role
if ($_SESSION['role'] != 2) {
    header('Location: unauthorized.php'); // หรือแสดงข้อความว่าไม่อนุญาต
    exit();
}

include 'includes/db_connect.php';

// Fetch order details from the buffet and pickup orders
$sql_orders = "SELECT o.order_buffet_id, o.table_id, o.adult, o.child, o.order_date, t.table_number, t.table_status, o.payment_status
               FROM order_buffet o
               JOIN tables t ON o.table_id = t.table_id
               WHERE o.payment_status = 0";
$result_orders = $conn->query($sql_orders);

// Fetch pickup orders
$sql_pickup_orders = "SELECT o.order_pickup_id, o.emp_id, o.order_date
                      FROM order_pickup o";
$result_pickup_orders = $conn->query($sql_pickup_orders);

// Check for query execution errors
if (!$result_orders || !$result_pickup_orders) {
    die("Error executing query: " . $conn->error);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_customers'])) {
        // Get data from form
        $order_id = $_POST['order_id'];
        $ad = $_POST['ad'];
        $hc = $_POST['hc'];

        // Update database
        $sql_update = "UPDATE order_buffet SET adult = ?, child = ? WHERE order_buffet_id = ?";
        $stmt = $conn->prepare($sql_update);
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }

        $stmt->bind_param("iii", $ad, $hc, $order_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Update successful!";
        } else {
            $_SESSION['error_message'] = "Update failed: " . $conn->error;
        }
        $stmt->close();

        // Redirect to avoid form resubmission
        header("Location: manage_orders.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการออเดอร์ภายในร้าน</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/cashier.css">
    <style>
        .table-card {
            border: 2px solid green;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            background-color: #e6ffe6;
        }

        .table-card.occupied {
            border-color: green;
            background-color: #e6ffe6;
        }

        .table-card:not(.occupied) {
            border-color: #ccc;
        }
    </style>
    <script>
        function goToOrderDetails(orderId, type) {
            if (type === 'buffet') {
                window.location.href = 'cashier_details.php?order_id=' + orderId;
            } else if (type === 'pickup') {
                window.location.href = 'cashier_details_pickup.php?order_pickup_id=' + orderId;
            }
        }
    </script>
</head>

<body>
    <div class="container mt-4">
        <h1 class="text-center">จัดการออเดอร์ภายในร้าน</h1>

        <!-- Display status messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message'];
                unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error_message'];
                unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <div class="row mt-4">
            <?php while ($row = $result_orders->fetch_assoc()) {
                if ($row['payment_status'] == 0) { ?>
                    <div class="col-md-3 mb-4">
                        <div class="table-card occupied" onclick="goToOrderDetails('<?php echo htmlspecialchars($row['order_buffet_id']); ?>', 'buffet')">
                            <h3>โต๊ะ <?php echo htmlspecialchars($row['table_number']); ?></h3>
                            <p>จำนวนผู้ใหญ่: <?php echo htmlspecialchars($row['adult']); ?></p>
                            <p>จำนวนเด็ก: <?php echo htmlspecialchars($row['child']); ?></p>
                            <p>สถานะ: รอชำระเงิน</p>
                        </div>
                    </div>
            <?php }
            } ?>
        </div>

        <div class="col-12">
            <h3>ออเดอร์สั่งกลับบ้าน</h3>
            <div class="row">
                <?php while ($row = $result_pickup_orders->fetch_assoc()) { ?>
                    <div class="col-md-3 mb-4">
                        <div class="table-card" onclick="goToOrderDetails('<?php echo htmlspecialchars($row['order_pickup_id']); ?>', 'pickup')">
                            <h3>รหัสออเดอร์: <?php echo htmlspecialchars($row['order_pickup_id']); ?></h3>
                            <p>พนักงาน ID: <?php echo htmlspecialchars($row['emp_id']); ?></p>
                            <p>วันที่: <?php echo htmlspecialchars($row['order_date']); ?></p>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>