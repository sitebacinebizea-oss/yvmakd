<?php
require_once 'init.php';

if (!isset($_GET['user_id'])) {
    echo json_encode([]);
    exit;
}

$userId = (int) $_GET['user_id'];

$codes = $User->fetchNafadCodesByUserId($userId);

echo json_encode($codes, JSON_UNESCAPED_UNICODE);
exit;
