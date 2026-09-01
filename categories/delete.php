<?php
session_start();
require "../config/db.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['success'] = "Category deleted successfully!";
    header("Location: index.php");
    exit();
}