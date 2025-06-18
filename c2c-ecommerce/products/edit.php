<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

redirectIfNotLoggedIn();

if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . 'products/');
    exit();
}

$product_id = (int)$_GET['id'];

// Fetch product details
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
$stmt->execute([$product_id, $_SESSION['user_id']]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: ' . BASE_URL . 'products/');
    exit();
}

$errors = [];
$title = $product['title'];
$description = $product['description'];
$price = $product['price'];
$category_id = $product['category_id'];
$contact_phone = $product['contact_phone'];
$current_image = $product['image'];
$quantity = $product['quantity'];

// Fetch categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $category_id = $_POST['category_id'];
    $contact_phone = trim($_POST['contact_phone']);
    $quantity = trim($_POST['quantity']);

    // Validation
    if (empty($title)) {
        $errors['title'] = 'Title is required';
    } elseif (strlen($title) < 5) {
        $errors['title'] = 'Title must be at least 5 characters';
    }

    if (empty($description)) {
        $errors['description'] = 'Description is required';
    } elseif (strlen($description) < 10) {
        $errors['description'] = 'Description must be at least 10 characters';
    }

    if (empty($price)) {
        $errors['price'] = 'Price is required';
    } elseif (!is_numeric($price) || $price <= 0) {
        $errors['price'] = 'Price must be a valid number greater than 0';
    }

    if (empty($category_id)) {
        $errors['category_id'] = 'Category is required';
    }

    if (empty($contact_phone)) {
        $errors['contact_phone'] = 'Contact phone is required';
    }

    if (empty($quantity)) {
        $errors['quantity'] = 'Quantity is required';
    } elseif (!is_numeric($quantity) || $quantity < 1) {
        $errors['quantity'] = 'Quantity must be a number greater than or equal to 1';
    }

    // Handle file upload
    $image = $current_image;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];

        if (!in_array($file_type, $allowed_types)) {
            $errors['image'] = 'Only JPG, PNG, and GIF images are allowed';
        } else {
            $upload_dir = '../assets/images/';
            $file_name = uniqid() . '_' . basename($_FILES['image']['name']);
            $target_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                // Delete old image if it exists
                if ($image && file_exists($upload_dir . $image)) {
                    unlink($upload_dir . $image);
                }
                $image = $file_name;
            } else {
                $errors['image'] = 'Failed to upload image';
            }
        }
    }

    // Handle image removal
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
        if ($image && file_exists($upload_dir . $image)) {
            unlink($upload_dir . $image);
        }
        $image = null;
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE products 
                              SET title = ?, description = ?, price = ?, category_id = ?, image = ?, contact_phone = ?, quantity = ?, updated_at = NOW() 
                              WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$title, $description, $price, $category_id, $image, $contact_phone, $quantity, $product_id, $_SESSION['user_id']])) {
            $_SESSION['success_message'] = 'Product updated successfully!';
            header('Location: ' . BASE_URL . 'products/view.php?id=' . $product_id);
            exit();
        } else {
            $errors['general'] = 'Failed to update product listing. Please try again.';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Edit Product Listing</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control <?php echo isset($errors['title']) ? 'is-invalid' : ''; ?>" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>">
                            <?php if (isset($errors['title'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['title']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>" id="description" name="description" rows="5"><?php echo htmlspecialchars($description); ?></textarea>
                            <?php if (isset($errors['description'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['description']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Price (R)</label>
                                <input type="number" step="0.01" class="form-control <?php echo isset($errors['price']) ? 'is-invalid' : ''; ?>" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>">
                                <?php if (isset($errors['price'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['price']; ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select <?php echo isset($errors['category_id']) ? 'is-invalid' : ''; ?>" id="category_id" name="category_id">
                                    <option value="">Select a category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['category_id'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['category_id']; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" min="1" class="form-control <?php echo isset($errors['quantity']) ? 'is-invalid' : ''; ?>" id="quantity" name="quantity" value="<?php echo htmlspecialchars($quantity); ?>">
                            <?php if (isset($errors['quantity'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['quantity']; ?></div>
                            <?php endif; ?>
                            <small class="text-muted">Number of items available</small>
                        </div>

                        <div class="mb-3">
                            <label for="contact_phone" class="form-label">Contact Phone Number</label>
                            <input type="tel" class="form-control <?php echo isset($errors['contact_phone']) ? 'is-invalid' : ''; ?>" id="contact_phone" name="contact_phone" value="<?php echo htmlspecialchars($contact_phone); ?>">
                            <?php if (isset($errors['contact_phone'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['contact_phone']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Current Image</label>
                            <?php if ($current_image): ?>
                                <div>
                                    <img src="<?php echo BASE_URL . 'assets/images/' . $current_image; ?>" class="img-thumbnail mb-2" style="max-height: 200px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                                        <label class="form-check-label" for="remove_image">Remove current image</label>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No image uploaded</p>
                            <?php endif; ?>

                            <label for="image" class="form-label mt-2">New Image</label>
                            <input type="file" class="form-control <?php echo isset($errors['image']) ? 'is-invalid' : ''; ?>" id="image" name="image" accept="image/*">
                            <?php if (isset($errors['image'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['image']; ?></div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Listing</button>
                        <a href="<?php echo BASE_URL; ?>products/view.php?id=<?php echo $product_id; ?>" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>