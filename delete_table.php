<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'includes/db_connect.php';

$table_id = $_GET['id'];

$sql = "DELETE FROM tables WHERE table_id='$table_id'";
if ($conn->query($sql) === TRUE) {
    header("Location: manage_tables.php");
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
?>
