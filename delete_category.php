<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'includes/db_connect.php';

if (!isset($_GET['id'])) {
    header("Location: category.php");
    exit();
}

$category_id = $_GET['id'];

$sql = "DELETE FROM category WHERE category_id = '$category_id'";
if ($conn->query($sql) === TRUE) {
    header("Location: category.php");
} else {
    echo "Error deleting record: " . $conn->error;
}
?>
