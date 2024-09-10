<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_GET['order_buffet_id'])) {
    die('Error: order_buffet_id is missing from the URL.');
}

$order_buffet_id = $_GET['order_buffet_id'];

// Prepare SQL statement
$sql = "SELECT d.item_id, d.quantity, m.name, m.price, d.status
        FROM order_buffet_details d
        JOIN menuitems m ON d.item_id = m.item_id
        WHERE d.order_buffet_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}

$stmt->bind_param("i", $order_buffet_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the result is valid
if ($result === false) {
    die('Query failed: ' . htmlspecialchars($stmt->error));
}

// Status options
$status_options = [
    0 => 'ไม่ระบุสถานะ',
    1 => 'รอดำเนินการ',
    2 => 'กำลังดำเนินการ',
    3 => 'จัดส่งแล้ว',
    4 => 'ยกเลิก',
];

// Handle status update
if (isset($_POST['update_status'])) {
    $item_id = $_POST['item_id'];
    $new_status = $_POST['status'];
    $update_sql = "UPDATE order_buffet_details SET status = ? WHERE order_buffet_id = ? AND item_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    if ($update_stmt === false) {
        die('Update prepare failed: ' . htmlspecialchars($conn->error));
    }

    $update_stmt->bind_param("sii", $new_status, $order_buffet_id, $item_id);
    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = "สถานะออเดอร์ถูกอัปเดตแล้ว";
    } else {
        $_SESSION['error_message'] = "เกิดข้อผิดพลาดในการอัปเดตสถานะ";
    }
    $update_stmt->close();
    
    // Reload the page to see the updated status
    header("Location: order_details.php?order_buffet_id=" . $order_buffet_id);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/order_details.css">
</head>
<body>
    <div class="container mt-5">
        <h2>รายละเอียดออเดอร์ #<?php echo $order_buffet_id; ?></h2>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ชื่อเมนู</th>
                    <th>จำนวน</th>
                    <th>ราคา</th>
                    <th>รวม</th>
                    <th>สถานะ</th>
                    <th>อัปเดตสถานะ</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($row['price']); ?></td>
                        <td><?php echo number_format($row['price'] * $row['quantity'], 2); ?></td>
                        <td><?php echo htmlspecialchars($status_options[$row['status']]); ?></td>
                        <td>
                            <form action="order_details.php?order_buffet_id=<?php echo $order_buffet_id; ?>" method="POST">
                                <input type="hidden" name="item_id" value="<?php echo $row['item_id']; ?>">
                                <select name="status" class="form-select">
                                    <?php foreach ($status_options as $value => $label) { ?>
                                        <option value="<?php echo $value; ?>" <?php echo $row['status'] == $value ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-primary mt-2">อัปเดต</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <a href="manage_orders.php" class="btn btn-primary">กลับไปหน้าจัดการออเดอร์</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>