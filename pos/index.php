<?php 
session_start();
require "../config/db.php";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/navbar.php';

// جلب جميع المنتجات لعرضها في الـ POS
$products = $conn->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-3">
    <div class="row g-3">
        <!-- قسم المنتجات (يسار) -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 p-3 mb-3">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-boxes-stacked text-primary me-2"></i> Products</h5>
                <div class="row g-2">
                    <?php if(!empty($products)): ?>
                        <?php foreach($products as $product): ?>
                            <div class="col-md-3 col-6">
                                <div class="card h-100 border text-center p-2 rounded-3 shadow-sm product-card" 
                                     style="cursor: pointer;" 
                                     onclick="addToCart(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>', <?= $product['price'] ?>)">
                                    <div class="fw-bold fs-6 text-truncate"><?= htmlspecialchars($product['name']) ?></div>
                                    <div class="text-success fw-bold me-1">$<?= number_format($product['price'], 2) ?></div>
                                    <small class="text-muted">Stock: <?= $product['stock'] ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted">No products available.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- قسم السلة والعملية (يمين) -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-cart-shopping text-success me-2"></i> Current Order</h5>
                
                <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="cart-table">
                            <!-- أسطر السلة تضاف هنا عبر JS -->
                        </tbody>
                    </table>
                </div>

                <hr class="my-2">

                <!-- مدخلات الخصم والضريبة -->
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label small fw-bold mb-1">Discount ($)</label>
                        <input type="number" id="discount-input" class="form-control form-control-sm rounded-3" value="0" min="0" step="0.01" oninput="updateCartUI()">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold mb-1">Tax (%)</label>
                        <input type="number" id="tax-input" class="form-control form-control-sm rounded-3" value="0" min="0" step="0.01" oninput="updateCartUI()">
                    </div>
                </div>

                <!-- ملخص المبالغ -->
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>Subtotal:</span>
                    <span id="subtotal-val" class="fw-bold">$0.00</span>
                </div>
                <div class="d-flex justify-content-between small text-danger mb-1">
                    <span>Discount:</span>
                    <span id="discount-val" class="fw-bold">-$0.00</span>
                </div>
                <div class="d-flex justify-content-between small text-primary mb-2">
                    <span>Tax:</span>
                    <span id="tax-val" class="fw-bold">+$0.00</span>
                </div>

                <hr class="my-1">

                <div class="d-flex justify-content-between fs-5 fw-bold my-2">
                    <span>Grand Total:</span>
                    <span id="grand-total" class="text-success">$0.00</span>
                </div>

                <button class="btn btn-success w-100 fw-bold py-2 rounded-3 mt-2" onclick="checkout()">
                    <i class="fa-solid fa-money-bill-wave me-1"></i> Pay Now
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];

function addToCart(id, name, price) {
    let existingIndex = cart.findIndex(item => item.id === id);
    if (existingIndex > -1) {
        cart[existingIndex].qty += 1;
    } else {
        cart.push({ id: id, name: name, price: price, qty: 1 });
    }
    updateCartUI();
}

function changeQty(index, change) {
    cart[index].qty += change;
    if (cart[index].qty <= 0) {
        cart.splice(index, 1);
    }
    updateCartUI();
}

function removeItem(index) {
    cart.splice(index, 1);
    updateCartUI();
}

function updateCartUI() {
    let tbody = document.getElementById('cart-table');
    tbody.innerHTML = '';
    let subtotal = 0;

    if (cart.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Cart is empty</td></tr>';
    } else {
        cart.forEach((item, index) => {
            let itemTotal = item.price * item.qty;
            subtotal += itemTotal;

            tbody.innerHTML += `
                <tr>
                    <td class="small fw-semibold">${item.name}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-light border py-0 px-1" onclick="changeQty(${index}, -1)">-</button>
                        <span class="mx-1 small">${item.qty}</span>
                        <button class="btn btn-sm btn-light border py-0 px-1" onclick="changeQty(${index}, 1)">+</button>
                    </td>
                    <td class="text-end small fw-bold">$${itemTotal.toFixed(2)}</td>
                    <td class="text-center"><button class="btn btn-sm text-danger py-0 px-1" onclick="removeItem(${index})">&times;</button></td>
                </tr>
            `;
        });
    }

    let discount = parseFloat(document.getElementById('discount-input').value) || 0;
    let taxPercent = parseFloat(document.getElementById('tax-input').value) || 0;

    let afterDiscount = Math.max(0, subtotal - discount);
    let taxAmount = (afterDiscount * taxPercent) / 100;
    let grandTotal = afterDiscount + taxAmount;

    document.getElementById('subtotal-val').innerText = '$' + subtotal.toFixed(2);
    document.getElementById('discount-val').innerText = '-$' + discount.toFixed(2);
    document.getElementById('tax-val').innerText = '+$' + taxAmount.toFixed(2);
    document.getElementById('grand-total').innerText = '$' + grandTotal.toFixed(2);
}

function checkout() {
    if (cart.length === 0) return alert("Cart is empty!");

    let discount = parseFloat(document.getElementById('discount-input').value) || 0;
    let taxPercent = parseFloat(document.getElementById('tax-input').value) || 0;

    fetch('process_checkout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            cart: cart,
            discount: discount,
            tax_percent: taxPercent
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Order completed successfully!");
            
            
            
            cart = [];
            updateCartUI();
            location.reload();
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Something went wrong!");
    });
}

// تهيئة السلة عند بداية التشغيل
updateCartUI();
</script>

<?php include '../includes/footer.php'; ?>