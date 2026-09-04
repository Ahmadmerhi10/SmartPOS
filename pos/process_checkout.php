<?php
session_start();
require "../config/db.php";

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!empty($input['cart'])) {
        
        $cart = $input['cart'];
        $discount = floatval($input['discount'] ?? 0);
        $tax_percent = floatval($input['tax_percent'] ?? 0);

        // 1. حساب Subtotal المجموع المبدئي
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['qty'];
        }

        // 2. حساب قيمة الضريبة والمجموع النهائي Total
        $after_discount = max(0, $subtotal - $discount);
        $tax_amount = ($after_discount * $tax_percent) / 100;
        $total = $after_discount + $tax_amount;

        $invoice_no = 'INV-' . time();
        $user_id = $_SESSION['user_id'] ?? 1;

        // 3. حفظ بيانات الفاتورة كاملة في جدول sales
        $stmt = $conn->prepare("
            INSERT INTO sales (invoice_no, user_id, subtotal, discount, tax, total, paid, payment_method, sale_datetime) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Cash', NOW())
        ");
        $stmt->execute([$invoice_no, $user_id, $subtotal, $discount, $tax_amount, $total, $total]);
        $sale_id = $conn->lastInsertId();

        // 4. خصم الكميات وتسجيل المنتجات وحركة المخزون
        foreach ($cart as $item) {
            // أ) تنقيص كمية المنتج من جدول products
            $updateStock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $updateStock->execute([$item['qty'], $item['id']]);

            // ب) إضافة العنصر لـ sale_items
            $insertItem = $conn->prepare("
                INSERT INTO sale_items (sale_id, product_id, qty, price) 
                VALUES (?, ?, ?, ?)
            ");
            $insertItem->execute([$sale_id, $item['id'], $item['qty'], $item['price']]);

            // ج) تسجيل حركة المخزون في stock_movements
            $insertMovement = $conn->prepare("
                INSERT INTO stock_movements (product_id, type, qty, note, date) 
                VALUES (?, 'sale', ?, ?, NOW())
            ");
            $insertMovement->execute([$item['id'], $item['qty'], "Sale Invoice #$invoice_no"]);
        }

        echo json_encode(['success' => true, 'sale_id' => $sale_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cart is empty!']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}