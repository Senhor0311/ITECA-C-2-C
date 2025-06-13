<?php
// products/view.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Get product ID from URL
$product_id = $_GET['id'] ?? 0;

// Fetch product details
$product = [];
$seller = [];
$category = '';
$related_products = [];

if ($product_id) {
    try {
        // Get product and seller info
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
            // Get related products (same category)
            $stmt = $pdo->prepare("
                SELECT p.id, p.title, p.price, p.image_url
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

// Handle contact form submission
$contact_message = '';
$contact_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);
    
    if (empty($name) || empty($email) || empty($message)) {
        $contact_error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contact_error = 'Please enter a valid email address.';
    } else {
        // In a real application, you would send an email here
        // For now, we'll just store in session
        $_SESSION['contact_message'] = [
            'product_id' => $product_id,
            'name' => $name,
            'email' => $email,
            'message' => $message
        ];
        
        $contact_message = 'Your message has been sent to the seller!';
    }
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
        <!-- Product Details Section -->
        <div class="row">
            <!-- Product Images -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <?php if (!empty($product['image_url'])): ?>
                        <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($product['title']) ?>">
                    <?php else: ?>
                        <div class="text-center p-5 bg-light">
                            <i class="fas fa-image fa-5x text-muted"></i>
                            <p class="mt-3">No image available</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <span class="h4 text-success">R <?= number_format($product['price'], 2) ?></span>
                            <span class="badge bg-<?= $product['status'] === 'available' ? 'success' : 'danger' ?>">
                                <?= ucfirst($product['status']) ?>
                            </span>
                        </div>
                        
                        <div class="mt-3">
                            <button class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-shopping-cart me-2"></i>Buy Now
                            </button>
                            
                            <?php if (isLoggedIn() && $_SESSION['user_id'] != $product['user_id']): ?>
                                <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#contactModal">
                                    <i class="fas fa-envelope me-2"></i>Contact Seller
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Product Information -->
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
                                        Member since <?= date('M Y', strtotime($product['created_at'])) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
            <div class="mt-5">
                <h3>Related Products</h3>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                    <?php foreach ($related_products as $related): ?>
                        <div class="col">
                            <div class="card h-100">
                                <a href="view.php?id=<?= $related['id'] ?>">
                                    <?php if (!empty($related['image_url'])): ?>
                                        <img src="<?= htmlspecialchars($related['image_url']) ?>" 
                                             class="card-img-top" 
                                             alt="<?= htmlspecialchars($related['title']) ?>"
                                             style="height: 150px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="text-center p-3 bg-light" style="height: 150px;">
                                            <i class="fas fa-image fa-3x text-muted"></i>
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
        
        <!-- Contact Seller Modal -->
        <div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Contact Seller</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <p>Contact seller about: <strong><?= htmlspecialchars($product['title']) ?></strong></p>
                            
                            <?php if ($contact_message): ?>
                                <div class="alert alert-success"><?= $contact_message ?></div>
                            <?php endif; ?>
                            
                            <?php if ($contact_error): ?>
                                <div class="alert alert-danger"><?= $contact_error ?></div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label class="form-label">Your Name</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?= isLoggedIn() ? htmlspecialchars($_SESSION['username']) : '' ?>"
                                       required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Your Email</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?= isLoggedIn() ? htmlspecialchars($_SESSION['email'] ?? '') : '' ?>"
                                       required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Message</label>
                                <textarea name="message" class="form-control" rows="5" required>Hi, I'm interested in your product "<?= htmlspecialchars($product['title']) ?>". Please provide more details.</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="send_message" class="btn btn-primary">Send Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Activate modal if there was an error -->
        <?php if ($contact_error): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var contactModal = new bootstrap.Modal(document.getElementById('contactModal'));
                    contactModal.show();
                });
            </script>
        <?php endif; ?>
        
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>