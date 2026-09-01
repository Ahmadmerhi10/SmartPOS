<?php 
session_start();
require "../config/db.php";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/navbar.php';

// 1. جلب التصنيفات من قاعدة البيانات
$stmt = $conn->prepare("
    SELECT c1.id, c1.name, c1.parent_id, c2.name AS parent_name 
    FROM categories c1 
    LEFT JOIN categories c2 ON c1.parent_id = c2.id 
    ORDER BY c1.id 
");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. جلب التصنيفات الرئيسية لاستخدامها في القائمة المنسدلة
$parent_stmt = $conn->prepare("SELECT id, name FROM categories WHERE parent_id IS NULL");
$parent_stmt->execute();
$parent_categories = $parent_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid px-4 py-4">

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold m-0 text-dark">
                <i class="fa-solid fa-layer-group text-primary me-2"></i> Categories Management
            </h4>
            <small class="text-muted">Manage product categories</small>
        </div>
        <button type="button" class="btn btn-primary rounded-3 px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fa-solid fa-plus me-1"></i> Add New Category
        </button>
    </div>

    <!-- Alert Notifications -->
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Categories Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body px-0 py-2">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#ID</th>
                            <th>Category Name</th>
                            <th>Parent Category</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($categories)): ?>
                            <?php foreach($categories as $cat): ?>
                            <tr>
                                <td class="ps-4 fw-semibold text-muted">#<?= $cat['id'] ?></td>
                                <td><span class="fw-bold text-dark"><?= htmlspecialchars($cat['name']) ?></span></td>
                                <td>
                                    <?php if ($cat['parent_name']): ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3">
                                            <i class="fa-solid fa-turn-up me-1"></i> <?= htmlspecialchars($cat['parent_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3">
                                            Main Category
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <!-- Edit Button -->
                                    <button type="button" class="btn btn-light btn-sm text-primary rounded-circle me-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editCategoryModal"
                                            data-id="<?= $cat['id'] ?>"
                                            data-name="<?= htmlspecialchars($cat['name']) ?>"
                                            data-parent="<?= $cat['parent_id'] ?>"
                                            title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    
                                    <!-- Delete Button -->
                                    <a href="delete.php?id=<?= $cat['id'] ?>" 
                                       class="btn btn-light btn-sm text-danger rounded-circle" 
                                       onclick="return confirm('Are you sure you want to delete this category?')" 
                                       title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No categories found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal: Add Category -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-folder-plus text-primary me-2"></i> Add New Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <form action="add.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" name="name" placeholder="e.g., Cold Drinks" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Parent Category (Optional)</label>
                        <select class="form-select rounded-3" name="parent_id">
                            <option value="">-- Main Category (No Parent) --</option>
                            <?php foreach($parent_categories as $parent): ?>
                                <option value="<?= $parent['id'] ?>"><?= htmlspecialchars($parent['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit" class="btn btn-primary rounded-3 px-4">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- Modal: Edit Category -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-pen-to-square text-warning me-2"></i> Edit Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <form action="edit.php" method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="edit_category_id" value="">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="edit_category_name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Parent Category (Optional)</label>
                        <select class="form-select rounded-3" id="edit_parent_id" name="parent_id">
                            <option value="">-- Main Category (No Parent) --</option>
                            <?php foreach($parent_categories as $parent): ?>
                                <option value="<?= $parent['id'] ?>"><?= htmlspecialchars($parent['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit" class="btn btn-warning text-white rounded-3 px-4">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// JS لتغذية بيانات التعديل داخل الـ Modal تلقائياً
document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('editCategoryModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('edit_category_id').value = button.getAttribute('data-id');
            document.getElementById('edit_category_name').value = button.getAttribute('data-name');
            document.getElementById('edit_parent_id').value = button.getAttribute('data-parent') || '';
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>