<?php
session_start();
require "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : null;
    $address = !empty($_POST['address']) ? trim($_POST['address']) : null;

    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO suppliers (name, phone, address) VALUES (?, ?, ?)");
        $stmt->execute([$name, $phone, $address]);

        $_SESSION['success'] = "Supplier added successfully!";
        header("Location: index.php");
        exit();
    }
}