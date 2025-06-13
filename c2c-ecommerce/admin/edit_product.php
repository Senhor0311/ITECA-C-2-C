<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

$product_id = $_GET['id'] ?? 0;
$message = '';

// Fetch product data
if ($product_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $status = trim($_POST['status']);

    // Validate
    if (empty($title) || empty($description) || empty($price) || empty($status)) {
        $message = '<div class="alert alert-danger">All fields are required.</div>';
    } else {
        // Update product
        $stmt = $pdo->prepare("UPDATE products SET title = ?, description = ?, price = ?, status = ? WHERE id = ?");
        if ($stmt->execute([$title, $description, $price, $status, $product_id])) {
            $message = '<div class="alert alert-success">Product updated successfully.</div>';
            // Refetch updated product
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $message = '<div class="alert alert-danger">Failed to update product.</div>';
        }
    }
}

require_once '../includes/header.php';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <!-- Same navigation bar as dashboard.php -->
</nav>

<div class="container mt-4">
    <h2>Edit Product</h2>
    <?= $message ?>
    <form method="POST">
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($product['title'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" required><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Price (R)</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($product['price'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control" required>
                <option value="available" <?= ($product['status'] ?? '') == 'available' ? 'selected' : '' ?>>Available</option>
                <option value="sold" <?= ($product['status'] ?? '') == 'sold' ? 'selected' : '' ?>>Sold</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update Product</button>
        <a href="products.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>