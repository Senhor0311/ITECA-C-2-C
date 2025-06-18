<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Initialize cart 
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

require_once 'db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}

function redirectIfNotAdmin() {
    if (!isAdmin()) {
        $_SESSION['error_message'] = 'Access denied: Admin privileges required';
        header('Location: ' . BASE_URL);
        exit();
    }
}

function redirectIfAdmin(){
    if(isAdmin()){
        header("Location: " . BASE_URL . "admin/dashboard.php");
        exit();
    }
}
