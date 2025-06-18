<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
redirectIfNotAdmin();

$errors = [];
$success_message = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$product_id])) {
        $success_message = 'Product deleted successfully';
    } else {
        $errors['general'] = 'Failed to delete product';
    }
}

$stmt = $pdo->query("
    SELECT p.id, p.title, p.price, p.quantity, p.status, c.name AS category_name, u.username
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN users u ON p.user_id = u.id
    ORDER BY p.title
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="container mt-4">
    <h2 class="mb-4">Manage Products</h2>
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
    <?php endif; ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Owner</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['title']); ?></td>
                        <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                        <td>R<?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($product['status'])); ?></td>
                        <td><?php echo htmlspecialchars($product['username'] ?? 'N/A'); ?></td>
                        <td>
                            <a href="products.php?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger confirm-before-delete">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
