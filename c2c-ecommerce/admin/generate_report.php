<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

$report_type = $_POST['report_type'] ?? '';
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';

// Set default dates if not provided
if (!$start_date) $start_date = date('Y-m-d', strtotime('-1 month'));
if (!$end_date) $end_date = date('Y-m-d');

// Validate dates
if ($start_date > $end_date) {
    die("Start date cannot be after end date.");
}

switch ($report_type) {
    case 'sales':
        // Sales report: products sold in the date range
        $stmt = $pdo->prepare("
            SELECT p.title, p.price, p.sold_at, u.username as buyer, s.username as seller
            FROM products p
            JOIN users u ON p.buyer_id = u.id
            JOIN users s ON p.user_id = s.id
            WHERE p.status = 'sold' AND p.sold_at BETWEEN ? AND ?
            ORDER BY p.sold_at DESC
        ");
        $stmt->execute([$start_date, $end_date]);
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 'users':
        // User activity: users who joined and their activity
        $stmt = $pdo->prepare("
            SELECT u.*, COUNT(p.id) as products_listed
            FROM users u
            LEFT JOIN products p ON u.id = p.user_id
            WHERE u.created_at BETWEEN ? AND ?
            GROUP BY u.id
            ORDER BY u.created_at DESC
        ");
        $stmt->execute([$start_date, $end_date]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;

    case 'products':
        // Products listed in the date range
        $stmt = $pdo->prepare("
            SELECT p.*, u.username as seller
            FROM products p
            JOIN users u ON p.user_id = u.id
            WHERE p.created_at BETWEEN ? AND ?
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$start_date, $end_date]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        break;

    default:
        die("Invalid report type.");
}

require_once '../includes/header.php';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <!-- Same navigation bar as dashboard.php -->
</nav>

<div class="container mt-4">
    <h2>Report: <?= ucfirst($report_type) ?></h2>
    <p>Date Range: <?= $start_date ?> to <?= $end_date ?></p>

    <?php if ($report_type === 'sales'): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Sold At</th>
                    <th>Buyer</th>
                    <th>Seller</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales as $sale): ?>
                <tr>
                    <td><?= htmlspecialchars($sale['title']) ?></td>
                    <td>R <?= number_format($sale['price'], 2) ?></td>
                    <td><?= $sale['sold_at'] ?></td>
                    <td><?= htmlspecialchars($sale['buyer']) ?></td>
                    <td><?= htmlspecialchars($sale['seller']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($report_type === 'users'): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Joined</th>
                    <th>Products Listed</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= $user['created_at'] ?></td>
                    <td><?= $user['products_listed'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($report_type === 'products'): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Price</th>
                    <th>Seller</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['title']) ?></td>
                    <td>R <?= number_format($product['price'], 2) ?></td>
                    <td><?= htmlspecialchars($product['seller']) ?></td>
                    <td><?= $product['status'] ?></td>
                    <td><?= $product['created_at'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <div class="mt-3">
        <a href="reports.php" class="btn btn-secondary">Back to Reports</a>
        <button class="btn btn-primary" onclick="window.print()">Print Report</button>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>