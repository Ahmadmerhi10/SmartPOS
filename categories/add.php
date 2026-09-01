<?php
session_start();
require "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    
    // إذا لم يتم اختيار أب، نجعله NULL، وإلا نأخذ الـ ID المرسل
    $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;

    if (!empty($name)) {
        // إدخال البيانات مباشرة باستخدام الـ ID
        $stmt = $conn->prepare("INSERT INTO categories (name, parent_id) VALUES (?, ?)");
        $stmt->execute([$name, $parent_id]);

        $_SESSION['success'] = "Category added successfully!";
        header("Location: index.php");
        exit();
    }
}