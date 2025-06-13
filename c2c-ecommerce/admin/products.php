<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

// Fetch all products with seller username
$stmt = $pdo->query("
    SELECT p.*, u.username as seller_username 
    FROM products p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <!-- Same navigation bar as dashboard.php -->
</nav>

<div class="container mt-4">
    <h2>Manage Products</h2>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Price</th>
                    <th>Seller</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['id']) ?></td>
                    <td><?= htmlspecialchars($product['title']) ?></td>
                    <td>R <?= number_format($product['price'], 2) ?></td>
                    <td><?= htmlspecialchars($product['seller_username']) ?></td>
                    <td><?= htmlspecialchars($product['status']) ?></td>
                    <td><?= date('M j, Y', strtotime($product['created_at'])) ?></td>
                    <td>
                        <a href="../product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-info">View</a>
                        <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="delete_product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>