<?php 
session_start();
require "../config/db.php";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/navbar.php';

// جلب المنتجات مع أسماء الفئات والموردين
$stmt = $conn->prepare("
    SELECT p.*, c.name AS category_name, s.name AS supplier_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN suppliers s ON p.supplier_id = s.id 
    ORDER BY p.id DESC
");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// جلب الفئات والموردين للاستخدام في القوائم المنسدلة داخل المودال
$categories = $conn->query("SELECT id, name FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$suppliers = $conn->query("SELECT id, name FROM suppliers")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid px-4 py-4">

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold m-0 text-dark">
                <i class="fa-solid fa-boxes-stacked text-primary me-2"></i> Products Management
            </h4>
            <small class="text-muted">Manage inventory, prices, and stock alerts</small>
        </div>
        <button type="button" class="btn btn-primary rounded-3 px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="fa-solid fa-plus me-1"></i> Add New Product
        </button>
    </div>

    <!-- Alert Notifications -->
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Products Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body px-0 py-2">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Product</th>
                            <th>Barcode/SKU</th>
                            <th>Category</th>
                            <th>Cost / Price</th>
                            <th>Stock</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)): ?>
                            <?php foreach($products as $prod): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <?php if($prod['image']): ?>
                                            <img src="../uploads/products/<?= $prod['image'] ?>" class="rounded-3 me-3" width="40" height="40" style="object-fit:cover;">
                                        <?php else: ?>
                                            <div class="bg-light text-muted rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                                                <i class="fa-solid fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($prod['name']) ?></div>
                                            <small class="text-muted">Supplier: <?= htmlspecialchars($prod['supplier_name'] ?? 'N/A') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fs-7 fw-semibold"><?= htmlspecialchars($prod['barcode'] ?? '-') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($prod['sku'] ?? '-') ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">
                                        <?= htmlspecialchars($prod['category_name'] ?? 'Uncategorized') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-success">$<?= number_format($prod['price'], 2) ?></div>
                                    <small class="text-muted fs-7">Cost: $<?= number_format($prod['cost'], 2) ?></small>
                                </td>
                                <td>
                                    <?php if($prod['stock'] <= $prod['low_stock_alert']): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-2">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $prod['stock'] ?> <?= $prod['unit'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-2">
                                            <?= $prod['stock'] ?> <?= $prod['unit'] ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <button type="button" class="btn btn-light btn-sm text-primary rounded-circle me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editProductModal"
                                            data-id="<?= $prod['id'] ?>"
                                            data-name="<?= htmlspecialchars($prod['name']) ?>"
                                            data-barcode="<?= htmlspecialchars($prod['barcode'] ?? '') ?>"
                                            data-sku="<?= htmlspecialchars($prod['sku'] ?? '') ?>"
                                            data-category_id="<?= $prod['category_id'] ?>"
                                            data-supplier_id="<?= $prod['supplier_id'] ?>"
                                            data-cost="<?= $prod['cost'] ?>"
                                            data-price="<?= $prod['price'] ?>"
                                            data-stock="<?= $prod['stock'] ?>"
                                            data-unit="<?= htmlspecialchars($prod['unit']) ?>"
                                            data-alert="<?= $prod['low_stock_alert'] ?>"
                                            title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <a href="delete.php?id=<?= $prod['id'] ?>" class="btn btn-light btn-sm text-danger rounded-circle" onclick="return confirm('Delete this product?')" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No products found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal: Add Product -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-box-open text-primary me-2"></i> Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="add.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Barcode</label>
                            <input type="text" name="barcode" class="form-control rounded-3">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">SKU</label>
                            <input type="text" name="sku" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category_id" class="form-select rounded-3">
                                <option value="">-- Select Category --</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Supplier</label>
                            <select name="supplier_id" class="form-select rounded-3">
                                <option value="">-- Select Supplier --</option>
                                <?php foreach($suppliers as $sup): ?>
                                    <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Cost Price ($)</label>
                            <input type="number" step="0.01" name="cost" class="form-control rounded-3" value="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Selling Price ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Unit</label>
                            <input type="text" name="unit" class="form-control rounded-3" value="pcs">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Stock Quantity</label>
                            <input type="number" name="stock" class="form-control rounded-3" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Low Stock Alert</label>
                            <input type="number" name="low_stock_alert" class="form-control rounded-3" value="5">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Product Image</label>
                            <input type="file" name="image" class="form-control rounded-3" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Product -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-pen-to-square text-warning me-2"></i> Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="edit.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="edit_prod_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_prod_name" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Barcode</label>
                            <input type="text" name="barcode" id="edit_prod_barcode" class="form-control rounded-3">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">SKU</label>
                            <input type="text" name="sku" id="edit_prod_sku" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category_id" id="edit_prod_category" class="form-select rounded-3">
                                <option value="">-- Select Category --</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Supplier</label>
                            <select name="supplier_id" id="edit_prod_supplier" class="form-select rounded-3">
                                <option value="">-- Select Supplier --</option>
                                <?php foreach($suppliers as $sup): ?>
                                    <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Cost Price ($)</label>
                            <input type="number" step="0.01" name="cost" id="edit_prod_cost" class="form-control rounded-3">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Selling Price ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price" id="edit_prod_price" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Unit</label>
                            <input type="text" name="unit" id="edit_prod_unit" class="form-control rounded-3">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Stock Quantity</label>
                            <input type="number" name="stock" id="edit_prod_stock" class="form-control rounded-3">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Low Stock Alert</label>
                            <input type="number" name="low_stock_alert" id="edit_prod_alert" class="form-control rounded-3">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Change Image</label>
                            <input type="file" name="image" class="form-control rounded-3" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-white rounded-3 px-4">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('editProductModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('edit_prod_id').value = button.getAttribute('data-id');
            document.getElementById('edit_prod_name').value = button.getAttribute('data-name');
            document.getElementById('edit_prod_barcode').value = button.getAttribute('data-barcode');
            document.getElementById('edit_prod_sku').value = button.getAttribute('data-sku');
            document.getElementById('edit_prod_category').value = button.getAttribute('data-category_id') || '';
            document.getElementById('edit_prod_supplier').value = button.getAttribute('data-supplier_id') || '';
            document.getElementById('edit_prod_cost').value = button.getAttribute('data-cost');
            document.getElementById('edit_prod_price').value = button.getAttribute('data-price');
            document.getElementById('edit_prod_unit').value = button.getAttribute('data-unit');
            document.getElementById('edit_prod_stock').value = button.getAttribute('data-stock');
            document.getElementById('edit_prod_alert').value = button.getAttribute('data-alert');
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>