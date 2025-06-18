<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
redirectIfNotAdmin();

// Fetch report data
$stmt = $pdo->query("SELECT c.name, COUNT(p.id) as product_count 
                     FROM categories c 
                     LEFT JOIN products p ON c.id = p.category_id 
                     GROUP BY c.id 
                     ORDER BY product_count DESC");
$category_report = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT u.username, COUNT(p.id) as product_count 
                     FROM users u 
                     LEFT JOIN products p ON u.id = p.user_id 
                     GROUP BY u.id 
                     ORDER BY product_count DESC 
                     LIMIT 10");
$user_report = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT p.title, p.quantity, c.name AS category_name, u.username 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     LEFT JOIN users u ON p.user_id = u.id 
                     ORDER BY p.quantity DESC");
$quantity_report = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="container mt-4">
    <h2 class="mb-4">Reports</h2>
    <div class="card mb-4">
        <div class="card-header">
            <h3>Products by Category</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Product Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($category_report as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo $row['product_count']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <h3>Top 10 Users by Product Listings</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Product Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_report as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo $row['product_count']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3>Products by Quantity</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Quantity</th>
                            <th>Category</th>
                            <th>Owner</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quantity_report as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($row['category_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['username'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>