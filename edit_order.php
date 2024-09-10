<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'];
    $table_id = $_POST['table_id'];
    $emp_id = $_POST['emp_id'];
    $order_status = $_POST['order_status'];

    $sql = "UPDATE orders SET table_id='$table_id', emp_id='$emp_id', order_status='$order_status' WHERE order_id='$order_id'";
    if ($conn->query($sql) === TRUE) {
        header("Location: manage_orders.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    $order_id = $_GET['id'];
    $sql = "SELECT * FROM orders WHERE order_id='$order_id'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขออเดอร์</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script>
        function checker() {
            var result = confirm('คุณต้องการออกจากระบบหรือไม่?');
            if (result == false) {
                event.preventDefault();
            }
        }
    </script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="index.php">ต้วงหมูกะทะ</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
        <form method="GET" class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
            <div class="d-flex ms-auto me-0 me-md-3 my-2 my-md-0 input-group">
                <input list="menuitems" name="query" class="form-control" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                <datalist id="menuitems">
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['name']) . " - " . htmlspecialchars($row['price']) . " บาท'>";
                        }
                    }
                    ?>
                </datalist>
            </div>
        </form>
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                    <?php if (isset($_SESSION['username'])) : ?>
                        <?php echo $_SESSION['username']; ?>
                    <?php else : ?>
                        Guest
                    <?php endif; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <?php if (isset($_SESSION['username'])) : ?>
                        <li><a onclick="checker()" class="dropdown-item" href="logout.php">Logout</a></li>
                    <?php else : ?>
                        <li><a class="dropdown-item" href="login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </li>
        </ul>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">หน้าหลัก</div>
                        <a class="nav-link" href="index.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            หน้าหลัก
                        </a>
                        <div class="sb-sidenav-menu-heading">เมนูต่างๆ</div>
                        <a class="nav-link" href="manage_menu.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                            จัดการรายการอาหาร
                        </a>
                        <a class="nav-link" href="manage_employees.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                            จัดการพนักงาน
                        </a>
                        <a class="nav-link" href="category.php">
                            <div class="sb-nav-link-icon"><i class="fas fas fa-list"></i></div>
                            จัดการหมวดหมู่
                        </a>
                        <a class="nav-link" href="unit.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-balance-scale"></i></div>
                            จัดการหน่วย
                        </a>
                        <a class="nav-link" href="manage_tables.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-table"></i></div>
                            จัดการโต๊ะ
                        </a>
                        <a class="nav-link" href="manage_orders.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-receipt"></i></div>
                            จัดการออเดอร์
                        </a>
                        <a class="nav-link" href="cashier.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-receipt"></i></div>
                            ชำระเงิน
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Logged in as:</div>
                    <?php if (isset($_SESSION['username'])) : ?>
                        <?php echo $_SESSION['username']; ?>
                    <?php else : ?>
                        Guest
                    <?php endif; ?>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Edit Order</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="manage_orders.php">Manage Orders</a></li>
                        <li class="breadcrumb-item active">Edit Order</li>
                    </ol>
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-edit me-1"></i>
                            Edit Order
                        </div>
                        <div class="card-body">
                            <form action="edit_order.php" method="post">
                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                <div class="form-group mb-3">
                                    <label for="table_id">Table ID:</label>
                                    <input type="text" class="form-control" id="table_id" name="table_id" value="<?php echo $row['table_id']; ?>" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="emp_id">Employee ID:</label>
                                    <input type="text" class="form-control" id="emp_id" name="emp_id" value="<?php echo $row['emp_id']; ?>" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="order_status">Order Status:</label>
                                    <select class="form-control" id="order_status" name="order_status" required>
                                        <option value="1" <?php if ($row['order_status'] == 1) echo 'selected'; ?>>Paid</option>
                                        <option value="2" <?php if ($row['order_status'] == 2) echo 'selected'; ?>>Not Paid</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Order</button>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Your Website 2023</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>
</html>
