<?php 
session_start();
require "../config/db.php";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/navbar.php';

// جلب المبيعات مع اسم الزبون، الكاشير، وعدد المواد
$sales = $conn->query("
    SELECT 
        s.id,
        s.invoice_no,
        s.total,
        s.payment_method,
        s.sale_datetime,
        c.name AS customer_name,
        u.name AS cashier_name,
        COUNT(si.id) AS total_items
    FROM sales s
    LEFT JOIN customers c ON s.customer_id = c.id
    LEFT JOIN users u ON s.user_id = u.id
    LEFT JOIN sale_items si ON s.id = si.sale_id
    GROUP BY s.id
    ORDER BY s.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="m-0"><i class="fa-solid fa-file-invoice text-primary me-2"></i> Sales & Invoices</h4>
        <a href="../pos/index.php" class="btn btn-primary btn-sm rounded-3">
            <i class="fa-solid fa-plus me-1"></i> New Order (POS)
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Date & Time</th>
                            <th>Customer</th>
                            <th>Cashier</th>
                            <th class="text-center">Items</th>
                            <th>Payment Method</th>
                            <th class="text-end">Total Amount</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($sales)): ?>
                            <?php foreach($sales as $sale): ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($sale['invoice_no']) ?></span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= date('Y-m-d h:i A', strtotime($sale['sale_datetime'])) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($sale['customer_name'] ?? 'Walk-in Customer') ?></td>
                                    <td><?= htmlspecialchars($sale['cashier_name'] ?? 'System') ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary rounded-pill"><?= $sale['total_items'] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark"><?= htmlspecialchars($sale['payment_method']) ?></span>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        $<?= number_format($sale['total'], 2) ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="invoice_details.php?id=<?= $sale['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="fa-solid fa-eye me-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-receipt fa-2x mb-2 opacity-50"></i>
                                    <div>No sales transactions found.</div>
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