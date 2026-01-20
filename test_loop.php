<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "START\n";
flush();

while (true) {
    echo "LOOP " . date('H:i:s') . "\n";
    flush();
    sleep(2);
}
