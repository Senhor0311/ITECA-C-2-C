<?php
require_once 'config.php';

$host = 'sql211.infinityfree.com';
$dbname = 'if0_39253904_South_Africa_C2C';
$username = 'if0_39253904';
$password = 'eANGvsftslea2Jt';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
