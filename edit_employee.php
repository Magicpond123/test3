<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update employee details
    $emp_id = $_POST['emp_id'];
    $username = $_POST['username'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $location = $_POST['location'];
    $role = $_POST['role'];
    $status = $_POST['status'];

    $sql = "UPDATE employees SET username='$username', firstname='$firstname', lastname='$lastname', mail='$email', location='$location', role='$role', status='$status' WHERE emp_id='$emp_id'";
    if ($conn->query($sql) === TRUE) {
        header("Location: manage_employees.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    // Retrieve employee data for editing
    $emp_id = $_GET['id'] ?? null;

    if ($emp_id) {
        $sql = "SELECT * FROM employees WHERE emp_id='$emp_id'";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
        } else {
            echo "ไม่พบพนักงานที่ต้องการแก้ไข";
            exit();
        }
    } else {
        echo "ไม่พบพารามิเตอร์ ID";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขพนักงาน</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
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
                    <!-- Query logic for fetching menu items -->
                </datalist>
            </div>
        </form>
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                    <?php echo $_SESSION['username'] ?? 'Guest'; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
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
                    <?php echo $_SESSION['username'] ?? 'Guest'; ?>
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Edit Employee</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="manage_employees.php">Manage Employees</a></li>
                        <li class="breadcrumb-item active">Edit Employee</li>
                    </ol>
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-edit me-1"></i>
                            Edit Employee
                        </div>
                        <div class="card-body">
                            <form action="edit_employee.php" method="post">
                                <input type="hidden" name="emp_id" value="<?php echo $row['emp_id'] ?? ''; ?>">
                                <div class="form-group mb-3">
                                    <label for="username">Username:</label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo $row['username'] ?? ''; ?>" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="firstname">First Name:</label>
                                    <input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo $row['firstname'] ?? ''; ?>" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="lastname">Last Name:</label>
                                    <input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo $row['lastname'] ?? ''; ?>" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="email">Email:</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $row['mail'] ?? ''; ?>" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="location">Location:</label>
                                    <textarea class="form-control" id="location" name="location" required><?php echo $row['location'] ?? ''; ?></textarea>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="role">Role:</label>
                                    <select class="form-control" id="role" name="role" required>
                                        <option value="1" <?php echo ($row['role'] == 1) ? 'selected' : ''; ?>>เจ้าของ</option>
                                        <option value="2" <?php echo ($row['role'] == 2) ? 'selected' : ''; ?>>แคชเชียร์</option>
                                        <option value="3" <?php echo ($row['role'] == 3) ? 'selected' : ''; ?>>พนักงานต้อนรับ</option>
                                        <option value="4" <?php echo ($row['role'] == 4) ? 'selected' : ''; ?>>พนักงานครัว</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="status">Status:</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="1" <?php echo ($row['status'] == 1) ? 'selected' : ''; ?>>ออนไลน์</option>
                                        <option value="2" <?php echo ($row['status'] == 2) ? 'selected' : ''; ?>>ออฟไลน์</option>
                                        <option value="3" <?php echo ($row['status'] == 3) ? 'selected' : ''; ?>>ลาออก</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Employee</button>
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
