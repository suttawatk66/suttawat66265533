<?php
// api/check_auth.php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    echo json_encode([
        'loggedin' => true,
        'user' => $_SESSION['user']
    ]);
} else {
    echo json_encode([
        'loggedin' => false
    ]);
}
?>