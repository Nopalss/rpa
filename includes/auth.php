<?php
if (!isset($_SESSION['username'])) {
    $url = BASE_URL;
    header("Location: $url");
    exit;
}
