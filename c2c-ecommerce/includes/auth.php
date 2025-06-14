<?php
// auth.php - Core authentication functions

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

require_once 'db.php';

/**
 * Check if user is logged in
 * @return bool True if user is authenticated
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has admin role
 * @return bool True if user is admin
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

/**
 * Redirect to login page if user is not authenticated
 */
function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}

/**
 * Redirect to home page if user is not admin
 */
function redirectIfNotAdmin() {
    if (!isAdmin()) {
        $_SESSION['error_message'] = 'Access denied: Admin privileges required';
        header('Location: ' . BASE_URL);
        exit();
    }
}

/**
 * Redirect to admin dashboard if user is admin
 */
function redirectIfAdmin(){
    if(isAdmin()){
        header("Location: " . BASE_URL . "admin/dashboard.php");
        exit();
    }
}