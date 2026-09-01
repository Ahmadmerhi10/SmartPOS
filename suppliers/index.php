<?php 
session_start();
require "../config/db.php";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/navbar.php';

// جلب جميع الموردين من قاعدة البيانات
$stmt = $conn->prepare("SELECT * FROM suppliers ORDER BY id DESC");
$stmt->execute();
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid px-4 py-4">

    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold m-0 text-dark">
                <i class="fa-solid fa-truck-field text-primary me-2"></i> Suppliers Management
            </h4>
            <small class="text-muted">Manage product suppliers and contact details</small>
        </div>
        <button type="button" class="btn btn-primary rounded-3 px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
            <i class="fa-solid fa-plus me-1"></i> Add New Supplier
        </button>
    </div>

    <!-- Alert Notifications -->
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Suppliers Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body px-0 py-2">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#ID</th>
                            <th>Supplier Name</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($suppliers)): ?>
                            <?php foreach($suppliers as $supplier): ?>
                            <tr>
                                <td class="ps-4 fw-semibold text-muted">#<?= $supplier['id'] ?></td>
                                <td><span class="fw-bold text-dark"><?= htmlspecialchars($supplier['name']) ?></span></td>
                                <td><?= htmlspecialchars($supplier['phone'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($supplier['address'] ?? 'N/A') ?></td>
                                <td class="text-end pe-4">
                                    <button type="button" class="btn btn-light btn-sm text-primary rounded-circle me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editSupplierModal"
                                            data-id="<?= $supplier['id'] ?>"
                                            data-name="<?= htmlspecialchars($supplier['name']) ?>"
                                            data-phone="<?= htmlspecialchars($supplier['phone'] ?? '') ?>"
                                            data-address="<?= htmlspecialchars($supplier['address'] ?? '') ?>"
                                            title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <a href="delete.php?id=<?= $supplier['id'] ?>" class="btn btn-light btn-sm text-danger rounded-circle" onclick="return confirm('Are you sure you want to delete this supplier?')" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No suppliers found. Click "Add New Supplier" to create one.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal: Add Supplier -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-user-plus text-primary me-2"></i> Add New Supplier
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="add.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" name="name" placeholder="e.g., ABC Trading Co." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone Number</label>
                        <input type="text" class="form-control rounded-3" name="phone" placeholder="e.g., 70123456">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Address</label>
                        <input type="text" class="form-control rounded-3" name="address" placeholder="e.g., Tripoli, Lebanon">
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit" class="btn btn-primary rounded-3 px-4">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Supplier -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-pen-to-square text-warning me-2"></i> Edit Supplier
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="edit.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="edit_supplier_id">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="edit_supplier_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone Number</label>
                        <input type="text" class="form-control rounded-3" id="edit_supplier_phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Address</label>
                        <input type="text" class="form-control rounded-3" id="edit_supplier_address" name="address">
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit" class="btn btn-warning text-white rounded-3 px-4">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('editSupplierModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('edit_supplier_id').value = button.getAttribute('data-id');
            document.getElementById('edit_supplier_name').value = button.getAttribute('data-name');
            document.getElementById('edit_supplier_phone').value = button.getAttribute('data-phone');
            document.getElementById('edit_supplier_address').value = button.getAttribute('data-address');
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>