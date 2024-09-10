<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'includes/db_connect.php';

$item_id = $_GET['id'];
$sql = "SELECT * FROM menuitems WHERE item_id = $item_id";
$result = $conn->query($sql);
$menu_item = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $image_path = $menu_item['image_path'];
    $status = $_POST['status'];
    $order_type = $_POST['order_type'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        $image_path = $target_dir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);
    }
    $unit_id = isset($_POST['unit_id']) ? $_POST['unit_id'] : null;

    $sql = "UPDATE menuitems SET 
         name = '$name', 
         description = '$description', 
         price = '$price', 
         category_id = '$category_id', 
         unit_id = '$unit_id',
         status = '$status',
         order_type = '$order_type' 
        WHERE item_id = '$item_id'";
    if ($conn->query($sql) === TRUE) {
        header("Location: manage_menu.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>แก้ไขรายการเมนู</title>
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
                        <li><a onclick=checker() class="dropdown-item" href="logout.php">Logout</a></li>
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
                    <h1 class="mt-4">แก้ไขรายการอาหาร</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="manage_menu.php">จัดการรายการอาหาร</a></li>
                        <li class="breadcrumb-item active">แก้ไขรายการอาหาร</li>
                    </ol>
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-edit me-1"></i>
                            แก้ไขรายการอาหาร
                        </div>
                        <div class="card-body">
                            <form action="edit_menu_item.php?id=<?php echo $item_id; ?>" method="post" enctype="multipart/form-data">
                                <div class="form-group mb-3">
                                    <label for="name">ชื่อ:</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $menu_item['name']; ?>" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="description">คำอธิบาย:</label>
                                    <textarea class="form-control" id="description" name="description" required><?php echo $menu_item['description']; ?></textarea>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="price">ราคา:</label>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo $menu_item['price']; ?>" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="category_id">หมวดหมู่:</label>
                                    <select class="form-control" id="category_id" name="category_id" required>
                                        <option value="1" <?php echo ($menu_item['category_id'] == 1) ? 'selected' : ''; ?>>อาหาร</option>
                                        <option value="2" <?php echo ($menu_item['category_id'] == 2) ? 'selected' : ''; ?>>เครื่องดื่ม</option>
                                        <option value="3" <?php echo ($menu_item['category_id'] == 3) ? 'selected' : ''; ?>>ของหวาน</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="unit_id">หน่วย:</label>
                                    <select class="form-control" id="unit_id" name="unit_id" required>
                                        <option value="1" <?php echo ($menu_item['unit_id'] == 1) ? 'selected' : ''; ?>>กิโลกรัม</option>
                                        <option value="2" <?php echo ($menu_item['unit_id'] == 2) ? 'selected' : ''; ?>>กรัม</option>
                                        <option value="3" <?php echo ($menu_item['unit_id'] == 3) ? 'selected' : ''; ?>>ชิ้น</option>
                                        <option value="4" <?php echo ($menu_item['unit_id'] == 4) ? 'selected' : ''; ?>>แพ็ค</option>
                                        <option value="5" <?php echo ($menu_item['unit_id'] == 5) ? 'selected' : ''; ?>>ขวด</option>
                                        <option value="6" <?php echo ($menu_item['unit_id'] == 6) ? 'selected' : ''; ?>>จาน</option>
                                        <option value="7" <?php echo ($menu_item['unit_id'] == 7) ? 'selected' : ''; ?>>ขีด</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="status">สถานะ:</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="1" <?php echo ($menu_item['status'] == 1) ? 'selected' : ''; ?>>มีให้บริการ</option>
                                        <option value="2" <?php echo ($menu_item['status'] == 2) ? 'selected' : ''; ?>>ไม่มีให้บริการ</option>
                                        <option value="2" <?php echo ($menu_item['status'] == 3) ? 'selected' : ''; ?>>หมด</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="image">รูปภาพ:</label>
                                    <input type="file" class="form-control" id="image" name="image">
                                    <?php if ($menu_item['image_path']) { ?>
                                        <img src="<?php echo $menu_item['image_path']; ?>" alt="รูปภาพรายการอาหาร" style="width: 100px; height: auto;">
                                    <?php } ?>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="order_type">ชนิด:</label>
                                    <select class="form-control" id="order_type" name="order_type" required>
                                        <option value="1" <?php echo ($menu_item['order_type'] == 1) ? 'selected' : ''; ?>>บุฟเฟ่ต์</option>
                                        <option value="2" <?php echo ($menu_item['order_type'] == 2) ? 'selected' : ''; ?>>อาหารพร้อมสั่ง</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
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