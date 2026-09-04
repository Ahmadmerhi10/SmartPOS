<?php 
// 1. بدء الجلسة أولاً
session_start();

// تفعيل بيانات جلسة مؤقتة للتجربة (استناداً إلى بيانات جدول users المتوفرة)
$_SESSION['user_name'] = $_SESSION['user_name'] ?? 'Admin';
$_SESSION['user_role'] = $_SESSION['user_role'] ?? 'admin';

// 2. الاتصال بقاعدة البيانات والملفات
require "config/db.php";
include 'includes/header.php';
?>
<div class="bg-dark text-white border-end p-3" id="sidebar-wrapper" style="width: 250px; min-height: 100vh;">
    <div class="sidebar-heading text-center py-3 fs-4 fw-bold border-bottom text-primary">
        <i class="fa-solid fa-cash-register me-2"></i> SmartPOS
    </div>
    <div class="list-group list-group-flush mt-3">
        <a href="index.php" class="list-group-item list-group-item-action bg-dark text-white py-2">
            <i class="fa-solid fa-chart-line me-2"></i> Dashboard
        </a>
        <a href="pos/index.php" class="list-group-item list-group-item-action bg-dark text-white py-2">
            <i class="fa-solid fa-cart-shopping me-2"></i> POS / Checkout
        </a>
        <a href="products/index.php" class="list-group-item list-group-item-action bg-dark text-white py-2">
            <i class="fa-solid fa-box me-2"></i> Products
        </a>
        <a href="categories/index.php" class="list-group-item list-group-item-action bg-dark text-white py-2">
            <i class="fa-solid fa-tags me-2"></i> Categories
        </a>
        <a href="suppliers/index.php" class="list-group-item list-group-item-action bg-dark text-white py-2">
            <i class="fa-solid fa-truck me-2"></i> Suppliers
        </a>
        <a href="sales/index.php" class="list-group-item list-group-item-action bg-dark text-white py-2">
            <i class="fa-solid fa-receipt me-2"></i> Sales & Invoices
        </a>
        <a href="expenses/index.php" class="list-group-item list-group-item-action bg-dark text-white py-2">
            <i class="fa-solid fa-wallet me-2"></i> Expenses
        </a>
        <a href="reports/index.php" class="list-group-item list-group-item-action bg-dark text-white py-2">
            <i class="fa-solid fa-file-invoice-dollar me-2"></i> Reports
        </a>
    </div>
</div>
<?php
include 'includes/navbar.php';

// --- استعلامات قاعدة البيانات المحدثة بناءً على المخطط الجديد ---

// مجموع مبيعات اليوم
$stmt1 = $conn->prepare("SELECT SUM(total) FROM sales WHERE DATE(sale_datetime) = CURDATE()");
$stmt1->execute();
$today_sales = $stmt1->fetchColumn() ?: 0;

// عدد فواتير اليوم
$stmt2 = $conn->prepare("SELECT COUNT(*) FROM sales WHERE DATE(sale_datetime) = CURDATE()");
$stmt2->execute();
$total_invoices = $stmt2->fetchColumn();

// إجمالي المنتجات
$stmt3 = $conn->prepare("SELECT COUNT(*) FROM products");
$stmt3->execute();
$total_products = $stmt3->fetchColumn();

// المنتجات القريبة من النفاذ (اعتماداً على العمود المخصص low_stock_alert)
$stmt4 = $conn->prepare("SELECT COUNT(*) FROM products WHERE stock <= low_stock_alert");
$stmt4->execute();
$total_alert = $stmt4->fetchColumn();

// آخر المبيعات (مع جلب اسم المستخدم وطريقة الدفع)
$stmt5 = $conn->prepare("SELECT sales.id, sales.invoice_no, users.name AS cashier_name, sales.total, sales.payment_method, sales.sale_datetime 
                        FROM sales 
                        INNER JOIN users ON sales.user_id = users.id 
                        ORDER BY sales.sale_datetime DESC 
                        LIMIT 5");
$stmt5->execute();
$recent_sales = $stmt5->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPOS - Dashboard</title>

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<body class="bg-light">

    <div class="d-flex" id="wrapper">

        <!-- Page Content -->
        <div id="page-content-wrapper" class="w-100">

            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4 py-3 shadow-sm">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <h4 class="m-0 fw-bold text-dark">
                        <i class="fa-solid fa-gauge text-primary me-2"></i> Dashboard
                    </h4>
                    
                    <!-- User Profile & Logout -->
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted fs-6">
                            Welcome, <strong class="text-dark"><?= htmlspecialchars($_SESSION['user_name']) ?></strong> 
                            <span class="badge bg-primary ms-1"><?= htmlspecialchars($_SESSION['user_role']) ?></span>
                        </span>
                        <a href="auth/logout.php" class="btn btn-outline-danger btn-sm rounded-3">
                            <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Main Container -->
            <div class="container-fluid px-4 py-4">

                <!-- 1. Stats Cards Section -->
                <div class="row g-3 mb-4">

                    <!-- Today's Sales -->
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted fw-normal mb-1">Today's Sales</h6>
                                    <h3 class="fw-bold text-dark mb-0">$<?= number_format($today_sales, 2) ?></h3>
                                </div>
                                <div class="bg-success-subtle text-success p-3 rounded-circle fs-4">
                                    <i class="fa-solid fa-dollar-sign"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoices Today -->
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted fw-normal mb-1">Invoices Today</h6>
                                    <h3 class="fw-bold text-dark mb-0"><?= $total_invoices ?></h3>
                                </div>
                                <div class="bg-primary-subtle text-primary p-3 rounded-circle fs-4">
                                    <i class="fa-solid fa-receipt"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Products -->
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted fw-normal mb-1">Total Products</h6>
                                    <h3 class="fw-bold text-dark mb-0"><?= $total_products ?></h3>
                                </div>
                                <div class="bg-info-subtle text-info p-3 rounded-circle fs-4">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Low Stock Warning -->
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted fw-normal mb-1">Low Stock Warning</h6>
                                    <h3 class="fw-bold text-danger mb-0"><?= $total_alert ?></h3>
                                </div>
                                <div class="bg-danger-subtle text-danger p-3 rounded-circle fs-4">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- 2. Quick Actions & Recent Activity Section -->
                <div class="row g-4">

                    <!-- Quick Shortcuts -->
                    <div class="col-12 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h5 class="fw-bold m-0">Quick Actions</h5>
                            </div>
                            <div class="card-body px-4 d-flex flex-column gap-3">
                                <a href="pos/index.php" class="btn btn-primary py-3 rounded-3 fw-bold fs-6 shadow-sm d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-cash-register me-2 fs-5"></i> Open POS Terminal
                                </a>
                                <a href="products/add.php" class="btn btn-outline-dark py-2 rounded-3 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-plus me-2"></i> Add New Product
                                </a>
                                <a href="sales/index.php" class="btn btn-outline-secondary py-2 rounded-3 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-file-invoice-dollar me-2"></i> View Sales History
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Sales Table -->
                    <div class="col-12 col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold m-0">Recent Sales</h5>
                                <a href="sales/index.php" class="btn btn-link text-decoration-none p-0">View All</a>
                            </div>
                            <div class="card-body px-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-4">Invoice #</th>
                                                <th>Cashier</th>
                                                <th>Payment Method</th>
                                                <th>Total</th>
                                                <th>Date</th>
                                                <th class="text-end pe-4">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($recent_sales)): ?>
                                                <?php foreach($recent_sales as $ligne) : ?>
                                                <tr>
                                                    <td class="ps-4 fw-semibold">#<?= htmlspecialchars($ligne["invoice_no"]) ?></td>
                                                    <td><?= htmlspecialchars($ligne["cashier_name"]) ?></td>
                                                    <td>
                                                        <span class="badge bg-secondary-subtle text-dark border">
                                                            <?= htmlspecialchars($ligne["payment_method"] ?? 'Cash') ?>
                                                        </span>
                                                    </td>
                                                    <td class="fw-bold text-success">$<?= number_format($ligne["total"], 2) ?></td>
                                                    <td class="text-muted fs-7"><?= date('M d, H:i', strtotime($ligne["sale_datetime"])) ?></td>
                                                    <td class="text-end pe-4">
                                                        <a href="sales/view.php?id=<?= $ligne['id'] ?>" class="btn btn-light btn-sm rounded-circle">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center py-3 text-muted">No recent sales found.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>

<?php 
include 'includes/footer.php'; 
?>