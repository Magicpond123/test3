<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'includes/db_connect.php';

$emp_id = $_GET['id'];

$sql = "DELETE FROM employees WHERE emp_id='$emp_id'";
if ($conn->query($sql) === TRUE) {
    header("Location: manage_employees.php");
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
?>
