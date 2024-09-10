<?php
include 'includes/db_connect.php';

if (isset($_GET['id'])) {
    $emp_id = $_GET['id'];
    $sql = "SELECT * FROM employees WHERE emp_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $employee = $result->fetch_assoc();
        echo "<p>ชื่อผู้ใช้: " . $employee['username'] . "</p>";
        echo "<p>ชื่อจริง: " . $employee['firstname'] . " " . $employee['lastname'] . "</p>";
        
        // แสดงตำแหน่ง
        switch ($employee['role']) {
            case 1:
                echo "<p>ตำแหน่ง: เจ้าของ</p>";
                break;
            case 2:
                echo "<p>ตำแหน่ง: แคชเชียร์</p>";
                break;
            case 3:
                echo "<p>ตำแหน่ง: พนักงานต้อนรับ</p>";
                break;
            case 4:
                echo "<p>ตำแหน่ง: พนักงานครัว</p>";
                break;
            default:
                echo "<p>ตำแหน่ง: ไม่ทราบ</p>";
        }

        // แสดงสถานะ
        switch ($employee['status']) {
            case 1:
                echo "<p>สถานะ: ออนไลน์</p>";
                break;
            case 2:
                echo "<p>สถานะ: ออฟไลน์</p>";
                break;
            case 3:
                echo "<p>สถานะ: ลาออก</p>";
                break;
            default:
                echo "<p>สถานะ: ไม่ทราบ</p>";
        }

        echo "<p>อีเมล: " . $employee['mail'] . "</p>";
        echo "<p>ที่อยู่: " . $employee['location'] . "</p>";
    } else {
        echo "<p>ไม่พบข้อมูลพนักงาน</p>";
    }
} else {
    echo "<p>ข้อมูลไม่ถูกต้อง</p>";
}
?>
