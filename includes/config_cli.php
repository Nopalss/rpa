<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Jakarta');

// ===== DB ONLY =====
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "rpa_core";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_PERSISTENT => false,
];

$pdo = new PDO($dsn, $user, $pass, $options);

// ⛔ JANGAN ADA exit / die / return DI SINI
