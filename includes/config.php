<?php

session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db = "rpa_core";
$charset = "utf8mb4";

date_default_timezone_set('Asia/Jakarta');
$http_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
// Gunakan $http_host untuk BASE_URL
define('BASE_URL', "http://{$http_host}/rpa/");
// define('BASE_URL', "http://localhost/rpa/");
define('VERSION', 'V1.0.16');


$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {

    die("KoneKsi ke database gagal: " . $e->getMessage());
}
