<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
redirectIfNotAdmin();

$errors = [];
$success_message = '';
$name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    if (empty($name)) {
        $errors['name'] = 'Category name is required';
    } elseif (strlen($name) < 3) {
        $errors['name'] = 'Category name must be at least 3 characters';
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        if ($stmt->execute([$name])) {
            $success_message = 'Category created successfully';
            $name = '';
        } else {
            $errors['general'] = 'Failed to create category';
        }
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $category_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    if ($stmt->execute([$category_id])) {
        $success_message = 'Category deleted successfully';
    } else {
        $errors['general'] = 'Failed to delete category';
    }
}

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
?>

<div class="container mt-4">
    <h2 class="mb-4">Manage Categories</h2>
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
    <?php endif; ?>
    <div class="card mb-4">
        <div class="card-header">
            <h3>Add New Category</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Category Name</label>
                    <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>">
                    <?php if (isset($errors['name'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary">Add Category</button>
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                        <td>
                            <a href="categories.php?delete=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger confirm-before-delete">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
