<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Fetch featured products
$stmt = $pdo->prepare("SELECT p.*, u.username, c.name as category_name 
                       FROM products p 
                       JOIN users u ON p.user_id = u.id 
                       JOIN categories c ON p.category_id = c.id 
                       WHERE p.status = 'available' 
                       ORDER BY p.created_at DESC 
                       LIMIT 8");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories
$stmt = $pdo->query("SELECT * FROM categories LIMIT 5");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<div class="hero-section bg-light py-5 mb-4">
    <div class="container text-center">
        <h1 class="display-4">Welcome to the South African Market</h1>
        <h2> Buy and sell locally </h2>
        <p class="lead">Find great deals or sell items in your community</p>
        <a href="<?php echo BASE_URL; ?>products/" class="btn btn-primary btn-lg mt-3">Browse Products</a>
        <?php if (!isLoggedIn()): ?>
            <a href="<?php echo BASE_URL; ?>users/register.php" class="btn btn-outline-primary btn-lg mt-3">Sign Up to Sell</a>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <h2 class="mb-4">Featured Products</h2>
    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    <img src="<?php echo BASE_URL . 'assets/images/' . ($product['image'] ?: 'placeholder.png'); ?>" class="card-img-top product-image" alt="<?php echo htmlspecialchars($product['title']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($product['category_name']); ?></p>
                        <p class="card-text">R <?php echo number_format($product['price'], 2); ?></p>
                    </div>
                    <div class="card-footer bg-white">
                        <a href="<?php echo BASE_URL; ?>products/view.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h2 class="mb-4">Popular Categories</h2>
    <div class="row">
        <?php foreach ($categories as $category): ?>
            <div class="col-md-2 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?php echo htmlspecialchars($category['name']); ?></h5>
                        <a href="<?php echo BASE_URL; ?>products/?category=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline-primary">Browse</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>