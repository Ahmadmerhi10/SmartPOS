<?php 
session_start();
require "../config/db.php";

// =========================================================
// 1. معالجة البيانات (قبل استدعاء الهيدر والـ HTML)
// =========================================================

// إضافة مصروف جديد
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_expense'])) {
    $title = trim($_POST['title']);
    $amount = floatval($_POST['amount']);
    $note = trim($_POST['note']);

    if (!empty($title) && $amount > 0) {
        $stmt = $conn->prepare("INSERT INTO expenses (title, amount, note, date) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$title, $amount, $note]);
        header("Location: index.php");
        exit;
    }
}

// حذف مصروف
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: index.php");
    exit;
}

// =========================================================
// 2. استدعاء واجهة المستخدم والـ HTML
// =========================================================
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/navbar.php';

// جلب قائمة المصاريف وإجمالي المبالغ
$expenses = $conn->query("SELECT * FROM expenses ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$total_expenses = $conn->query("SELECT SUM(amount) FROM expenses")->fetchColumn() ?: 0;
?>

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="m-0"><i class="fa-solid fa-wallet text-danger me-2"></i> Expenses Management</h4>
        <button class="btn btn-danger btn-sm rounded-3" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
            <i class="fa-solid fa-plus me-1"></i> Add Expense
        </button>
    </div>

    <!-- كارت إجمالي المصاريف -->
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card bg-danger text-white border-0 shadow-sm rounded-4 p-3">
                <small class="text-white-50 fw-semibold">Total Expenses</small>
                <h3 class="fw-bold m-0">$<?= number_format($total_expenses, 2) ?></h3>
            </div>
        </div>
    </div>

    <!-- جدول عرض المصاريف -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Note</th>
                            <th>Date</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($expenses)): ?>
                            <?php foreach($expenses as $exp): ?>
                                <tr>
                                    <td><strong>#<?= $exp['id'] ?></strong></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($exp['title']) ?></td>
                                    <td class="text-muted"><?= htmlspecialchars($exp['note'] ?? '-') ?></td>
                                    <td>
                                        <small class="text-muted"><?= date('Y-m-d h:i A', strtotime($exp['date'])) ?></small>
                                    </td>
                                    <td class="text-end fw-bold text-danger">
                                        $<?= number_format($exp['amount'], 2) ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="index.php?action=delete&id=<?= $exp['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger rounded-pill px-3" 
                                           onclick="return confirm('Are you sure you want to delete this expense?');">
                                            <i class="fa-solid fa-trash-can me-1"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-receipt fa-2x mb-2 opacity-50"></i>
                                    <div>No expenses recorded yet.</div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal لإضافة مصروف جديد -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="index.php" class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title fw-bold"><i class="fa-solid fa-plus-circle text-danger me-2"></i> Add New Expense</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label fw-semibold">Title / Description</label>
            <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. Electricity Bill, Shop Rent" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Amount ($)</label>
            <input type="number" step="0.01" name="amount" class="form-control rounded-3" placeholder="0.00" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Note (Optional)</label>
            <textarea name="note" class="form-control rounded-3" rows="2" placeholder="Additional details..."></textarea>
        </div>
      </div>
      <div class="modal-footer border-top-0">
        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" name="add_expense" class="btn btn-danger rounded-3 fw-semibold">Save Expense</button>
      </div>
    </form>
  </div>
</div>

<?php include '../includes/footer.php'; ?>