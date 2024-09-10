<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'includes/db_connect.php';

$item_id = $_GET['id'];

$sql = "DELETE FROM menuitems WHERE item_id='$item_id'";
if ($conn->query($sql) === TRUE) {
    header("Location: manage_menu.php");
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
?>
