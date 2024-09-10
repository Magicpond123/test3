<?php
session_start();

// ตรวจสอบว่าเข้าสู่ระบบหรือไม่
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit();
}

// ตรวจสอบสิทธิ์การเข้าถึงตาม Role
if ($_SESSION['role'] != 1 && $_SESSION['role'] != 4) {
    header('Location: unauthorized.php'); // หรือแสดงข้อความว่าไม่อนุญาต
    exit();
}


include 'includes/db_connect.php';

// Fetch menu items data
$sql = "SELECT mi.item_id, mi.name, mi.description, mi.price, mi.image_path, mi.order_type, 
               c.type AS category, u.name AS unit 
        FROM menuitems mi 
        JOIN category c ON mi.category_id = c.category_id 
        JOIN unit u ON mi.unit_id = u.unit_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Menu</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function showDetails(itemId) {
            $.ajax({
                url: "fetch_item_details.php",
                method: "POST",
                data: {
                    id: itemId
                },
                success: function(data) {
                    $('#itemDetailsModal .modal-body').html(data);
                    $('#itemDetailsModal').modal('show');
                }
            });
        }

        function deleteItem(item_id) {
            Swal.fire({
                title: "คุณต้องการลบหรือไม่?",
                text: "",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `delete_menu_item.php?id=${item_id}`;
                }
            });
        }

        function logoutCheck(event) {
            if (!confirm('คุณต้องการออกจากระบบหรือไม่?')) {
                event.preventDefault();
            }
        }
    </script>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="index.php">ต้วงหมูกะทะ</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
            <i class="fas fa-bars"></i>
        </button>
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
                    <?= isset($_SESSION['username']) ? $_SESSION['username'] : 'ผู้เยี่ยมชม'; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <?php if (isset($_SESSION['username'])) : ?>
                        <li><a class="dropdown-item" href="logout.php" onclick="logoutCheck(event)">ออกจากระบบ</a></li>
                    <?php else : ?>
                        <li><a class="dropdown-item" href="login.php">เข้าสู่ระบบ</a></li>
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
                            <div class="sb-nav-link-icon"><i class="fas fa-list"></i></div>
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
                        <a class="nav-link" href="manage_promotions.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>
                            จัดการโปรโมชั่น
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Logged in as:</div>
                    <?= isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest'; ?>
                </div>
            </nav>
        </div>

        <!-- Modal for item details -->
        <div class="modal fade" id="itemDetailsModal" tabindex="-1" role="dialog" aria-labelledby="itemDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="itemDetailsModalLabel">รายละเอียดรายการอาหาร</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Details content will be loaded here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">จัดการรายการอาหาร</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item active">จัดการรายการอาหาร</li>
                    </ol>
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-table me-1"></i>
                            เพิ่มรายการอาหาร
                        </div>
                        <div class="card-body">
                            <a href="add_menu_item.php" class="btn btn-primary mb-3">เพิ่มรายการอาหาร</a>
                            <table id="datatablesSimple" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th style="text-align: center;">ลำดับ</th>
                                        <th style="text-align: center;">ชื่อ</th>
                                        <th style="text-align: center;">ราคา</th>
                                        <th style="text-align: center;">ประเภท</th>
                                        <th style="text-align: center;">หน่วย</th>
                                        <th style="text-align: center;">ชนิด</th>
                                        <th style="text-align: center;">ปรับแต่ง</th>
                                        <th style="text-align: center;">ดูรายละเอียด</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; ?>
                                    <?php while ($row = $result->fetch_assoc()) : ?>
                                        <tr>
                                            <td style="text-align: center;"><?= $i++; ?></td>
                                            <td style="text-align: left;"><?= htmlspecialchars($row['name']); ?></td>
                                            <td style="text-align: right;"><?= number_format($row['price'], 2); ?></td>
                                            <td style="text-align: left;"><?= htmlspecialchars($row['category']); ?></td>
                                            <td style="text-align: left;"><?= htmlspecialchars($row['unit']); ?></td>
                                            <td style="text-align: left;">
                                                <?php
                                                switch ($row['order_type']) {
                                                    case 1:
                                                        echo "บุฟเฟ่ต์";
                                                        break;
                                                    case 2:
                                                        echo "อะลาคาร์ท";
                                                        break;
                                                    default:
                                                        echo "ไม่ทราบประเภท";
                                                        break;
                                                }
                                                ?>
                                            </td>
                                            <td style="text-align: center;">
                                                <a href="edit_menu_item.php?id=<?= $row['item_id']; ?>" class="btn btn-warning btn-sm">แก้ไข</a>
                                                <button class="btn btn-danger btn-sm delete-btn" data-id="<?= $row['item_id']; ?>" onclick="deleteItem(<?= $row['item_id']; ?>)">ลบ</button>
                                            </td>
                                            <td style="text-align: center;">
                                                <a href="javascript:void(0);" class="btn btn-info btn-sm" onclick="showDetails(<?= $row['item_id']; ?>)">ดูรายละเอียด</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
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
</body>

</html>