<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

$product_id = $_GET['id'] ?? 0;

if ($product_id) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$product_id])) {
        $_SESSION['message'] = 'Product deleted successfully.';
    } else {
        $_SESSION['error'] = 'Failed to delete product.';
    }
}

header('Location: products.php');
exit();