<?php

function checkLogin($pdo, $username, $password, $useHash = true)
{
    $sql = "SELECT * FROM tbl_user WHERE username = :username LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($useHash) {
            // Kalau pakai password_hash + password_verify
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        } else {
            // Kalau password masih plain text (tidak disarankan)
            if ($password === $user['password']) {
                return $user;
            }
        }
    }
    return false;
}
