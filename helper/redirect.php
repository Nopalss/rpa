<?php

if (!function_exists('redirect')) {
    function redirect($path)
    {
        header("Location: " . BASE_URL . $path);
        exit;
    }
}
