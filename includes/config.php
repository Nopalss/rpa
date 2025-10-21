<?php

session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db = "rpa_core";

date_default_timezone_set('Asia/Jakarta');

// define('BASE_URL', 'http://rpa.test/');
define('BASE_URL', 'http://localhost/rpa/');


try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8";
    $pdo = new PDO($dsn, $user, $pass);

    // mode error → lempar exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
