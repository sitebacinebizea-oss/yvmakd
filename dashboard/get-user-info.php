<?php
require_once 'init.php';

if (!isset($_GET['user_id'])) {
    echo json_encode([]);
    exit;
}

$userId = (int)$_GET['user_id'];
$user = $User->fetchUserById($userId);

if (!$user) {
    echo json_encode([]);
    exit;
}

echo json_encode($user, JSON_UNESCAPED_UNICODE);
