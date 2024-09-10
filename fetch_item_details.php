<?php
include 'includes/db_connect.php';

if(isset($_POST['id'])) {
    $item_id = $_POST['id'];
    $query = "SELECT menuitems.item_id, menuitems.name, menuitems.description, menuitems.price, menuitems.image_path, 
               menuitems.status, category.type AS category, unit.name AS unit 
        FROM menuitems 
        JOIN category ON menuitems.category_id = category.category_id 
        JOIN unit ON menuitems.unit_id = unit.unit_id
        WHERE menuitems.item_id = $item_id";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        echo '<p><strong>ชื่อ: </strong>' . $row['name'] . '</p>';
        echo '<p><strong>คำอธิบาย: </strong>' . $row['description'] . '</p>';
        echo '<p><strong>ราคา: </strong>' . number_format($row['price'], 2) . ' บาท</p>';
        echo '<p><strong>ประเภท: </strong>' . $row['category'] . '</p>';
        echo '<p><strong>หน่วย: </strong>' . $row['unit'] . '</p>';
        echo '<p><strong>สถานะ: </strong>' . ($row['status'] == 1 ? 'พร้อมบริการ' : 'ไม่พร้อมบริการ') . '</p>';
        echo '<img src="' . $row['image_path'] . '" alt="Menu Item Image" style="width: 200px; height: auto;">';
    } else {
        echo '<p>ไม่พบข้อมูล</p>';
    }
}
?>
