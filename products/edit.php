<?php
session_start();
require "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $barcode = !empty($_POST['barcode']) ? trim($_POST['barcode']) : null;
    $sku = !empty($_POST['sku']) ? trim($_POST['sku']) : null;
    $category_id = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
    $supplier_id = !empty($_POST['supplier_id']) ? $_POST['supplier_id'] : null;
    $cost = $_POST['cost'] ?? 0.00;
    $price = $_POST['price'];
    $stock = $_POST['stock'] ?? 0;
    $unit = $_POST['unit'] ?? 'pcs';
    $low_stock_alert = $_POST['low_stock_alert'] ?? 5;

    if (!empty($id) && !empty($name)) {
        // فحص رفع صورة جديدة
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = time() . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/products/" . $image_name);

            $stmt = $conn->prepare("UPDATE products SET name=?, barcode=?, sku=?, category_id=?, supplier_id=?, cost=?, price=?, stock=?, unit=?, image=?, low_stock_alert=? WHERE id=?");
            $stmt->execute([$name, $barcode, $sku, $category_id, $supplier_id, $cost, $price, $stock, $unit, $image_name, $low_stock_alert, $id]);
        } else {
            $stmt = $conn->prepare("UPDATE products SET name=?, barcode=?, sku=?, category_id=?, supplier_id=?, cost=?, price=?, stock=?, unit=?, low_stock_alert=? WHERE id=?");
            $stmt->execute([$name, $barcode, $sku, $category_id, $supplier_id, $cost, $price, $stock, $unit, $low_stock_alert, $id]);
        }

        $_SESSION['success'] = "Product updated successfully!";
        header("Location: index.php");
        exit();
    }
}