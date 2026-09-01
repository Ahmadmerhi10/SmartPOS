<?php
session_start();
require "../config/db.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM suppliers WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['success'] = "Supplier deleted successfully!";
    header("Location: index.php");
    exit();
}