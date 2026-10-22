<?php

$host = 'localhost';
$port = '3306';
$dbname = 'inventory-management';  //各グループのデータベース名
$user = 'root';
$password = 'root';

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

$pdo = null;
$errorMessage = '';

try {
    $pdo = new PDO($dsn, $user, $password);

} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}