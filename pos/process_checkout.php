<?php
session_start();
require "../config/db.php";

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!empty($input['cart'])) {
        
        // 1. حساب المجموع الكلي
        $total = 0;
        foreach ($input['cart'] as $item) {
            $total += $item['price'] * $item['qty'];
        }

        // 2. تجهيز بيانات الفاتورة
        $invoice_no = 'INV-' . time();
        $user_id = $_SESSION['user_id'] ?? 1; // معرف المستخدم الحالي أو 1 كافتراضي
        $payment_method = 'Cash';

        // 3. إدخال الفاتورة في جدول sales
        $stmt = $conn->prepare("
            INSERT INTO sales (invoice_no, user_id, subtotal, total, paid, payment_method, sale_datetime) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$invoice_no, $user_id, $total, $total, $total, $payment_method]);
        $sale_id = $conn->lastInsertId();

        // 4. خصم المخزون وإدخال عناصر الفاتورة
        foreach ($input['cart'] as $item) {
            // أ) خصم الكمية من جدول products
            $updateStock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $updateStock->execute([$item['qty'], $item['id']]);

            // ب) إدخال تفاصيل المنتج في جدول sale_items
            $insertItem = $conn->prepare("
                INSERT INTO sale_items (sale_id, product_id, qty, price) 
                VALUES (?, ?, ?, ?)
            ");
            $insertItem->execute([$sale_id, $item['id'], $item['qty'], $item['price']]);

            // ج) تسجيل حركة المخزون في جدول stock_movements
            $insertMovement = $conn->prepare("
                INSERT INTO stock_movements (product_id, type, qty, note, date) 
                VALUES (?, 'sale', ?, ?, NOW())
            ");
            $insertMovement->execute([$item['id'], $item['qty'], "Sale Invoice #$invoice_no"]);
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cart is empty!']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}