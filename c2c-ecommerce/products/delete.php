<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . 'products/');
    exit();
}

$product_id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT image FROM products WHERE id = ? AND user_id = ?");
$stmt->execute([$product_id, $_SESSION['user_id']]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: ' . BASE_URL . 'products/');
    exit();
}

$stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
if ($stmt->execute([$product_id])) {
    // Delete image file if it exists
    if ($product['image'] && file_exists('../assets/images/' . $product['image'])) {
        unlink('../assets/images/' . $product['image']);
    }
    
    $_SESSION['success_message'] = 'Product deleted successfully!';
} else {
    $_SESSION['error_message'] = 'Failed to delete product. Please try again.';
}

header('Location: ' . BASE_URL . 'products/');
exit();
?>
