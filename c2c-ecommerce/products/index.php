<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "SELECT p.*, u.username, c.name as category_name 
          FROM products p 
          JOIN users u ON p.user_id = u.id 
          JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'available'";

$params = [];

if ($category_id) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_id;
}

if ($search_query) {
    $query .= " AND (p.title LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                        <a href="<?php echo BASE_URL; ?>products/" class="btn btn-outline-secondary w-100 mt-2">Reset</a>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Available Products</h2>
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo BASE_URL; ?>products/create.php" class="btn btn-primary">Sell an Item</a>
                <?php endif; ?>
            </div>
            
            <?php if (empty($products)): ?>
                <div class="alert alert-info">No products found matching your criteria.</div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <img src="<?php echo BASE_URL . 'assets/images/' . ($product['image'] ?: 'placeholder.png'); ?>" class="card-img-top product-image" alt="<?php echo htmlspecialchars($product['title']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                                    <p class="card-text text-muted"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                    <p class="card-text">R <?php echo number_format($product['price'], 2); ?></p>
                                    <p class="card-text"><small class="text-muted">Seller: <?php echo htmlspecialchars($product['username']); ?></small></p>
                                </div>
                                <div class="card-footer bg-white">
                                    <a href="<?php echo BASE_URL; ?>products/view.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
