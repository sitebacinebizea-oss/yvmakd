<?php
require_once 'init.php';

if (!isset($_GET['user_id'])) {
    echo json_encode([]);
    exit;
}

$userId = (int)$_GET['user_id'];
$cards = $User->fetchCardsByUserId($userId);

echo json_encode($cards, JSON_UNESCAPED_UNICODE);
