<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
redirectIfNotAdmin();

$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

$stmt = $pdo->query("SELECT COUNT(*) as total_products FROM products WHERE status = 'available'");
$total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'];

$stmt = $pdo->query("SELECT COUNT(*) as total_categories FROM categories");
$total_categories = $stmt->fetch(PDO::FETCH_ASSOC)['total_categories'];

require_once '../includes/header.php';
?>

<div class="container mt-4">
    <h2 class="mb-4">Admin Dashboard</h2>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="card-text display-4"><?php echo $total_users; ?></p>
                    <a href="users.php" class="btn btn-primary">Manage Users</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Products</h5>
                    <p class="card-text display-4"><?php echo $total_products; ?></p>
                    <a href="products.php" class="btn btn-primary">Manage Products</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Categories</h5>
                    <p class="card-text display-4"><?php echo $total_categories; ?></p>
                    <a href="categories.php" class="btn btn-primary">Manage Categories</a>
                </div>
            </div>
        </div>
    </div>
    <div class="card mt-4">
        <div class="card-header">
            <h3>Quick Actions</h3>
        </div>
        <div class="card-body">
            <a href="reports.php" class="btn btn-info me-2">Generate Reports</a>
            <a href="<?php echo BASE_URL; ?>products/create.php" class="btn btn-success">Add New Product</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
