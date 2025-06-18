<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantities'] as $product_id => $quantity) {
            $product_id = (int)$product_id;
            $quantity = max(1, (int)$quantity);
            
            if ($quantity > 0) {
                $_SESSION['cart'][$product_id] = $quantity;
            } else {
                unset($_SESSION['cart'][$product_id]);
            }
        }
    } elseif (isset($_POST['remove_item'])) {
        
        $product_id = (int)$_POST['remove_item'];
        unset($_SESSION['cart'][$product_id]);
    } elseif (isset($_POST['checkout'])) {
        
        header('Location: checkout.php');
        exit();
    }
}

$cart_items = [];
$cart_total = 0;

if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
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
            'image' => $product['image'],
            'quantity' => $quantity,
            'total' => $item_total
        ];
    }
}

require_once 'includes/header.php';
?>

<div class="container py-5">
    <h1 class="mb-4">Your Shopping Cart</h1>
    
    <?php if (empty($cart_items)): ?>
        <div class="alert alert-info">
            Your cart is empty. <a href="<?= BASE_URL ?>products/">Browse products</a>
        </div>
    <?php else: ?>
        <form method="POST">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?= BASE_URL . 'assets/images/' . ($item['image'] ?: 'placeholder.png') ?>" 
                                                     class="img-thumbnail me-3" 
                                                     style="width: 80px; height: 80px; object-fit: cover;"
                                                     alt="<?= htmlspecialchars($item['title']) ?>">
                                                <div>
                                                    <h5 class="mb-0"><?= htmlspecialchars($item['title']) ?></h5>
                                                </div>
                                            </div>
                                        </td>
                                        <td>R <?= number_format($item['price'], 2) ?></td>
                                        <td>
                                            <input type="number" name="quantities[<?= $item['id'] ?>]" 
                                                   value="<?= $item['quantity'] ?>" min="1" class="form-control" style="width: 80px;">
                                        </td>
                                        <td>R <?= number_format($item['total'], 2) ?></td>
                                        <td>
                                            <button type="submit" name="remove_item" class="btn btn-danger btn-sm"
                                                    value="<?= $item['id'] ?>">
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button type="submit" name="update_cart" class="btn btn-outline-secondary">
                    Update Cart
                </button>
                
                <div class="text-end">
                    <h4>Cart Total: R <?= number_format($cart_total, 2) ?></h4>
                    <button type="submit" name="checkout" class="btn btn-primary btn-lg">
                        Proceed to Checkout
                    </button>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
