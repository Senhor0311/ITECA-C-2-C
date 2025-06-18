<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

redirectIfNotLoggedIn();

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_purchase'])) {
    if (empty($_SESSION['cart'])) {
        $errors['general'] = 'Your cart is empty.';
    } else {
        try {
            $pdo->beginTransaction();
            $product_ids = array_keys($_SESSION['cart']);
            $placeholders = implode(',', array_fill(0, count($product_ids), '?'));

            $stmt = $pdo->prepare("
                SELECT id, quantity
                FROM products
                WHERE id IN ($placeholders) AND status = 'available'
                FOR UPDATE
            ");
            $stmt->execute($product_ids);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $products_by_id = [];
            foreach ($products as $product) {
                $products_by_id[$product['id']] = $product['quantity'];
            }

            foreach ($_SESSION['cart'] as $product_id => $cart_quantity) {
                if (!isset($products_by_id[$product_id])) {
                    throw new Exception("Product ID $product_id is not available.");
                }
                $current_quantity = $products_by_id[$product_id];
                if ($cart_quantity > $current_quantity) {
                    throw new Exception("Insufficient quantity for product ID $product_id. Available: $current_quantity, Requested: $cart_quantity.");
                }
            }

            foreach ($_SESSION['cart'] as $product_id => $cart_quantity) {
                $new_quantity = max(1, $products_by_id[$product_id] - $cart_quantity);
                $stmt = $pdo->prepare("UPDATE products SET quantity = ? WHERE id = ?");
                $stmt->execute([$new_quantity, $product_id]);
            }

            $pdo->commit();
            $_SESSION['cart'] = []; 
            $success_message = 'Purchase confirmed! Your cart has been cleared. Please contact the sellers to finalize payment and delivery.';
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors['general'] = 'Failed to confirm purchase: ' . htmlspecialchars($e->getMessage());
        }
    }
}

$cart_items = [];
$cart_total = 0;

if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));

    $stmt = $pdo->prepare("
        SELECT p.id, p.title, p.price, p.contact_phone, u.username
        FROM products p
        JOIN users u ON p.user_id = u.id
        WHERE p.id IN ($placeholders) AND p.status = 'available'
    ");
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $product) {
        $quantity = $_SESSION['cart'][$product['id']];
        $item_total = $product['price'] * $quantity;
        $cart_total += $item_total;

        $cart_items[] = [
            'id' => $product['id'],
            'title' => $product['title'],
            'price' => $product['price'],
            'contact_phone' => $product['contact_phone'],
            'seller_username' => $product['username'],
            'quantity' => $quantity,
            'total' => $item_total
        ];
    }
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <h1 class="mb-4">Checkout</h1>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php elseif (!empty($errors['general'])): ?>
        <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <div class="alert alert-info">
            Your cart is empty. <a href="<?= BASE_URL ?>products/">Browse products</a>
        </div>
    <?php else: ?>
        <form method="POST">
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="mb-4">Order Summary</h3>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Seller</th>
                                    <th>Contact</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['title']) ?></td>
                                        <td>R <?= number_format($item['price'], 2) ?></td>
                                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                                        <td>R <?= number_format($item['total'], 2) ?></td>
                                        <td><?= htmlspecialchars($item['seller_username']) ?></td>
                                        <td><?= htmlspecialchars($item['contact_phone']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <h4>Order Total: R <?= number_format($cart_total, 2) ?></h4>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h3 class="mb-4">Next Steps</h3>
                    <p class="lead">
                        To complete your purchase, please contact each seller directly to arrange payment and delivery.
                    </p>
                    <ul class="list-group">
                        <?php foreach ($cart_items as $item): ?>
                            <li class="list-group-item">
                                <strong>Product:</strong> <?= htmlspecialchars($item['title']) ?><br>
                                <strong>Quantity:</strong> <?= htmlspecialchars($item['quantity']) ?><br>
                                <strong>Total:</strong> R <?= number_format($item['total'], 2) ?><br>
                                <strong>Seller:</strong> <?= htmlspecialchars($item['seller_username']) ?><br>
                                <strong>Contact:</strong> <?= htmlspecialchars($item['contact_phone']) ?><br>
                                <p class="mt-2">
                                    Please call or message the seller at the above number to discuss payment options and delivery arrangements.
                                </p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mt-4 d-flex justify-content-between">
                        <div>
                            <a href="<?= BASE_URL ?>cart.php" class="btn btn-secondary">Back to Cart</a>
                            <a href="<?= BASE_URL ?>products/" class="btn btn-primary">Continue Shopping</a>
                        </div>
                        <button type="submit" name="confirm_purchase" class="btn btn-success">Confirm Purchase</button>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
