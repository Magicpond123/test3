<?php
session_start();
include 'includes/db_connect.php'; // เชื่อมต่อกับฐานข้อมูล

// ตรวจสอบว่ามีการส่งค่า ID มาหรือไม่
if (isset($_GET['id'])) {
    $order_id = intval($_GET['id']);

    // เริ่มต้นการทำงานของ Transaction
    $conn->begin_transaction();

    try {
        // ลบข้อมูลจากตาราง orderdetails
        $stmt_details = $conn->prepare("DELETE FROM orderdetails WHERE order_id = ?");
        $stmt_details->bind_param("i", $order_id);
        $stmt_details->execute();
        $stmt_details->close();

        // ลบข้อมูลจากตาราง orders
        $stmt_order = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
        $stmt_order->bind_param("i", $order_id);
        $stmt_order->execute();
        $stmt_order->close();

        // ถ้าทุกอย่างเรียบร้อย ให้ commit การทำงานของ Transaction
        $conn->commit();

        // ลบสำเร็จ เปลี่ยนเส้นทางกลับไปยัง manage_orders.php
        header("Location: manage_orders.php?message=Order deleted successfully");
        exit();
    } catch (Exception $e) {
        // หากเกิดข้อผิดพลาด ให้ rollback การทำงานของ Transaction
        $conn->rollback();

        // แสดงข้อผิดพลาด
        echo "Error deleting order: " . $e->getMessage();
    }
} else {
    // หากไม่มีการส่ง ID มา แสดงข้อผิดพลาด
    echo "Error: ID not found.";
}
?>
