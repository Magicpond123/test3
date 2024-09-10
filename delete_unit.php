<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'includes/db_connect.php';

if (!isset($_GET['id'])) {
    header("Location: unit.php");
    exit();
}

$unit_id = $_GET['id'];

// Delete related menuitems first
$sql = "DELETE FROM menuitems WHERE unit_id = '$unit_id'";
if ($conn->query($sql) === TRUE) {
    $sql = "DELETE FROM unit WHERE unit_id = '$unit_id'";
    if ($conn->query($sql) === TRUE) {
        header("Location: unit.php");
    } else {
        echo "Error deleting unit: " . $conn->error;
    }
} else {
    echo "Error deleting menuitems: " . $conn->error;
}
?>
