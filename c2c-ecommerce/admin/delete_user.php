<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

$user_id = $_GET['id'] ?? 0;

if ($user_id) {
    try {
        $pdo->beginTransaction();
        
        // Delete user's products first to maintain referential integrity
        $stmt = $pdo->prepare("DELETE FROM products WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Now delete the user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        $pdo->commit();
        $_SESSION['message'] = 'User deleted successfully.';
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Failed to delete user: ' . $e->getMessage();
    }
}

header('Location: users.php');
exit();