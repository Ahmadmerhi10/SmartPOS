<?php
session_start();
require "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id']; // تأكد أن name="id" في input الـ Modal المخفي
    $name = trim($_POST['name']);
    $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;

    if (!empty($name) && !empty($id)) {
        $stmt = $conn->prepare("UPDATE categories SET name = ?, parent_id = ? WHERE id = ?");
        $stmt->execute([$name, $parent_id, $id]);

        $_SESSION['success'] = "Category edited successfully!";
        header("Location: index.php");
        exit();
    }
}