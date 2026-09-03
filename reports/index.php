<?php 
session_start();
require "../config/db.php";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/navbar.php';

// 1. إعدادات فلاتر التاريخ (الافتراضي: عرض تقرير الشهر الحالي)
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date   = $_GET['end_date'] ?? date('Y-m-t');

// 2. إجمالي المبيعات للفترة المحددة
$stmtSales = $conn->prepare("
    SELECT SUM(total) FROM sales 
    WHERE DATE(sale_datetime) BETWEEN ? AND ?
");
$stmtSales->execute([$start_date, $end_date]);
$total_sales = $stmtSales->fetchColumn() ?: 0;

// 3. إجمالي المصاريف للفترة المحددة
$stmtExpenses = $conn->prepare("
    SELECT SUM(amount) FROM expenses 
    WHERE DATE(date) BETWEEN ? AND ?
");
$stmtExpenses->execute([$start_date, $end_date]);
$total_expenses = $stmtExpenses->fetchColumn() ?: 0;

// 4. حساب صافي الربح
$net_profit = $total_sales - $total_expenses;

// 5. أكثر 5 منتجات مبيعاً خلال الفترة
$stmtTopProducts = $conn->prepare("
    SELECT 
        p.name, 
        SUM(si.qty) as total_qty, 
        SUM(si.qty * si.price) as total_revenue
    FROM sale_items si
    JOIN sales s ON si.sale_id = s.id
    JOIN products p ON si.product_id = p.id
    WHERE DATE(s.sale_datetime) BETWEEN ? AND ?
    GROUP BY si.product_id
    ORDER BY total_qty DESC
    LIMIT 5
");
$stmtTopProducts->execute([$start_date, $end_date]);
$top_products = $stmtTopProducts->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="m-0"><i class="fa-solid fa-chart-line text-primary me-2"></i> Financial & Sales Reports</h4>
    </div>

    <!-- فلتر التاريخ -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">From Date</label>
                    <input type="date" name="start_date" class="form-control rounded-3" value="<?= $start_date ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">To Date</label>
                    <input type="date" name="end_date" class="form-control rounded-3" value="<?= $end_date ?>">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-3 w-100 fw-semibold">
                        <i class="fa-solid fa-filter me-1"></i> Filter Report
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary rounded-3">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- بطاقات المخصصة للأرقام المالية -->
    <div class="row g-3 mb-4">
        <!-- إجمالي المبيعات -->
        <div class="col-md-4">
            <div class="card bg-success text-white border-0 shadow-sm rounded-4 p-3">
                <small class="text-white-50 fw-semibold">Total Revenue (Sales)</small>
                <h3 class="fw-bold m-0">$<?= number_format($total_sales, 2) ?></h3>
            </div>
        </div>
        <!-- إجمالي المصاريف -->
        <div class="col-md-4">
            <div class="card bg-danger text-white border-0 shadow-sm rounded-4 p-3">
                <small class="text-white-50 fw-semibold">Total Expenses</small>
                <h3 class="fw-bold m-0">$<?= number_format($total_expenses, 2) ?></h3>
            </div>
        </div>
        <!-- صافي الربح -->
        <div class="col-md-4">
            <div class="card <?= $net_profit >= 0 ? 'bg-primary' : 'bg-warning' ?> text-white border-0 shadow-sm rounded-4 p-3">
                <small class="text-white-50 fw-semibold">Net Profit / Loss</small>
                <h3 class="fw-bold m-0">$<?= number_format($net_profit, 2) ?></h3>
            </div>
        </div>
    </div>

    <!-- جدول المنتجات الأكثر مبيعاً -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="fw-bold m-0"><i class="fa-solid fa-trophy text-warning me-2"></i> Top 5 Best Selling Products</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product Name</th>
                            <th class="text-center">Quantity Sold</th>
                            <th class="text-end">Total Revenue Generated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($top_products)): ?>
                            <?php foreach($top_products as $item): ?>
                                <tr>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($item['name']) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary rounded-pill"><?= $item['total_qty'] ?> pcs</span>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        $<?= number_format($item['total_revenue'], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">
                                    No sales transactions recorded in this period.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>