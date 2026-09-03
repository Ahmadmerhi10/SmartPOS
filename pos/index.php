<?php 
session_start();
require "../config/db.php";
include '../includes/header.php';
include '../includes/sidebar.php';
include '../includes/navbar.php';

// 1. جلب التصنيفات
$categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// 2. جلب المنتجات
$products = $conn->query("SELECT * FROM products")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid py-3">
    <div class="row">

        <!-- قسم المنتجات والتصنيفات (اليسار) -->
        <div class="col-md-7">
            
            <!-- خانة البحث -->
            <input type="text" id="search" class="form-control mb-3" placeholder="Search product name..." onkeyup="filterProducts()">

            <!-- أزرار التصنيفات -->
            <div class="mb-3">
                <button class="btn btn-primary btn-sm" onclick="filterCategory('all')">All</button>
                <?php foreach($categories as $cat): ?>
                    <button class="btn btn-outline-secondary btn-sm" onclick="filterCategory('<?= $cat['id'] ?>')">
                        <?= $cat['name'] ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- شبكة المنتجات -->
            <div class="row g-2">
                <?php foreach($products as $p): ?>
                    <div class="col-4 product-card" data-category="<?= $p['category_id'] ?>" data-name="<?= strtolower($p['name']) ?>">
                        <div class="card p-2 text-center" style="cursor:pointer;" onclick="addToCart(<?= $p['id'] ?>, '<?= $p['name'] ?>', <?= $p['price'] ?>)">
                            <h6><?= $p['name'] ?></h6>
                            <span class="text-primary fw-bold">$<?= $p['price'] ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>

        <!-- قسم السلة والحساب (اليمين) -->
        <div class="col-md-5">
            <div class="card p-3">
                <h5>Current Order</h5>
                
                <!-- جدول السلة -->
                <table class="table table-sm align-middle mt-2">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="cart-table">
                        <!-- المنتجات ستظهر هنا برمجياً -->
                    </tbody>
                </table>

                <hr>
                
                <!-- المجموع الإجمالي -->
                <div class="d-flex justify-content-between fs-5 fw-bold mb-3">
                    <span>Total:</span>
                    <span id="grand-total">$0.00</span>
                </div>

                <button class="btn btn-success w-100 fw-bold" onclick="checkout()">Pay Now</button>
            </div>
        </div>

    </div>
</div>

<script>
let cart = [];

// 1. إضافة منتج للسلة
function addToCart(id, name, price) {
    let item = cart.find(i => i.id === id);
    if (item) {
        item.qty++;
    } else {
        cart.push({ id: id, name: name, price: price, qty: 1 });
    }
    updateCartUI();
}

// 2. تحديث شكل السلة والمجموع
function updateCartUI() {
    let tbody = document.getElementById('cart-table');
    tbody.innerHTML = '';
    let total = 0;

    cart.forEach((item, index) => {
        let itemTotal = item.price * item.qty;
        total += itemTotal;

        tbody.innerHTML += `
            <tr>
                <td>${item.name}</td>
                <td>
                    <button class="btn btn-sm btn-light py-0" onclick="changeQty(${index}, -1)">-</button>
                    ${item.qty}
                    <button class="btn btn-sm btn-light py-0" onclick="changeQty(${index}, 1)">+</button>
                </td>
                <td>$${itemTotal.toFixed(2)}</td>
                <td><button class="btn btn-sm text-danger" onclick="removeItem(${index})">&times;</button></td>
            </tr>
        `;
    });

    document.getElementById('grand-total').innerText = '$' + total.toFixed(2);
}

// 3. تغيير الكمية أو الحذف
function changeQty(index, change) {
    cart[index].qty += change;
    if (cart[index].qty <= 0) cart.splice(index, 1);
    updateCartUI();
}

function removeItem(index) {
    cart.splice(index, 1);
    updateCartUI();
}

// 4. البحث المباشر
function filterProducts() {
    let val = document.getElementById('search').value.toLowerCase();
    document.querySelectorAll('.product-card').forEach(card => {
        let name = card.getAttribute('data-name');
        card.style.display = name.includes(val) ? 'block' : 'none';
    });
}

// 5. الفلترة بالتصنيف
function filterCategory(catId) {
    document.querySelectorAll('.product-card').forEach(card => {
        let cat = card.getAttribute('data-category');
        card.style.display = (catId === 'all' || cat === catId) ? 'block' : 'none';
    });
}

// 6. الدفع (مبدئي)
function checkout() {
    if (cart.length === 0) return alert("Cart is empty!");

    // إرسال بيانات السلة إلى ملف process_checkout.php
    fetch('process_checkout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cart: cart })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Order completed & Stock updated successfully!");
            cart = []; // تفريغ السلة
            updateCartUI();
            location.reload(); // إعادة تحميل الصفحة لتحديث كميات المخزون المعروضة
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Something went wrong!");
    });
}
</script>

<?php include '../includes/footer.php'; ?>