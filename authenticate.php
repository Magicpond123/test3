<?php
// เชื่อมต่อฐานข้อมูล
include 'includes/db_connect.php';
session_start();

// ตรวจสอบการกรอกข้อมูลในฟอร์ม
if (isset($_POST['username']) && isset($_POST['password'])) {
    $users = $_POST['username'];
    $pass = $_POST['password'];

    // ดึงข้อมูลผู้ใช้จากฐานข้อมูล
    $sql = "SELECT * FROM employees WHERE status = 1 AND username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $users); // ใช้ "s" สำหรับ string

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stored_password = $row['password'];

            if ($pass == $stored_password) {
                // ตั้งค่า session สำหรับผู้ใช้
                $_SESSION['login'] = true;
                $_SESSION['user_id'] = $row['emp_id'];
                $_SESSION['role'] = $row['role']; // บันทึก role เพื่อตรวจสอบสิทธิ์
                $_SESSION['username'] = $row['username'];
                header("Location: index.php");
                exit();
            } else {
                echo "Invalid username or password";
            }
        } else {
            echo "Invalid username or password";
        }
    } else {
        echo "Error executing query: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Please enter username and password.";
}

$conn->close(); // ปิดการเชื่อมต่อฐานข้อมูล
?>
