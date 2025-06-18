<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$product_id = $_GET['id'] ?? 0;

$product = [];
$seller = [];
$category = '';
$related_products = [];

if ($product_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, u.username AS seller_name, u.email AS seller_email,
            c.name AS category_name
            FROM products p
            JOIN users u ON p.user_id = u.id
            JOIN categories c ON p.category_id = c.id
            WHERE p.id = ?
        ");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            $stmt = $pdo->prepare("
                SELECT p.id, p.title, p.price, p.image
                FROM products p
                WHERE p.category_id = ? AND p.id != ? AND p.status = 'available'
                ORDER BY RAND() LIMIT 4
            ");
            $stmt->execute([$product['category_id'], $product_id]);
            $related_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    redirectIfNotLoggedIn();

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $product_id = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    $_SESSION['success_message'] = 'Product added to cart!';
    header("Location: view.php?id=$product_id");
    exit();
}

$page_title = $product['title'] ?? 'Product Details';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <?php if (empty($product)): ?>
        <div class="alert alert-danger">
            Product not found. <a href="../products/">Browse all products</a>
        </div>
    <?php else: ?>
        
        <div class="row">
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?= BASE_URL . 'assets/images/' . htmlspecialchars($product['image']) ?>" 
                             class="card-img-top product-detail-image"
                             alt="<?= htmlspecialchars($product['title']) ?>">
                    <?php else: ?>
                        <div class="text-center p-5 bg-light">
                            <img src="<?= BASE_URL ?>assets/images/placeholder.png" 
                                 class="img-fluid"
                                 alt="No image available">
                            <p class="mt-3">No image available</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h3 text-success">R <?= number_format($product['price'], 2) ?></span>
                            <span class="badge bg-<?= $product['status'] === 'available' ? 'success' : 'danger' ?>">
                                <?= ucfirst($product['status']) ?>
                            </span>
                        </div>
                        
                        <div class="mt-4">
                            <?php if ($product['status'] === 'available'): ?>
                                <form method="POST" class="d-flex align-items-center">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    
                                    <div class="me-2" style="width: 100px;">
                                        <label for="quantity" class="form-label">Quantity</label>
                                        <input type="number" class="form-control" id="quantity" 
                                               name="quantity" value="1" min="1">
                                    </div>
                                    
                                    <button type="submit" name="add_to_cart" class="btn btn-primary w-100">
                                        <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100" disabled>
                                    Not Available
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
       
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h1 class="card-title"><?= htmlspecialchars($product['title']) ?></h1>
                        
                        <div class="d-flex align-items-center mb-3">
                            <span class="me-3">Category:</span>
                            <span class="badge bg-info"><?= htmlspecialchars($product['category_name']) ?></span>
                        </div>
                        
                        <div class="mb-4">
                            <h4>Description</h4>
                            <p class="card-text"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                        </div>
                        
                        <div class="border-top pt-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-user-circle fa-2x text-secondary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5>Seller Information</h5>
                                    <p class="mb-1">
                                        <strong><?= htmlspecialchars($product['seller_name']) ?></strong>
                                    </p>
                                    <p class="mb-0 text-muted">
                                        Contact: <?= htmlspecialchars($product['contact_phone']) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($related_products)): ?>
            <div class="mt-5">
                <h3>Related Products</h3>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                    <?php foreach ($related_products as $related): ?>
                        <div class="col">
                            <div class="card h-100">
                                <a href="view.php?id=<?= $related['id'] ?>">
                                    <?php if (!empty($related['image'])): ?>
                                        <img src="<?= BASE_URL . 'assets/images/' . htmlspecialchars($related['image']) ?>" 
                                             class="card-img-top"
                                             alt="<?= htmlspecialchars($related['title']) ?>"
                                             style="height: 150px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="text-center p-3 bg-light" style="height: 150px;">
                                            <img src="<?= BASE_URL ?>assets/images/placeholder.png" 
                                                 class="img-fluid h-100"
                                                 alt="No image">
                                        </div>
                                    <?php endif; ?>
                                </a>
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="view.php?id=<?= $related['id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($related['title']) ?>
                                        </a>
                                    </h5>
                                    <p class="card-text text-success">R <?= number_format($related['price'], 2) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
