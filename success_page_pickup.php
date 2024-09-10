<?php
session_start();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สั่งสำเร็จ</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .success-container {
            text-align: center;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .success-container h1 {
            color: #4CAF50;
            margin-bottom: 20px;
        }

        .success-container p {
            font-size: 1.2em;
            color: #333333;
            margin-bottom: 30px;
        }

        .success-container a {
            text-decoration: none;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s, box-shadow 0.3s, transform 0.3s;
        }

        .success-container a:hover {
            background-color: #45a049;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="success-container">
        <h1>สั่งสำเร็จ!</h1>
        <p>ขอบคุณสำหรับการสั่งซื้อของคุณ.</p>
        <a href="menu_order_pickup.php">กลับไปที่เมนูอาหาร</a>
    </div>
</body>
</html>
