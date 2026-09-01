<?php
session_start();
require "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $phone = !empty($_POST['phone']) ? trim($_POST['phone']) : null;
    $address = !empty($_POST['address']) ? trim($_POST['address']) : null;

    if (!empty($id) && !empty($name)) {
        $stmt = $conn->prepare("UPDATE suppliers SET name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $address, $id]);

        $_SESSION['success'] = "Supplier updated successfully!";
        header("Location: index.php");
        exit();
    }
}