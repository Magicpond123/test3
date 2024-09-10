<?php
session_start();

// ตรวจสอบว่าเข้าสู่ระบบหรือไม่
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

// ตรวจสอบสิทธิ์การเข้าถึงตาม Role
if ($_SESSION['role'] != 1) {
    header('Location: unauthorized.php'); // หรือแสดงข้อความว่าไม่อนุญาต
    exit();
}

include 'includes/db_connect.php';

// Fetch employees data
$sql = "SELECT emp_id, username, firstname, lastname, mail, location, role, status FROM employees";
$result = $conn->query($sql);

if (!$result) {
    die("Query Failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>จัดการพนักงาน</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/styles.css">
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
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
            <div class="input-group">
                <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                <button class="btn btn-danger" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
            </div>
        </form>
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                    <?php if (isset($_SESSION['username'])) : ?>
                        <?php echo $_SESSION['username']; ?>
                    <?php else : ?>
                        ผู้เยี่ยมชม
                    <?php endif; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <?php if (isset($_SESSION['username'])) : ?>
                        <li><a class="dropdown-item" href="logout.php">ออกจากระบบ</a></li>
                    <?php else : ?>
                        <li><a class="dropdown-item" href="login.php">เข้าสู่ระบบ</a></li>
                    <?php endif; ?>
                </ul>
            </li>
        </ul>
    </nav>
    <!-- Employee Details Modal -->
    <div class="modal fade" id="employeeDetailsModal" tabindex="-1" aria-labelledby="employeeDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="employeeDetailsModalLabel">รายละเอียดพนักงาน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Employee details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

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
                    <h1 class="mt-4">จัดการพนักงาน</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item active">จัดการพนักงาน</li>
                    </ol>
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-table me-1"></i>
                            ข้อมูลพนักงาน
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <a href="register.php" class="btn btn-primary">สมัครพนักงานใหม่</a>
                            </div>
                            <table id="datatablesSimple" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th style="text-align: center;">ลำดับ</th>
                                        <th style="text-align: center;">ชื่อผู้ใช้</th>
                                        <th style="text-align: center;">ชื่อจริง</th>
                                        <th style="text-align: center;">นามสกุล</th>
                                        <th style="text-align: center;">ตำแหน่ง</th>
                                        <th style="text-align: center;">สถานะ</th>
                                        <th style="text-align: center;">ปรับแต่ง</th>
                                        <th style="text-align: center;">รายละเอียด</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    while ($row = $result->fetch_assoc()) { ?>
                                        <tr>
                                            <td style="text-align: center;"><?php echo $i++; ?></td>
                                            <td style="text-align: left;"><?php echo $row['username']; ?></td>
                                            <td style="text-align: left;"><?php echo $row['firstname']; ?></td>
                                            <td style="text-align: left;"><?php echo $row['lastname']; ?></td>
                                            <td style="text-align: left;">
                                                <?php
                                                switch ($row['role']) {
                                                    case 1:
                                                        echo 'เจ้าของ';
                                                        break;
                                                    case 2:
                                                        echo 'แคชเชียร์';
                                                        break;
                                                    case 3:
                                                        echo 'พนักงานต้อนรับ';
                                                        break;
                                                    case 4:
                                                        echo 'พนักงานครัว';
                                                        break;
                                                    case 5:
                                                        echo 'ลาออก';
                                                    default:
                                                        echo 'ไม่ทราบ';
                                                }
                                                ?>
                                            </td>
                                            <td style="text-align: center;"><?php
                                                                            if ($row['status'] == 1) echo 'ออนไลน์';
                                                                            elseif ($row['status'] == 2) echo 'ออฟไลน์';
                                                                            else echo 'ลาออก'; ?>
                                            </td>
                                            <td style="text-align: center;">
                                                <a href="edit_employee.php?id=<?php echo $row['emp_id']; ?>" class="btn btn-warning btn-sm">แก้ไข</a>
                                                <a href="delete_employee.php?id=<?php echo $row['emp_id']; ?>" class="btn btn-danger btn-sm">ลบ</a>
                                            </td>
                                            <td>
                                                <!-- Button trigger modal -->
                                                <button type="button" class="btn btn-info btn-sm view-details" data-id="<?php echo $row['emp_id']; ?>">
                                                    แสดงรายละเอียด
                                                </button>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
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
    <script>
        document.querySelectorAll('.view-details').forEach(button => {
            button.addEventListener('click', function() {
                const empId = this.getAttribute('data-id');
                fetch('fetch_employee_details.php?id=' + empId)
                    .then(response => response.text())
                    .then(data => {
                        document.querySelector('#employeeDetailsModal .modal-body').innerHTML = data;
                        new bootstrap.Modal(document.getElementById('employeeDetailsModal')).show();
                    });
            });
        });
    </script>



</body>

</html>